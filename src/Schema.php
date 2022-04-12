<?php

declare(strict_types=1);

namespace Giann\Schematics;

use BadMethodCallException;
use JsonSerializable;
use ReflectionClass;
use InvalidArgumentException;
use ReflectionNamedType;
use Exception;
use ReflectionException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Throwable;

class InvalidSchemaValueException extends Exception
{
    public function __construct(string $message = "", array $path, int $code = 0, ?Throwable $previous = null)
    {
        $message = $message . ' at ' . implode("/", $path);

        parent::__construct($message, $code, $previous);
    }
}

class NotYetImplementedException extends BadMethodCallException
{
}

//#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "PROPERTY", "ANNOTATION"})
 */
class Schema implements JsonSerializable
{
    const TYPE_STRING = 'string';
    const TYPE_NUMBER = 'number';
    const TYPE_INTEGER = 'integer';
    const TYPE_OBJECT = 'object';
    const TYPE_ARRAY = 'array';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_NULL = 'null';

    /** @var string|array|null */
    public $type = null;
    public ?string $id = null;
    public ?string $anchor = null;
    public ?string $ref = null;
    // To avoid resolving the ref multiple times
    private ?string $resolvedRef = null;
    public ?array $defs = null;
    public ?string $title = null;
    public ?string $description = null;
    /** @var mixed */
    public $default = null;
    public ?bool $deprecated = null;
    public ?bool $readOnly = null;
    public ?bool $writeOnly = null;
    /** @var mixed */
    public $const = null;
    /**
     * @var string[]|null
     */
    public ?array $enum = null;
    /**
     * @var Schema[]|null
     */
    public ?array $allOf = null;
    /**
     * @var Schema[]|null
     */
    public ?array $oneOf = null;
    /**
     * @var Schema[]|null
     */
    public ?array $anyOf = null;
    public ?Schema $not = null;

    /**
     * @param string|array|null $type
     * @param string|null $id
     * @param string|null $anchor
     * @param string|null $ref
     * @param array|null $defs
     * @param string|null $title
     * @param string|null $description
     * @param mixed $default
     * @param boolean|null $deprecated
     * @param boolean|null $readOnly
     * @param boolean|null $writeOnly
     * @param mixed $const
     * @param array|null $enum
     * @param Schema[]|null $allOf
     * @param Schema[]|null $oneOf
     * @param Schema[]|null $anyOf
     * @param Schema|null $not
     * @param string|null $enumPattern
     */
    public function __construct(
        $type = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        ?array $defs = null,
        ?string $title = null,
        ?string $description = null,
        $default = null,
        ?bool $deprecated = null,
        ?bool $readOnly = null,
        ?bool $writeOnly = null,
        $const = null,
        ?array $enum = null,
        ?array $allOf = null,
        ?array $oneOf = null,
        ?array $anyOf = null,
        ?Schema $not = null,
        ?string $enumPattern = null
    ) {
        $this->type = $type;
        $this->id = $id;
        $this->anchor = $anchor;
        $this->ref = $ref;
        $this->defs = $defs;
        $this->title = $title;
        $this->description = $description;
        $this->default = $default;
        $this->deprecated = $deprecated;
        $this->readOnly = $readOnly;
        $this->writeOnly = $writeOnly;
        $this->const = $const;
        $this->enum = $enum;
        $this->allOf = $allOf;
        $this->oneOf = $oneOf;
        $this->anyOf = $anyOf;
        $this->not = $not;

        if ($this->enum === null && $enumPattern !== null) {
            $this->enum = self::patternToEnum($enumPattern);
        }
    }

    /**
     * @param string|array $json
     * @return Schema
     */
    public static function fromJson($json): Schema
    {
        $decoded = is_array($json) ? $json : json_decode($json, true);

        // TODO: can we have type both string and object and have then object properties?
        $type = is_array($decoded['type']) ? array_filter($decoded['type'], fn ($el) => $el !== 'null')[0] : $decoded['type'];

        // No type was specified, try to guess it from schema properties
        if ($type === null) {
            $keys = array_keys($decoded);

            if (count(array_intersect(['items', 'prefixItems', 'contains', 'minContains', 'maxContains', 'minItems', 'maxItems', 'uniqueItems'], $keys)) > 0) {
                $type = 'array';
            } else if (count(array_intersect(['multipleOf', 'minimum', 'maximum', 'exclusiveMinimum', 'exclusiveMaximum'], $keys)) > 0) {
                $type = 'number';
            } else if (count(array_intersect(['properties', 'patternProperties', 'additionalProperties', 'unevaluatedProperties', 'requiredProperties', 'propertyNames', 'minProperties', 'maxProperties'], $keys)) > 0) {
                $type = 'object';
            } else if (count(array_intersect(['format', 'minLength', 'maxLength', 'pattern', 'contentEncoding', 'contentMediaType'], $keys)) > 0) {
                $type = 'string';
            }
        }

        switch (strtolower($type ?? '')) {
            case 'string':
                return StringSchema::fromJson($json);
            case 'array':
                return ArraySchema::fromJson($json);
            case 'boolean':
                return BooleanSchema::fromJson($json);
            case 'null':
                return NullSchema::fromJson($json);
            case 'number':
            case 'integer':
                return NumberSchema::fromJson($json);
            case 'object':
                return ObjectSchema::fromJson($json);
        }

        return new Schema(
            $decoded['type'],
            $decoded['id'],
            $decoded['$anchor'],
            $decoded['ref'],
            isset($decoded['$defs']) ? array_map(fn ($def) => self::fromJson($def), $decoded['$defs']) : null,
            $decoded['title'],
            $decoded['description'],
            $decoded['default'],
            $decoded['deprecated'],
            $decoded['readOnly'],
            $decoded['writeOnly'],
            $decoded['const'],
            $decoded['enum'],
            isset($decoded['allOf']) ? array_map(fn ($def) => self::fromJson($def), $decoded['allOf']) : null,
            isset($decoded['oneOf']) ? array_map(fn ($def) => self::fromJson($def), $decoded['oneOf']) : null,
            isset($decoded['anyOf']) ? array_map(fn ($def) => self::fromJson($def), $decoded['anyOf']) : null,
            isset($decoded['not']) ? self::fromJson($decoded['not']) : null,
        );
    }

    protected function resolveRef(?Schema $root): Schema
    {
        $root ??= $this;

        if ($this->ref !== null && $this->resolvedRef === null) {
            $root->defs ??= [];

            if ($this->ref == '#') {
                $this->resolvedRef = '#';
            } else {
                $this->resolvedRef = '#/$def/' . $this->ref;

                if (!isset($root->defs[$this->ref])) {
                    $root->defs[$this->ref] = true; // Avoid circular ref resolving
                    $schema = self::classSchema($this->ref, $root);
                    $root->defs[$this->ref] = $schema instanceof Schema ? $schema->resolveRef($root) : $schema;
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

    // Json Schema types => PHP types
    private const typeCorrespondance = [
        'string' => 'string',
        'number' => 'double',
        'integer' => 'integer',
        'object' => 'object',
        'array' => 'array',
        'boolean' => 'boolean',
        'null' => 'NULL',
    ];

    public static function validateInstance(Model $value): object
    {
        $schema = self::classSchema(get_class($value));

        assert($schema instanceof Schema);

        $schema->validate($value);

        return $value;
    }

    /**
     * @param mixed $value
     * @param Schema|null $root
     * @param string[] $path
     * @return void
     */
    public function validate($value, ?Schema $root = null, array $path = ['#']): void
    {
        $root = $root ?? $this;

        if (is_array($this->type)) {
            $match = false;
            foreach ($this->type as $type) {
                if (self::typeCorrespondance[$type->value] == gettype($value)) {
                    $match = true;
                    break;
                }
            }

            if ($match) {
                throw new InvalidSchemaValueException("Expected type to be one of " . implode(",", $this->type) . ", got " . gettype($value), $path);
            }
        } else if ($this->type !== null && self::typeCorrespondance[$this->type] !== gettype($value)) {
            if ($this->enum !== null) {
                if (!in_array($value, $this->enum)) {
                    throw new InvalidSchemaValueException("Expected type " . self::typeCorrespondance[$this->type] . " got " . gettype($value), $path);
                }
            } else {
                throw new InvalidSchemaValueException("Expected type " . self::typeCorrespondance[$this->type] . " got " . gettype($value), $path);
            }
        }

        if ($this->enum !== null && !in_array($value instanceof JsonSerializable ? $value->jsonSerialize() : $value, $this->enum, true)) {
            throw new InvalidSchemaValueException("Expected value within [" . implode(', ', $this->enum) . '] got `' . $value . '`', $path);
        }

        if ($this->resolvedRef != null) {
            // Root reference
            if ($this->ref === '#' && $root !== $this) {
                $root->validate($value, $root, $path);
            } else {
                $refPath = explode('#', $this->resolvedRef);
                $basePath = explode('/', $refPath[0]);
                $fragment = count($refPath) > 1 ? explode('/', $refPath[1]) : [];

                if (
                    count($basePath) === 1 && $basePath[0] === ''
                    && count($fragment) > 2 && $fragment[1] === '$defs'
                ) {
                    if (isset($root->defs[$fragment[2]])) {
                        $ref = $root->defs[$fragment[2]];

                        $ref->validate($value, $root, [...$path, $this->resolvedRef]);
                    } else {
                        throw new InvalidArgumentException('Can\'t resolve $ref ' . ($this->resolvedRef ?? $this->ref ?? ''));
                    }
                } else {
                    throw new NotYetImplementedException('Reference other than #/$defs/<name> are not yet implemented: ' . ($this->resolvedRef ?? $this->ref ?? ''));
                }
            }
        }

        foreach ($this->allOf ?? [] as $i => $schema) {
            $schema->validate($value, $root, [...$path, 'allOf', $i]);
        }

        if ($this->oneOf !== null && count($this->oneOf) > 0) {
            $oneOf = 0;
            $exceptions = [];
            foreach ($this->oneOf as $i => $schema) {
                try {
                    $schema->validate($value, $root, [...$path, 'oneOf', $i]);
                    $oneOf++;

                    break;
                } catch (InvalidSchemaValueException $e) {
                    $exceptions[] = $e;
                }
            }

            if ($oneOf > 1 || $oneOf == 0) {
                throw new InvalidSchemaValueException(
                    "Should validate against one of\n"
                        . json_encode($this->oneOf, JSON_PRETTY_PRINT)
                        . "\nbut fails with:\n\t- "
                        . implode("\n\t- ", array_map(fn ($e) => $e->getMessage(), $exceptions)),
                    $path
                );
            }
        }

        if ($this->anyOf !== null && count($this->anyOf) > 0) {
            $anyOf = false;
            $exceptions = [];
            foreach ($this->anyOf as $i => $schema) {
                try {
                    $schema->validate($value, $root, [...$path, 'anyOf', $i]);
                    $anyOf = true;

                    break;
                } catch (InvalidSchemaValueException $e) {
                    $exceptions[] = $e;
                }
            }

            if (!$anyOf) {
                throw new InvalidSchemaValueException(
                    "Should validate against any of\n"
                        . json_encode($this->anyOf, JSON_PRETTY_PRINT)
                        . "\nbut fails with:\n\t- "
                        . implode("\n\t- ", array_map(fn ($e) => $e->getMessage(), $exceptions)),
                    $path
                );
            }
        }

        if ($this->not !== null) {
            try {
                $this->not->validate($value, $root, [...$path, 'not']);

                throw new InvalidSchemaValueException("Can't validate against: " . json_encode($this->not), $path);
            } catch (InvalidSchemaValueException $_) {
                // Good
            }
        }
    }

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

        /**
         * @var string $val
         * @var string $name 
         */
        foreach ($constants as $name => $val) {
            if (fnmatch($constantPattern, $name)) {
                $values[] = $val;
            }
        }

        /*
            Ensure there is no duplicate values in enums
         */
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

    /** @return Schema | string */
    public static function classSchema(string $class, ?Schema $root = null)
    {
        if ($class === '#') {
            return '#';
        }

        assert(class_exists($class));

        $classReflection = new ReflectionClass($class);

        $reader = new AnnotationReader();
        /** @var ?ObjectSchema */
        $schema = $reader->getClassAnnotation($classReflection, ObjectSchema::class);

        if ($schema === null) {
            throw new InvalidArgumentException('The class ' . $class . ' is not annotated');
        }

        // Attribute annotations
        $attributes = array_filter(
            $reader->getClassAnnotations($classReflection),
            fn (object $annotation) => $annotation instanceof SchemaAttribute
        );

        /**
         * @var SchemaAttribute $attribute
         */
        foreach ($attributes as $attribute) {
            $schema->{$attribute->key} = $attribute->value;
        }

        $root = $root ?? $schema;

        // Does it extends another class/schema?
        $parentReflection = $classReflection->getParentClass();
        if ($parentReflection !== false) {
            $parent = $parentReflection->getName();

            $root->defs ??= [];
            if (!isset($root->defs[$parent])) {
                $root->defs[$parent] = true; // Avoid circular ref resolving
                $parentSchema = self::classSchema($parent, $root);
                $root->defs[$parent] = $parentSchema instanceof Schema ? $parentSchema->resolveRef($root) : null;
            }

            $ref = new Schema(null, null, null, $parent);
            $ref->resolvedRef = '#/$defs/' . $parent;
            $schema->allOf = ($schema->allOf ?? [])
                + [$ref];
        }

        $properties = $classReflection->getProperties();
        foreach ($properties as $property) {
            // Ignore properties coming from parent class
            if (
                $property->getDeclaringClass()->getNamespaceName() . '\\' . $property->getDeclaringClass()->getName()
                !== $classReflection->getNamespaceName() . '\\' . $classReflection->getName()
            ) {
                continue;
            }

            /** @var ?Schema */
            $propertySchema = $reader->getPropertyAnnotation($property, Schema::class);

            if ($propertySchema !== null) {
                $schema->properties[$property->getName()] = $propertySchema->resolveRef($root);
            } else {
                // Not annotated, try to infer something
                $propertyType = $property->getType();
                /** @var ?Schema */
                $propertySchema = null;

                if ($propertyType instanceof ReflectionNamedType) {
                    $type = $propertyType->getName();

                    switch ($type) {
                        case 'string':
                            $propertySchema = new StringSchema();
                            break;
                        case 'int':
                            $propertySchema = new NumberSchema(true);
                            break;
                        case 'double':
                            $propertySchema = new NumberSchema(false);
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
                        default:
                            // Is it a class?

                            // Is it a circular reference to root schema ?
                            if ($type === $classReflection->getName()) {
                                $type = $root == $schema ? '#' : $type;
                            }

                            $propertySchema = (new Schema(null, null, null, $type))->resolveRef($root);
                    }

                    if ($propertyType->allowsNull()) {
                        $propertySchema = new Schema(
                            // Stupid php 7.4
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            // oneOf
                            [
                                new NullSchema(),
                                $propertySchema
                            ]
                        );
                    }

                    $schema->properties[$property->getName()] = $propertySchema;
                }
            }

            $propertyAttributes = array_filter(
                $reader->getPropertyAnnotations($property),
                fn (object $annotation) => $annotation instanceof SchemaAttribute
            );

            /**
             * @var SchemaAttribute $attribute
             */
            foreach ($propertyAttributes as $attribute) {
                $propertySchema->{$attribute->key} = $attribute->value;
            }
        }

        return $schema;
    }

    public function jsonSerialize(): array
    {
        return ($this->type !== null ? [
            'type' => $this->type
        ] : [])
            + ($this->id !== null ? ['$id' => $this->id] : [])
            + ($this->anchor !== null ? ['$anchor' => $this->anchor] : [])
            + ($this->resolvedRef !== null ? ['$ref' => $this->resolvedRef] : [])
            + ($this->defs !== null ? ['$defs' => $this->defs] : [])
            + ($this->title !== null ? ['title' => $this->title] : [])
            + ($this->description !== null ? ['description' => $this->description] : [])
            + ($this->default !== null ? ['default' => $this->default] : [])
            + ($this->deprecated !== null ? ['deprecated' => $this->deprecated] : [])
            + ($this->readOnly !== null ? ['readOnly' => $this->readOnly] : [])
            + ($this->writeOnly !== null ? ['writeOnly' => $this->writeOnly] : [])
            + ($this->const !== null ? ['const' => $this->const] : [])
            + ($this->enum !== null ? ['enum' => $this->enum] : [])
            + ($this->allOf !== null ? ['allOf' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->allOf)] : [])
            + ($this->oneOf !== null ? ['oneOf' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->oneOf)] : [])
            + ($this->anyOf !== null ? ['anyOf' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->anyOf)] : [])
            + ($this->not !== null ? ['not' => $this->not->jsonSerialize()] : []);
    }
}
