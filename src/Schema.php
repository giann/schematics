<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Attribute;
use Giann\Schematics\Property\Property;
use Giann\Schematics\Exception\InvalidSchemaException;
use JsonSerializable;
use ReflectionClass;
use InvalidArgumentException;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionNamedType;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionType;
use ReflectionUnionType;
use UnitEnum;

// Use to differenciate a property with a null value from the absence of the property. ex: { "const": null }
final class NullConst
{
}

final class CircularReference
{
}

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Schema implements JsonSerializable
{
    // To avoid resolving the ref multiple times
    private ?string $resolvedRef = null;

    // A boolean is a valid schema: true validates anything and false nothing
    private ?bool $unilateral = null;

    /**
     * @param Type[] $type
     * @param string|null $id Defines a URI for the schema, and the base URI that other URI references within the schema are resolved against
     * @param string|null $anchor The "$anchor" keyword is used to specify a name fragment. It is an identifier keyword that can only be used to create plain name fragments
     * @param string|null $ref Reference a schema, and provides the ability to validate recursive structures through self-reference
     * @param array<string,Schema|CircularReference|null> $defs Reserves a location for schema authors to inline re-usable JSON Schemas into a more general schema
     * @param string|null $title
     * @param string|null $description
     * @param mixed $default
     * @param boolean|null $deprecated Indicates that applications should refrain from usage of the declared property
     * @param boolean|null $readOnly Indicates that the value of the instance is managed exclusively by the server or the owning authority, and attempts by a user agent to modify the value of this property are expected to be ignored or rejected by a server
     * @param boolean|null $writeOnly Indicates that the value is never present when the instance is retrieved from the owning authority
     * @param mixed $const Restrict a value to a single value
     * @param mixed[]|null $enum An instance validates successfully against this keyword if its value is equal to one of the elements in this keyword's array value
     * @param Schema[]|null $allOf
     * @param Schema[]|null $oneOf
     * @param Schema[]|null $anyOf
     * @param Schema|null $not
     * @param string|null $enumPattern
     * @param class-string<UnitEnum>|null $enumClass
     */
    public function __construct(
        public array $type = [],
        public ?string $id = null,
        public ?string $anchor = null,
        public ?string $ref = null,
        public array $defs = [],
        public ?string $title = null,
        public ?string $description = null,
        public mixed $default = null,
        public ?bool $deprecated = null,
        public ?bool $readOnly = null,
        public ?bool $writeOnly = null,
        public mixed $const = null,
        public ?array $enum = null,
        public ?array $allOf = null,
        public ?array $oneOf = null,
        public ?array $anyOf = null,
        public ?Schema $not = null,
        ?string $enumPattern = null,
        ?string $enumClass = null,
    ) {
        if ($this->enum === null && $enumClass !== null) {
            $this->enum = ($this->enum ?? []) + self::classToEnum($enumClass);
        }

        if ($this->enum === null && $enumPattern !== null) {
            $this->enum = ($this->enum ?? []) + self::patternToEnum($enumPattern);
        }
    }

    public function getResolvedRef(): ?string
    {
        return $this->resolvedRef;
    }

    public function getUnilateral(): ?bool
    {
        return $this->unilateral;
    }


    // TODO: we miss some ref to resolve
    protected function resolveRef(?Schema $root): Schema
    {
        $root ??= $this;

        $ref = $this->ref;
        if ($ref !== null && $this->resolvedRef === null) {
            if ($ref == '#') {
                $this->resolvedRef = '#';
            } else {
                $this->resolvedRef = '#/$defs/' . $ref;

                if (!isset($root->defs[$ref])) {
                    $root->defs[$ref] = new CircularReference(); // Avoid circular ref resolving
                    $schema = self::classSchema($ref, $root);
                    $root->defs[$ref] = $schema instanceof Schema ? $schema->resolveRef($root) : $schema;
                }
            }
        }

        if ($this->not !== null) {
            $this->not->resolveRef($root);
        }

        foreach ($this->allOf ?? [] as $schema) {
            $schema->resolveRef($root);
        }

        foreach ($this->oneOf ?? [] as $schema) {
            $schema->resolveRef($root);
        }

        foreach ($this->anyOf ?? [] as $schema) {
            $schema->resolveRef($root);
        }

        return $this;
    }

    /**
     * @param string $constantPattern
     * @return string[]|null
     */
    private static function patternToEnum(string $constantPattern): ?array
    {
        if (strpos($constantPattern, '::')) {
            list($cls, $constantPattern) = explode("::", $constantPattern);
            try {
                assert(class_exists($cls));
                $refl = new ReflectionClass($cls);
                $constants = $refl->getConstants();
            } catch (ReflectionException $e) {
                return null;
            }
        } else {
            $constants = get_defined_constants();
        }

        /** @var string[] */
        $values = [];

        foreach ($constants as $name => $val) {
            if (fnmatch($constantPattern, $name)) {
                $values[] = $val;
            }
        }

        // Ensure there is no duplicate values in enums
        if (count(array_unique($values)) !== count($values)) {
            $duplicatedKeys = array_keys(
                array_filter(
                    array_count_values($values),
                    static function ($value): bool {
                        return $value > 1;
                    }
                )
            );

            throw new InvalidArgumentException('Invalid duplicate values for enum ' . $constantPattern . ' for items : ' . implode(', ', $duplicatedKeys));
        }

        return $values;
    }

    /**
     * @param class-string<UnitEnum> $className
     * @return mixed[]
     */
    private static function classToEnum(string $className): array
    {
        $reflection = new ReflectionEnum($className);

        $result = [];
        foreach ($reflection->getCases() as $case) {
            $result[] = $case instanceof ReflectionEnumBackedCase ? $case->getBackingValue() : $case->getValue();
        }

        return $result;
    }

    public static function classSchema(string $class, ?Schema $root = null): ?Schema
    {
        if ($class === '#') {
            return new Schema(id: '#');
        }

        assert(class_exists($class));

        $classReflection = new ReflectionClass($class);

        $schemaAttributes = $classReflection->getAttributes();
        $reflectionAttributes = $classReflection->getAttributes();

        if (empty($schemaAttributes)) {
            return null;
        }

        if (count($schemaAttributes) > 1) {
            throw new InvalidArgumentException('The class ' . $class . ' has more than one object schema attribute');
        }

        $schema = $schemaAttributes[0]->newInstance();

        if (!($schema instanceof ObjectSchema)) {
            return null;
        }

        // Attribute annotations
        /** @var object[] */
        $attributes = array_map(
            fn ($attribute) => $attribute->newInstance(),
            $reflectionAttributes,
        );

        /** @var Property[] */
        $schemaAttributes = array_filter(
            $attributes,
            fn ($attribute) => $attribute instanceof Property,
        );

        foreach ($schemaAttributes as $attribute) {
            $schema->{$attribute->key} = $attribute->value;
        }

        $root = $root ?? $schema;

        // Does it extends another class/schema?
        $parentReflection = $classReflection->getParentClass();
        if ($parentReflection !== false) {
            $parent = $parentReflection->getName();

            if (!isset($root->defs[$parent])) {
                $root->defs[$parent] = new CircularReference(); // Avoid circular ref resolving
                $parentSchema = self::classSchema($parent, $root);
                $root->defs[$parent] = $parentSchema instanceof Schema ? $parentSchema->resolveRef($root) : $parentSchema;
            }

            $ref = new Schema(ref: $parent);
            $ref->resolvedRef = '#/$defs/' . $parent;
            $schema->allOf = ($schema->allOf ?? []) + [$ref];
        }

        $properties = $classReflection->getProperties();
        $required = [];
        foreach ($properties as $property) {
            // Ignore properties coming from parent class
            if (
                $property->getDeclaringClass()->getNamespaceName() . '\\' . $property->getDeclaringClass()->getName()
                !== $classReflection->getNamespaceName() . '\\' . $classReflection->getName()
            ) {
                continue;
            }

            $propertyAttributes = array_map(
                fn ($attr) => $attr->newInstance(),
                $property->getAttributes()
            );

            $isRequired = count(
                array_filter(
                    $propertyAttributes,
                    fn ($attr) => $attr instanceof NotRequired
                )
            ) == 0;

            $propertySchemaProperties = array_filter(
                $propertyAttributes,
                fn ($attr) => $attr instanceof Property
            );

            $propertySchemas = array_filter(
                $propertyAttributes,
                fn ($attr) => $attr instanceof Schema
            );

            if (count($propertySchemas) > 1) {
                throw new InvalidSchemaException('The property ' . $class . '::' . $property->getName() . ' has multiple schema attributes');
            }

            $propertySchema = empty($propertySchemas) ? null : $propertySchemas[0];

            if ($propertySchema !== null) {
                $schema->properties[$property->getName()] = $propertySchema->resolveRef($root);
                if ($isRequired) {
                    $required[] = $property->getName();
                }
            } else {
                $type = $property->getType();

                // Not annotated, try to infer something
                $schema->properties[$property->getName()] = $type !== null
                    ? static::inferType(
                        $schema,
                        $root,
                        $classReflection,
                        $type
                    )
                    : new Schema();
                if ($isRequired) {
                    $required[] = $property->getName();
                }
            }

            foreach ($propertySchemaProperties as $attribute) {
                $propertySchema->{$attribute->key} = $attribute->value;
            }
        }

        $schema->required = $required;

        return $schema;
    }

    /**
     * @param Schema $current
     * @param Schema $root
     * @param ReflectionClass<object> $classReflection
     * @param ReflectionType $typeReflection
     * @return Schema
     */
    public static function inferType(
        Schema $current,
        Schema $root,
        ReflectionClass $classReflection,
        ReflectionType $typeReflection
    ): Schema {
        if ($typeReflection instanceof ReflectionNamedType) {
            switch ($typeReflection->getName()) {
                case 'string':
                    $propertySchema = new StringSchema();
                    break;
                case 'int':
                    $propertySchema = new IntegerSchema();
                    break;
                case 'double':
                    $propertySchema = new NumberSchema();
                    break;
                case 'array':
                    $propertySchema = new ArraySchema();
                    break;
                case 'bool':
                    $propertySchema = new BooleanSchema();
                    break;
                case 'object':
                    $propertySchema = new ObjectSchema();
                    break;
                case 'mixed':
                    $propertySchema = new Schema();
                    break;
                default:
                    // Is it a circular reference to root schema ?
                    if ($typeReflection->getName() === $classReflection->getName()) {
                        $type = $root == $current ? '#' : $typeReflection->getName();
                        $propertySchema = (new Schema(ref: $type))->resolveRef($root);
                    } elseif (class_exists($typeReflection->getName())) { // Is it a class?
                        $propertySchema = (new Schema(ref: $typeReflection->getName()))->resolveRef($root);
                    } else {
                        throw new InvalidSchemaException('Could not infer json schema type of type ' . $typeReflection->getName());
                    }
            }

            if ($typeReflection->allowsNull()) {
                $propertySchema = new Schema(
                    oneOf: [
                        new NullSchema(),
                        $propertySchema
                    ]
                );
            }

            return $propertySchema;
        }

        if ($typeReflection instanceof ReflectionUnionType) {
            $oneOf = [];
            foreach ($typeReflection->getTypes() as $subTypeReflection) {
                $oneOf[] = static::inferType($current, $root, $classReflection, $subTypeReflection);
            }

            return new Schema(
                oneOf: $oneOf
            );
        }

        if ($typeReflection instanceof ReflectionIntersectionType) {
            $allOf = [];
            foreach ($typeReflection->getTypes() as $subTypeReflection) {
                $allOf[] = static::inferType($current, $root, $classReflection, $subTypeReflection);
            }

            return new Schema(
                allOf: $allOf
            );
        }

        return new Schema();
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        $types = array_map(fn (Type $element) => $element->value, $this->type);
        return (!empty($types) ? ['type' => count($types) > 1 ? $types : $types[0]] : [])
            + ($this->id !== null ? ['$id' => $this->id] : [])
            + ($this->anchor !== null ? ['$anchor' => $this->anchor] : [])
            + ($this->resolvedRef !== null ? ['$ref' => $this->resolvedRef] : [])
            + ($this->resolvedRef === null && $this->ref !== null ? ['$ref' => $this->ref] : [])
            + (!empty($this->defs) ? [
                '$defs' => array_map(
                    fn ($el) => $el instanceof Schema ? $el->jsonSerialize() : $el,
                    $this->defs
                )
            ] : [])
            + ($this->title !== null ? ['title' => $this->title] : [])
            + ($this->description !== null ? ['description' => $this->description] : [])
            + ($this->default !== null ? ['default' => $this->default] : [])
            + ($this->deprecated !== null ? ['deprecated' => $this->deprecated] : [])
            + ($this->readOnly !== null ? ['readOnly' => $this->readOnly] : [])
            + ($this->writeOnly !== null ? ['writeOnly' => $this->writeOnly] : [])
            + ($this->const !== null ? ['const' => $this->const instanceof NullConst ? null : $this->const] : [])
            + ($this->enum !== null ? ['enum' => $this->enum] : [])
            + ($this->allOf !== null ? ['allOf' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->allOf)] : [])
            + ($this->oneOf !== null ? ['oneOf' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->oneOf)] : [])
            + ($this->anyOf !== null ? ['anyOf' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->anyOf)] : [])
            + ($this->not !== null ? ['not' => $this->not->jsonSerialize()] : []);
    }
}
