<?php

declare(strict_types=1);

namespace Giann\Schematics\December2020;

use Attribute;
use BackedEnum;
use Giann\Schematics\Draft;
use Giann\Schematics\December2020\Property\Property;
use Giann\Schematics\Exception\InvalidSchemaException;
use Giann\Schematics\ExcludedFromSchema;
use Giann\Schematics\NotRequired;
use Giann\Schematics\Renamed;
use JsonSerializable;
use ReflectionClass;
use InvalidArgumentException;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionNamedType;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionProperty;
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

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Schema implements JsonSerializable
{
    // To avoid resolving the ref multiple times
    private ?string $resolvedRef = null;

    // A boolean is a valid schema: true validates anything and false nothing
    private ?bool $unilateral = null;

    protected bool $isRoot = true;

    public static Draft $draft = Draft::December2020;

    /**
     * @param Type[] $type
     * @param string|null $schema Will be ignored if not root of the schema
     * @param string|null $id Defines a URI for the schema, and the base URI that other URI references within the schema are resolved against
     * @param string|null $anchor The "$anchor" keyword is used to specify a name fragment. It is an identifier keyword that can only be used to create plain name fragments
     * @param string|null $ref Reference a schema, and provides the ability to validate recursive structures through self-reference
     * @param array<string,Schema|CircularReference|null> $defs Reserves a location for schema authors to inline re-usable JSON Schemas into a more general schema
     * @param string|null $title
     * @param string|null $description
     * @param string|null $comment
     * @param mixed[]|null $examples
     * @param mixed $default
     * @param boolean|null $deprecated Indicates that applications should refrain from usage of the declared property
     * @param boolean|null $readOnly Indicates that the value of the instance is managed exclusively by the server or the owning authority, and attempts by a user agent to modify the value of this property are expected to be ignored or rejected by a server
     * @param boolean|null $writeOnly Indicates that the value is never present when the instance is retrieved from the owning authority
     * @param mixed $const Restrict a value to a single value
     * @param mixed[]|null $enum An instance validates successfully against this keyword if its value is equal to one of the elements in this keyword's array value
     * @param Schema[]|null $allOf An instance validates successfully against this keyword if it validates successfully against all schemas defined by this keyword's value
     * @param Schema[]|null $oneOf An instance validates successfully against this keyword if it validates successfully against exactly one schema defined by this keyword's value
     * @param Schema[]|null $anyOf An instance validates successfully against this keyword if it validates successfully against at least one schema defined by this keyword's value
     * @param Schema|null $not An instance is valid against this keyword if it fails to validate successfully against the schema defined by this keyword
     * @param Schema|null $if Instances that successfully validate against this keyword's subschema MUST also be valid against the subschema value of the "then" keyword, if present
     * @param Schema|null $then When "if" is present, and the instance successfully validates against its subschema, then validation succeeds against this keyword if the instance also successfully validates against this keyword's subschema
     * @param Schema|null $else When "if" is present, and the instance fails to validate against its subschema, then validation succeeds against this keyword if the instance successfully validates against this keyword's subschema
     * @param string|null $enumPattern Builds enum field using a list of constant matching this pattern (ex: 'MyClass::VALUE_*')
     * @param class-string<UnitEnum>|null $enumClass Builds enum field using a php enum 
     */
    public function __construct(
        public array $type = [],
        public ?string $schema = null,
        public ?string $id = null,
        public ?string $anchor = null,
        public ?string $ref = null,
        public array $defs = [],
        public ?string $title = null,
        public ?string $description = null,
        public ?string $comment = null,
        public ?array $examples = null,
        public mixed $default = new NullConst(),
        public ?bool $deprecated = null,
        public ?bool $readOnly = null,
        public ?bool $writeOnly = null,
        public mixed $const = null,
        public ?array $enum = null,
        public ?array $allOf = null,
        public ?array $oneOf = null,
        public ?array $anyOf = null,
        public ?Schema $not = null,
        public ?Schema $if = null,
        public ?Schema $then = null,
        public ?Schema $else = null,
        ?string $enumPattern = null,
        ?string $enumClass = null,
    ) {
        if ($this->enum === null && $enumClass !== null) {
            $this->enum = array_merge($this->enum ?? [], self::classToEnum($enumClass));
        }

        if ($this->enum === null && $enumPattern !== null) {
            $this->enum = array_merge($this->enum ?? [], self::patternToEnum($enumPattern) ?? []);
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

                    if ($schema !== null && $schema instanceof Schema) {
                        $root->defs[$ref] = $schema;
                    } else {
                        // We did not resolve it, it's up to the user to have a resolver
                        unset($root->defs[$ref]);
                        $this->resolvedRef = $ref; // No need to investigate further
                    }
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

    public static function hasSchema(string $class): bool
    {
        if (!class_exists($class)) {
            return false;
        }

        $classReflection = new ReflectionClass($class);

        $schemaAttributes = array_values(
            array_filter(
                array_map(
                    fn ($attribute) => $attribute->newInstance(),
                    $classReflection->getAttributes()
                ),
                fn ($attribute) => $attribute instanceof ObjectSchema,
            )
        );

        if (empty($schemaAttributes)) {
            return false;
        }

        if (count($schemaAttributes) > 1) {
            throw new InvalidArgumentException('The class ' . $class . ' has more than one object schema attribute');
        }

        if (!($schemaAttributes[0] instanceof ObjectSchema)) {
            return false;
        }

        return true;
    }

    public static function classSchema(string $class, ?Schema $root = null): ?Schema
    {
        if ($class === '#') {
            return new Schema(id: '#');
        }

        if (!class_exists($class)) {
            return null;
        }

        $classReflection = new ReflectionClass($class);
        $attributes = array_map(
            fn ($attribute) => $attribute->newInstance(),
            $classReflection->getAttributes()
        );

        $schemaAttributes = array_values(
            array_filter(
                $attributes,
                fn ($attribute) => $attribute instanceof ObjectSchema,
            )
        );

        if (empty($schemaAttributes)) {
            return null;
        }

        if (count($schemaAttributes) > 1) {
            throw new InvalidArgumentException('The class ' . $class . ' has more than one object schema attribute');
        }

        $schema = $schemaAttributes[0];

        if (!($schema instanceof ObjectSchema)) {
            return null;
        }

        // Attribute annotations
        /** @var Property[] */
        $schemaProperties = array_values(
            array_filter(
                $attributes,
                fn ($attribute) => $attribute instanceof Property,
            )
        );

        foreach ($schemaProperties as $schemaProperty) {
            $schema->{$schemaProperty->key} = $schemaProperty->value;
        }

        $root = $root ?? $schema;
        $root->isRoot = true;

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
            $schema->allOf = array_merge($schema->allOf ?? [], [$ref]);
        }

        $required = [];
        /** @var ReflectionMethod|ReflectionProperty $member */
        foreach (array_merge($classReflection->getProperties(), $classReflection->getMethods()) as $member) {
            // Ignore properties/methods coming from parent class
            if (
                $member->getDeclaringClass()->getNamespaceName() . '\\' . $member->getDeclaringClass()->getName()
                !== $classReflection->getNamespaceName() . '\\' . $classReflection->getName()
            ) {
                continue;
            }

            // If its a method, it must be a getter
            $isMethod = $member instanceof ReflectionMethod;
            $isGetter = $isMethod
                && ($returnType = $member->getReturnType())
                // Starts with 'get'
                && (str_starts_with($member->getName(), 'get') || str_starts_with($member->getName(), 'is') || str_starts_with($member->getName(), 'has'))
                // As no parameters
                && $member->getNumberOfParameters() === 0
                // Does not return void
                && (
                    !($returnType instanceof ReflectionNamedType)
                    || !$returnType->isBuiltin()
                    || $returnType->getName() !== 'void'
                )
                // Does not override parent method
                && (
                    $parentReflection === false
                    || !$parentReflection->hasMethod($member->getName())
                )
                // Current class implements JsonSerializable
                && $classReflection->implementsInterface(JsonSerializable::class);

            if ($isMethod && !$isGetter) {
                continue;
            }

            $memberAttributes = array_map(
                fn ($attr) => $attr->newInstance(),
                $member->getAttributes()
            );

            // Property excluded from json schema ?
            if (count(
                array_filter(
                    $memberAttributes,
                    fn ($attr) => $attr instanceof ExcludedFromSchema
                )
            ) > 0) {
                continue;
            }

            $isRequired = count(
                array_filter(
                    $memberAttributes,
                    fn ($attr) => $attr instanceof NotRequired
                )
            ) == 0;

            $names = array_values(
                array_filter(
                    $memberAttributes,
                    fn ($attr) => $attr instanceof Renamed
                )
            );
            // If getter drop the `get`
            $name = $member->getName();
            $name = $isGetter
                ? lcfirst(substr($name, str_starts_with($name, 'is') ? 2 : 3))
                : $name;
            // If renamed, replace the name
            $name = !empty($names) ? $names[0]->name : $name;

            $propertySchemaProperties = array_values(
                array_filter(
                    $memberAttributes,
                    fn ($attr) => $attr instanceof Property
                )
            );

            /** @var Schema[] */
            $propertySchemas = array_values(
                array_filter(
                    $memberAttributes,
                    fn ($attr) => $attr instanceof Schema
                )
            );

            // A getter must have a Schema attribute to be considered as a property of the schema
            if ($isGetter && count($propertySchemas) === 0) {
                continue;
            }

            if (count($propertySchemas) > 1) {
                throw new InvalidSchemaException('The property ' . $class . '::' . $name . ' has multiple schema attributes');
            }

            $propertySchema = empty($propertySchemas) ? null : $propertySchemas[0];

            // With `Renamed` and getters we might have multiple properties with the same name in the schema
            if (isset($schema->properties[$name])) {
                throw new InvalidSchemaException('The property ' . $class . '::' . $name . ' is already defined');
            }

            if ($propertySchema !== null) {
                $schema->properties[$name] = $propertySchema->resolveRef($root);
                if ($isRequired) {
                    $required[] = $name;
                }
            } else {
                // We can't get here with a getter
                assert(!($member instanceof ReflectionMethod));
                $type = $member->getType();
                $propertySchema = $type !== null
                    ? static::inferType(
                        $schema,
                        $root,
                        $classReflection,
                        $type
                    )
                    : new Schema();

                // Not annotated, try to infer something
                $schema->properties[$name] = $propertySchema;
                if ($isRequired) {
                    $required[] = $name;
                }
            }

            assert($propertySchema !== null);

            foreach ($propertySchemaProperties as $attribute) {
                $propertySchema->{$attribute->key} = $attribute->value;
            }

            if (!$isGetter) {
                assert($member instanceof ReflectionProperty);
                $propertySchema->default = self::getPropertyDefaultValue($classReflection, $member);
            }
        }

        $schema->required = !empty($required) ? $required : null;

        return $schema;
    }

    /**
     * If the property was define as __construct param, ReflectionProperty->hasDefaultValue() will return false
     * We have to either get the default value from ReflectionProperty->getDefaultValue() or by looking up the __construct param
     * 
     * @param ReflectionClass<object> $classReflection
     * @param ReflectionProperty $propertyReflection
     * @return mixed
     */
    private static function getPropertyDefaultValue(ReflectionClass $classReflection, ReflectionProperty $propertyReflection): mixed
    {
        if ($propertyReflection->hasDefaultValue()) {
            return json_encode($propertyReflection->getDefaultValue());
        }

        if ($propertyReflection->isPromoted()) {
            $isRequired = count(
                $propertyReflection->getAttributes(NotRequired::class)
            ) == 0;

            foreach ($classReflection->getConstructor()?->getParameters() ?? [] as $param) {
                if ($param->getName() === $propertyReflection->getName() && $param->isPromoted()) {
                    if ($param->isDefaultValueAvailable()) {
                        $default = $param->getDefaultValue();

                        // If property has `NotRequired` attribute, a null default is not considered as the default value
                        if ($default === null && !$isRequired) {
                            return new NullConst();
                        } else {
                            return json_decode(json_encode($default) ?: '');
                        }
                    } else {
                        break;
                    }
                }
            }
        }

        // The property may be promoted in a parent class
        if (($parentClass = $classReflection->getParentClass())) {
            foreach ($parentClass->getProperties() as $parentProperty) {
                if ($parentProperty->getName() === $propertyReflection->getName()) {
                    return self::getPropertyDefaultValue($parentClass, $parentProperty);
                }
            }
        }

        return new NullConst();
    }

    /**
     * @param Schema $current
     * @param Schema $root
     * @param ReflectionClass<object> $currentClassReflection
     * @param ReflectionType $typeReflection
     * @return Schema
     */
    public static function inferType(
        Schema $current,
        Schema $root,
        ReflectionClass $currentClassReflection,
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
                case 'float':
                    $propertySchema = new NumberSchema();
                    break;
                case 'array':
                    $propertySchema = new ArraySchema();
                    break;
                case 'bool':
                    $propertySchema = new BooleanSchema();
                    break;
                case 'object':
                case 'stdClass':
                    $propertySchema = new ObjectSchema();
                    break;
                case 'mixed':
                    $propertySchema = new Schema();
                    break;
                default:
                    // Is it a circular reference to root schema ?
                    if ($typeReflection->getName() === $currentClassReflection->getName()) {
                        $type = $root == $current ? '#' : $typeReflection->getName();
                        $propertySchema = (new Schema(ref: $type))->resolveRef($root);
                    } elseif (class_exists($typeReflection->getName())) { // Is it a class or enum?
                        $classReflection = new ReflectionClass($typeReflection->getName());

                        if ($classReflection->implementsInterface(BackedEnum::class)) {
                            // Backed enum
                            /** @var class-string<UnitEnum> */
                            $enumName = $typeReflection->getName();
                            $enumReflection = new ReflectionEnum($enumName);
                            $backingType = $enumReflection->getBackingType();

                            $propertySchema = $backingType !== null
                                ? static::inferType(
                                    $current,
                                    $root,
                                    $currentClassReflection,
                                    $backingType
                                )
                                : new StringSchema();

                            $propertySchema->enum = self::classToEnum($enumName);
                        } elseif ($classReflection->implementsInterface(UnitEnum::class)) {
                            /** @var class-string<UnitEnum> */
                            $enumName = $typeReflection->getName();

                            // Normal enum
                            $propertySchema = new StringSchema(
                                enumClass: $enumName
                            );
                        } elseif (static::hasSchema($typeReflection->getName())) {
                            // Class
                            $propertySchema = (new Schema(ref: $typeReflection->getName()))->resolveRef($root);
                        } else {
                            throw new InvalidSchemaException('Could not infer json schema type of type ' . $typeReflection->getName());
                        }
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
                $oneOf[] = static::inferType($current, $root, $currentClassReflection, $subTypeReflection);
            }

            return new Schema(
                oneOf: $oneOf
            );
        }

        if ($typeReflection instanceof ReflectionIntersectionType) {
            $allOf = [];
            foreach ($typeReflection->getTypes() as $subTypeReflection) {
                $allOf[] = static::inferType($current, $root, $currentClassReflection, $subTypeReflection);
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
        if ($this->not !== null) {
            $this->not->isRoot = false;
        }
        if ($this->if !== null) {
            $this->if->isRoot = false;
        }
        if ($this->then !== null) {
            $this->then->isRoot = false;
        }
        if ($this->else !== null) {
            $this->else->isRoot = false;
        }
        return ($this->isRoot || $this->schema !== null ? ['$schema' => $this->schema ?? self::$draft->value] : [])
            + (!empty($types) ? ['type' => count($types) > 1 ? $types : $types[0]] : [])
            + ($this->id !== null ? ['$id' => $this->id] : [])
            + ($this->anchor !== null ? ['$anchor' => $this->anchor] : [])
            + ($this->resolvedRef !== null ? ['$ref' => $this->resolvedRef] : [])
            + ($this->resolvedRef === null && $this->ref !== null ? ['$ref' => $this->ref] : [])
            + (!empty($this->defs) ? [
                '$defs' => array_map(
                    function ($el) {
                        if ($el instanceof Schema) {
                            $el->isRoot = false;
                            return $el->jsonSerialize();
                        }

                        return $el;
                    },
                    $this->defs
                )
            ] : [])
            + ($this->title !== null ? ['title' => $this->title] : [])
            + ($this->description !== null ? ['description' => $this->description] : [])
            + ($this->comment !== null ? ['$comment' => $this->comment] : [])
            + (!($this->default instanceof NullConst) ? ['default' => $this->default] : [])
            + ($this->deprecated !== null ? ['deprecated' => $this->deprecated] : [])
            + ($this->readOnly !== null ? ['readOnly' => $this->readOnly] : [])
            + ($this->writeOnly !== null ? ['writeOnly' => $this->writeOnly] : [])
            + ($this->const !== null ? ['const' => $this->const instanceof NullConst ? null : $this->const] : [])
            + ($this->enum !== null ? ['enum' => $this->enum] : [])
            + ($this->allOf !== null ? [
                'allOf' => array_map(function (Schema $element) {
                    $element->isRoot = false;
                    return $element->jsonSerialize();
                }, $this->allOf)
            ] : [])
            + ($this->oneOf !== null ? [
                'oneOf' => array_map(function (Schema $element) {
                    $element->isRoot = false;
                    return $element->jsonSerialize();
                }, $this->oneOf)
            ] : [])
            + ($this->anyOf !== null ? [
                'anyOf' => array_map(function (Schema $element) {
                    $element->isRoot = false;
                    return $element->jsonSerialize();
                }, $this->anyOf)
            ] : [])
            + ($this->not !== null ? ['not' => $this->not->jsonSerialize()] : [])
            + ($this->if !== null ? ['if' => $this->if->jsonSerialize()] : [])
            + ($this->then !== null ? ['then' => $this->then->jsonSerialize()] : [])
            + ($this->else !== null ? ['else' => $this->else->jsonSerialize()] : []);
    }
}
