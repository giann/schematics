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


class InvalidSchemaValueException extends Exception
{
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
    public ?array $definitions = null;
    public ?string $title = null;
    public ?string $description = null;
    public $default = null;
    public ?bool $deprecated = null;
    public ?bool $readOnly = null;
    public ?bool $writeOnly = null;
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
     * @param array|null $definitions
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
        ?array $definitions = null,
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
        $this->definitions = $definitions;
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

    protected function resolveRef(?Schema $root): Schema
    {
        $root ??= $this;

        if ($this->ref !== null && $this->resolvedRef === null) {
            $root->definitions ??= [];
            $root->definitions[$this->ref] ??= self::classSchema($this->ref, $root)->resolveRef($root);
            $this->resolvedRef = '#/definitions/' . $this->ref;
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

    public static function validateInstance(object $value): object
    {
        $schema = self::classSchema(get_class($value));

        assert($schema instanceof Schema);

        $schema->validate($value);

        return $value;
    }

    public function validate($value, ?Schema $root = null): void
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
                throw new InvalidSchemaValueException("Expected type to be one of " . implode(",", $this->type) . ", got " . gettype($value));
            }
        } else if ($this->type !== null && self::typeCorrespondance[$this->type] !== gettype($value)) {
            if ($this->enum !== null) {
                if (!in_array($value, $this->enum)) {
                    throw new InvalidSchemaValueException("Expected type " . self::typeCorrespondance[$this->type] . " got " . gettype($value));
                }
            } else {
                throw new InvalidSchemaValueException("Expected type " . self::typeCorrespondance[$this->type] . " got " . gettype($value));
            }
        }

        if ($this->enum !== null && !in_array($value instanceof JsonSerializable ? $value->jsonSerialize() : $value, $this->enum, true)) {
            throw new InvalidSchemaValueException("Expected value from enum");
        }

        if ($this->ref != null) {
            // Root reference
            if ($this->ref === '#' && $root !== null && $root !== $this) {
                $root->validate($value, $root);
            } else {
                $path = explode('#', $this->ref);
                $basePath = explode('/', $path[0]);
                $fragment = count($path) > 1 ? explode('/', $path[1]) : [];

                if (
                    count($basePath) === 1 && $basePath[0] === ''
                    && count($fragment) > 2 && $fragment[1] === 'definitions'
                ) {
                    if (isset($root->definitions[$fragment[2]])) {
                        $ref = $root->definitions[$fragment[2]];

                        $ref->validate($value, $root);
                    } else {
                        throw new InvalidArgumentException('Can\'t resolve $ref ' . $this->ref);
                    }
                } else {
                    throw new NotYetImplementedException("Reference other than #/definitions/<name> are not yet implemented");
                }
            }
        }

        foreach ($this->allOf ?? [] as $schema) {
            $schema->validate($value, $root);
        }

        if ($this->oneOf !== null && count($this->oneOf) > 0) {
            $oneOf = 0;
            foreach ($this->oneOf as $schema) {
                try {
                    $schema->validate($value, $root);
                    $oneOf++;

                    if ($oneOf > 1) {
                        break;
                    }
                } catch (InvalidSchemaValueException $_) {
                }
            }

            if ($oneOf > 1 || $oneOf == 0) {
                throw new InvalidSchemaValueException("Should validate against one of " . json_encode($this->oneOf));
            }
        }

        if ($this->anyOf !== null && count($this->anyOf) > 0) {
            $anyOf = false;
            foreach ($this->anyOf as $schema) {
                try {
                    $schema->validate($value, $root);
                    $anyOf = true;

                    break;
                } catch (InvalidSchemaValueException $_) {
                }
            }

            if (!$anyOf) {
                throw new InvalidSchemaValueException("Should validate against any of " . json_encode($this->anyOf));
            }
        }

        if ($this->not !== null) {
            try {
                $this->not->validate($value, $root);

                throw new InvalidSchemaValueException("Can't validate against: " . json_encode($this->not));
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
                $refl = new ReflectionClass($cls);
                $constants = $refl->getConstants();
            } catch (ReflectionException $e) {
                return null;
            }
        } else {
            $constants = get_defined_constants();
        }

        $values = [];

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

        $classReflection = new ReflectionClass($class);

        $reader = new AnnotationReader();
        /** @var Schema */
        $schema = $reader->getClassAnnotation($classReflection, self::class);

        if ($schema === false) {
            throw new InvalidArgumentException('The class ' . $class . ' is not annotated');
        }

        if ($schema === false) {
            throw new InvalidArgumentException('The class ' . $class . ' is not annotated');
        }

        $root = $root ?? $schema;

        // Does it extends another class/schema?
        $parentReflection = $classReflection->getParentClass();
        if ($parentReflection !== false) {
            $parent = $parentReflection->getName();

            $root->definitions ??= [];
            $root->definitions[$parent] ??= self::classSchema($parent, $root)->resolveRef($root);

            $schema->allOf = ($schema->allOf ?? [])
                + [new Schema(null, null, null, '#/definitions/' . $parent)];
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

            /** @var Schema */
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
                        default:
                            // Is it a class?

                            // Is it a circular reference to root schema ?
                            if ($type === $classReflection->getName()) {
                                $type = $root == $schema ? '#' : $type;
                            }

                            $propertySchema = (new Schema(null, null, null, $type))->resolveRef($root);
                    }

                    if ($propertySchema !== null) {
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
            + ($this->definitions !== null ? ['definitions' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->definitions)] : [])
            + ($this->title !== null ? ['title' => $this->title] : [])
            + ($this->description !== null ? ['description' => $this->description] : [])
            + ($this->default !== null ? ['default' => $this->default] : [])
            + ($this->deprecated !== null ? ['deprecated' => $this->deprecated] : [])
            + ($this->readOnly !== null ? ['readOnly' => $this->readOnly] : [])
            + ($this->writeOnly !== null ? ['writeOnly' => $this->writeOnly] : [])
            + ($this->const !== null ? ['const' => $this->const] : [])
            + ($this->enum !== null ? ['enum' => array_map(fn ($e) => is_object($e) ? $e->value : $e, $this->enum)] : [])
            + ($this->allOf !== null ? ['allOf' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->allOf)] : [])
            + ($this->oneOf !== null ? ['oneOf' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->oneOf)] : [])
            + ($this->anyOf !== null ? ['anyOf' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->anyOf)] : [])
            + ($this->not !== null ? ['not' => $this->not->jsonSerialize()] : []);
    }
}

//#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class ArraySchema extends Schema
{
    public ?Schema $items = null;
    /** @var Schema[] */
    public ?array $prefixItems = null;
    public ?Schema $contains = null;
    public ?int $minContains = null;
    public ?int $maxContains = null;
    public ?bool $uniqueItems = null;

    /**
     * @param string|null $title
     * @param string|null $id
     * @param string|null $anchor
     * @param string|null $ref
     * @param array|null $defs
     * @param array|null $definitions
     * @param string|null $description
     * @param mixed $default
     * @param boolean|null $deprecated
     * @param boolean|null $readOnly
     * @param boolean|null $writeOnly
     * @param mixed $const
     * @param array|null $enum
     * @param array|null $allOf
     * @param array|null $oneOf
     * @param array|null $anyOf
     * @param Schema|null $not
     * @param string|null $enumPattern
     * @param Schema|string|null $items
     * @param array|null $prefixItems
     * @param Schema|null $contains
     * @param integer|null $minContains
     * @param integer|null $maxContains
     * @param boolean|null $uniqueItems
     */
    public function __construct(
        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        ?array $defs = null,
        ?array $definitions = null,
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
        ?string $enumPattern = null,

        $items = null,
        /** @var Schema[] */
        ?array $prefixItems = null,
        ?Schema $contains = null,
        ?int $minContains = null,
        ?int $maxContains = null,
        ?bool $uniqueItems = null
    ) {
        parent::__construct(
            Schema::TYPE_ARRAY,
            $id,
            $anchor,
            $ref,
            $defs,
            $definitions,
            $title,
            $description,
            $default,
            $deprecated,
            $readOnly,
            $writeOnly,
            $const,
            $enum,
            $allOf,
            $oneOf,
            $anyOf,
            $not,
            $enumPattern,
        );

        $this->items = is_string($items) ? new Schema(null, null, null, $items) : $items;
        $this->prefixItems = $prefixItems;
        $this->contains = $contains;
        $this->minContains = $minContains;
        $this->maxContains = $maxContains;
        $this->uniqueItems = $uniqueItems;
    }

    protected function resolveRef(?Schema $root): Schema
    {
        $root ??= $this;

        parent::resolveRef($root);

        if ($this->items !== null) {
            assert($this->items instanceof Schema);

            $this->items->resolveRef($root);
        }

        foreach ($this->prefixItems ?? [] as $schema) {
            $schema->resolveRef($root);
        }

        return $this;
    }

    public function validate($value, ?Schema $root = null): void
    {
        $root = $root ?? $this;

        parent::validate($value, $root);

        if ($this->minContains !== null && count($value) < $this->minContains) {
            throw new InvalidSchemaValueException("Expected at least ' . $this->minContains . ' elements got " . count($value));
        }

        if ($this->maxContains !== null && count($value) > $this->maxContains) {
            throw new InvalidSchemaValueException("Expected at most ' . $this->maxContains . ' elements got " . count($value));
        }

        if ($this->uniqueItems === true) {
            $items = [];
            foreach ($value as $item) {
                if (in_array($item, $items)) {
                    throw new InvalidSchemaValueException('Expected unique items');
                }

                $items[] = $item;
            }
        }

        if ($this->prefixItems !== null && count($this->prefixItems) > 0) {
            foreach ($this->prefixItems as $i => $prefixItem) {
                $prefixItem->validate($value[$i], $root);
            }
        }

        if ($this->contains !== null) {
            $contains = false;
            foreach ($value as $item) {
                try {
                    $this->contains->validate($item, $root);

                    $contains = true;

                    break;
                } catch (InvalidSchemaValueException $_) {
                }
            }

            if (!$contains) {
                throw new InvalidSchemaValueException('Expected at least one item to validate against ' . $this->contains);
            }
        }
    }

    public function jsonSerialize(): array
    {
        return parent::jsonSerialize()
            + ($this->items !== null ? ['items' => $this->items->jsonSerialize()] : [])
            + ($this->prefixItems !== null ? ['prefixItems' => $this->prefixItems] : [])
            + ($this->contains !== null ? ['contains' => $this->contains->jsonSerialize()] : [])
            + ($this->minContains !== null ? ['minContains' => $this->minContains] : [])
            + ($this->maxContains !== null ? ['maxContains' => $this->maxContains] : [])
            + ($this->uniqueItems !== null ? ['uniqueItems' => $this->uniqueItems] : []);
    }
}

//#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class NumberSchema extends Schema
{
    public bool $integer = false;
    /** @var int|double|null  */
    public $multipleOf = null;
    /** @var int|double|null  */
    public $minimum = null;
    /** @var int|double|null  */
    public $maximum = null;
    /** @var int|double|null  */
    public $exclusiveMinimum = null;
    /** @var int|double|null  */
    public $exclusiveMaximum = null;

    /**
     * @param boolean $integer
     * @param int|double|null $multipleOf
     * @param int|double|null $minimum
     * @param int|double|null $maximum
     * @param int|double|null $exclusiveMinimum
     * @param int|double|null $exclusiveMaximum
     * @param string|null $title
     * @param string|null $id
     * @param string|null $anchor
     * @param string|null $ref
     * @param array|null $defs
     * @param array|null $definitions
     * @param string|null $description
     * @param mixed $default
     * @param boolean|null $deprecated
     * @param boolean|null $readOnly
     * @param boolean|null $writeOnly
     * @param mixed $const
     * @param array|null $enum
     * @param array|null $allOf
     * @param array|null $oneOf
     * @param array|null $anyOf
     * @param Schema|null $not
     * @param string|null $enumPattern
     */
    public function __construct(
        bool $integer = false,
        $multipleOf = null,
        $minimum = null,
        $maximum = null,
        $exclusiveMinimum = null,
        $exclusiveMaximum = null,

        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        ?array $defs = null,
        ?array $definitions = null,
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
        parent::__construct(
            $integer ? Schema::TYPE_INTEGER : Schema::TYPE_NUMBER,
            $id,
            $anchor,
            $ref,
            $defs,
            $definitions,
            $title,
            $description,
            $default,
            $deprecated,
            $readOnly,
            $writeOnly,
            $const,
            $enum,
            $allOf,
            $oneOf,
            $anyOf,
            $not,
            $enumPattern
        );

        $this->integer = $integer;
        $this->multipleOf = $multipleOf;
        $this->minimum = $minimum;
        $this->maximum = $maximum;
        $this->exclusiveMinimum = $exclusiveMinimum;
        $this->exclusiveMaximum = $exclusiveMaximum;
    }

    public function validate($value, ?Schema $root = null): void
    {
        $root = $root ?? $this;

        parent::validate($value, $root);

        if (!is_int($value) && $this->integer) {
            throw new InvalidSchemaValueException("Expected an integer got " . gettype($value));
        }

        if (!$this->integer && !is_double($value)) {
            throw new InvalidSchemaValueException("Expected a double got " . gettype($value));
        }

        if ($this->multipleOf !== null && $value % $this->multipleOf !== 0) {
            throw new InvalidSchemaValueException("Expected a multiple of " . $this->multipleOf);
        }

        if ($this->minimum !== null && $value < $this->minimum) {
            throw new InvalidSchemaValueException("Expected value to be less or equal to " . $this->minimum);
        }

        if ($this->maximum !== null && $value > $this->maximum) {
            throw new InvalidSchemaValueException("Expected value to be greater or equal to " . $this->maximum);
        }

        if ($this->exclusiveMinimum !== null && $value <= $this->exclusiveMinimum) {
            throw new InvalidSchemaValueException("Expected value to be less than " . $this->exclusiveMinimum);
        }

        if ($this->exclusiveMaximum !== null && $value >= $this->exclusiveMaximum) {
            throw new InvalidSchemaValueException("Expected value to be greather than " . $this->exclusiveMaximum);
        }
    }

    public function jsonSerialize(): array
    {
        return parent::jsonSerialize()
            + ($this->multipleOf !== null ? ['multipleOf' => $this->multipleOf] : [])
            + ($this->minimum !== null ? ['minimum' => $this->minimum] : [])
            + ($this->maximum !== null ? ['maximum' => $this->maximum] : [])
            + ($this->exclusiveMinimum !== null ? ['exclusiveMinimum' => $this->exclusiveMinimum] : [])
            + ($this->exclusiveMaximum !== null ? ['exclusiveMaximum' => $this->exclusiveMaximum] : []);
    }
}


//#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class BooleanSchema extends Schema
{
    public function __construct(
        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        ?array $defs = null,
        ?array $definitions = null,
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
        parent::__construct(
            Schema::TYPE_BOOLEAN,
            $id,
            $anchor,
            $ref,
            $defs,
            $definitions,
            $title,
            $description,
            $default,
            $deprecated,
            $readOnly,
            $writeOnly,
            $const,
            $enum,
            $allOf,
            $oneOf,
            $anyOf,
            $not,
            $enumPattern,
        );
    }
}

//#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class NullSchema extends Schema
{
    public function __construct(
        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        ?array $defs = null,
        ?array $definitions = null,
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
        parent::__construct(
            Schema::TYPE_NULL,
            $id,
            $anchor,
            $ref,
            $defs,
            $definitions,
            $title,
            $description,
            $default,
            $deprecated,
            $readOnly,
            $writeOnly,
            $const,
            $enum,
            $allOf,
            $oneOf,
            $anyOf,
            $not,
            $enumPattern,
        );
    }
}

//#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class StringSchema extends Schema
{
    // http://en.wikipedia.org/wiki/ISO_8601#Durations
    const DURATION_REGEX = '/^P([0-9]+(?:[,\.][0-9]+)?Y)?([0-9]+(?:[,\.][0-9]+)?M)?([0-9]+(?:[,\.][0-9]+)?D)?(?:T([0-9]+(?:[,\.][0-9]+)?H)?([0-9]+(?:[,\.][0-9]+)?M)?([0-9]+(?:[,\.][0-9]+)?S)?)?$/';

    // https://json-schema.org/understanding-json-schema/reference/string.html#id8
    const FORMAT_DATETIME = 'date-time';
    const FORMAT_TIME = 'time';
    const FORMAT_DATE = 'date';
    const FORMAT_DURATION = 'duration';
    const FORMAT_EMAIL = 'email';
    const FORMAT_IDNEMAIL = 'idn-email';
    const FORMAT_HOSTNAME = 'hostname';
    const FORMAT_IDNHOSTNAME = 'idn-hostname';
    const FORMAT_IPV4 = 'ipv4';
    const FORMAT_IPV6 = 'ipv6';
    const FORMAT_UUID = 'uuid';
    const FORMAT_URI = 'uri';
    const FORMAT_URIREFERENCE = 'uri-reference';
    const FORMAT_IRI = 'iri';
    const FORMAT_IRIREFERENCE = 'iri-reference';
    const FORMAT_URITEMPLATE = 'uri-template';
    const FORMAT_JSONPOINTER = 'json-pointer';
    const FORMAT_RELATIVEJSONPOINTER = 'relative-json-pointer';
    const FORMAT_REGEX = 'regex';

    public ?string $format = null;
    public ?int $minLength = null;
    public ?int $maxLength = null;
    public ?string $pattern = null;
    public ?string $contentEncoding = null;
    public ?string $contentMediaType = null;

    public function __construct(
        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        ?array $defs = null,
        ?array $definitions = null,
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
        ?string $enumPattern = null,

        ?string $format = null,
        ?int $minLength = null,
        ?int $maxLength = null,
        ?string $pattern = null,
        ?string $contentEncoding = null,
        ?string $contentMediaType = null

    ) {
        parent::__construct(
            Schema::TYPE_STRING,
            $id,
            $anchor,
            $ref,
            $defs,
            $definitions,
            $title,
            $description,
            $default,
            $deprecated,
            $readOnly,
            $writeOnly,
            $const,
            $enum,
            $allOf,
            $oneOf,
            $anyOf,
            $not,
            $enumPattern,
        );

        $this->format = $format;
        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
        $this->pattern = $pattern;
        $this->contentEncoding = $contentEncoding;
        $this->contentMediaType = $contentMediaType;

        if ($this->contentEncoding && !in_array($this->contentEncoding, ['7bit', '8bit', 'binary', 'quoted-printable', 'base16', 'base32', 'base64'])) {
            throw new InvalidArgumentException('contentEncoding must be 7bit, 8bit, binary, quoted-printable, base16, base32 or base64');
        }
    }

    public function validate($value, ?Schema $root = null): void
    {
        $root = $root ?? $this;

        parent::validate($value, $root);

        if ($this->maxLength !== null && strlen($value) > $this->maxLength) {
            throw new InvalidSchemaValueException('Expected at most ' . $this->maxLength . ' characters long, got ' . strlen($value));
        }

        if ($this->minLength !== null && strlen($value) < $this->minLength) {
            throw new InvalidSchemaValueException('Expected at least ' . $this->minLength . ' characters long, got ' . strlen($value));
        }

        // Add regex delimiters /.../ if missing
        $pattern = preg_match('/\/[^\/]+\//', $this->pattern) === 1 ? '/' . $this->pattern . '/' : $this->pattern;
        if ($this->pattern !== null && preg_match($pattern, $value) !== 1) {
            throw new InvalidSchemaValueException('Expected value to match ' . $this->pattern);
        }

        $decodedValue = $value;
        if ($this->contentEncoding !== null) {
            switch ($this->contentEncoding) {
                case '7bit':
                    throw new NotYetImplementedException('7bit decoding not yet implemented');
                    break;
                case '8bit':
                    throw new NotYetImplementedException('8bit decoding not yet implemented');
                    break;
                case 'binary':
                    throw new NotYetImplementedException('binary decoding not yet implemented');
                    break;
                case 'quoted-printable':
                    $decodedValue = quoted_printable_decode($value);
                    break;
                case 'base16':
                    throw new NotYetImplementedException('base16 decoding not yet implemented');
                    break;
                case 'base32':
                    throw new NotYetImplementedException('base32 decoding not yet implemented');
                    break;
                case 'base64':
                    $decodedValue = base64_decode($value);
                    break;
            }
        }

        if ($this->contentMediaType !== null) {
            $tmpfile = tempnam(sys_get_temp_dir(), 'jsonschemavalidation');
            file_put_contents($tmpfile, $decodedValue);
            $mimeType = mime_content_type($tmpfile);
            unlink($tmpfile);

            if ($mimeType !== false && $mimeType !== $this->contentMediaType) {
                throw new InvalidSchemaValueException('Expected content mime type to be ' . $this->contentMediaType . ' got ' . $mimeType);
            }
        }

        if ($this->format !== null) {
            switch ($this->format) {
                case self::FORMAT_DATETIME:
                    if (!preg_match('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d\+\d\d:\d+$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be date-time');
                    }
                    break;
                case self::FORMAT_TIME:
                    if (!preg_match('/^\d\d:\d\d:\d\d\+\d\d:\d+$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be time');
                    }
                    break;
                case self::FORMAT_DATE:
                    if (!preg_match('/^\d{4}-\d\d-\d\d$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be date');
                    }
                    break;
                case self::FORMAT_DURATION:
                    if (!preg_match(self::DURATION_REGEX, $value)) {
                        throw new InvalidSchemaValueException('Expected to be duration');
                    }
                    break;
                case self::FORMAT_EMAIL:
                case self::FORMAT_IDNEMAIL:
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        throw new InvalidSchemaValueException('Expected to be email');
                    }
                    break;
                case self::FORMAT_HOSTNAME:
                case self::FORMAT_IDNHOSTNAME:
                    if (!filter_var($value, FILTER_VALIDATE_DOMAIN)) {
                        throw new InvalidSchemaValueException('Expected to be hostname');
                    }
                    break;
                case self::FORMAT_IPV4:
                    if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be ipv4');
                    }
                    break;
                case self::FORMAT_IPV6:
                    if (!preg_match('/^[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be ipv6');
                    }
                    break;
                case self::FORMAT_UUID:
                    if (!preg_match('/^[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be uuid');
                    }
                    break;
                case self::FORMAT_URI:
                case self::FORMAT_URIREFERENCE:
                case self::FORMAT_IRI:
                case self::FORMAT_IRIREFERENCE:
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        throw new InvalidSchemaValueException('Expected to be uri');
                    }
                    break;
                case self::FORMAT_URITEMPLATE:
                    if (!preg_match('/^$/', $value)) {
                        throw new InvalidSchemaValueException('uri-template');
                    }
                    break;
                case self::FORMAT_JSONPOINTER:
                case self::FORMAT_RELATIVEJSONPOINTER:
                    if (!preg_match('/^\/?([^\/]+\/)*[^\/]+$/', $value)) {
                        throw new InvalidSchemaValueException('json-pointer');
                    }
                    break;
                case self::FORMAT_REGEX:
                    if (!filter_var($value, FILTER_VALIDATE_REGEXP)) {
                        throw new InvalidSchemaValueException('Expected to be email');
                    }
                    break;
            }
        }
    }

    public function jsonSerialize(): array
    {
        return parent::jsonSerialize()
            + ($this->format !== null ? ['format' => $this->format] : [])
            + ($this->minLength !== null ? ['minLength' => $this->minLength] : [])
            + ($this->maxLength !== null ? ['maxLength' => $this->maxLength] : [])
            + ($this->pattern !== null ? ['pattern' => $this->pattern] : [])
            + ($this->contentEncoding !== null ? ['contentEncoding' => $this->contentEncoding] : [])
            + ($this->contentMediaType !== null ? ['contentMediaType' => $this->contentMediaType] : []);
    }
}

//#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "PROPERTY", "ANNOTATION"})
 */
class ObjectSchema extends Schema
{
    public ?array $properties = null;
    public ?array $patternProperties = null;
    /** @var Schema|bool|null */
    public $additionalProperties = null;
    /** @var Schema|bool|null */
    public $unevaluatedProperties = null;
    /** @var string[] */
    public ?array $requiredProperties = null;
    public ?StringSchema $propertyNames = null;
    public ?int $minProperties = null;
    public ?int $maxProperties = null;

    /**
     * @param string|null $title
     * @param string|null $id
     * @param string|null $anchor
     * @param string|null $ref
     * @param array|null $defs
     * @param array|null $definitions
     * @param string|null $description
     * @param mixed $default
     * @param boolean|null $deprecated
     * @param boolean|null $readOnly
     * @param boolean|null $writeOnly
     * @param mixed $const
     * @param array|null $enum
     * @param array|null $allOf
     * @param array|null $oneOf
     * @param array|null $anyOf
     * @param Schema|null $not
     * @param string|null $enumPattern
     * @param array|null $properties
     * @param array|null $patternProperties
     * @param Schema|bool|null $additionalProperties
     * @param Schema|bool|null $unevaluatedProperties
     * @param string[]|null $requiredProperties
     * @param StringSchema|null $propertyNames
     * @param integer|null $minProperties
     * @param integer|null $maxProperties
     */
    public function __construct(
        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        ?array $defs = null,
        ?array $definitions = null,
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
        ?string $enumPattern = null,
        ?array $properties = null,
        ?array $patternProperties = null,
        $additionalProperties = null,
        $unevaluatedProperties = null,
        ?array $requiredProperties = null,
        ?StringSchema $propertyNames = null,
        ?int $minProperties = null,
        ?int $maxProperties = null
    ) {
        parent::__construct(
            Schema::TYPE_OBJECT,
            $id,
            $anchor,
            $ref,
            $defs,
            $definitions,
            $title,
            $description,
            $default,
            $deprecated,
            $readOnly,
            $writeOnly,
            $const,
            $enum,
            $allOf,
            $oneOf,
            $anyOf,
            $not,
            $enumPattern
        );

        $this->properties = $properties;
        $this->patternProperties = $patternProperties;
        $this->additionalProperties = $additionalProperties;
        $this->unevaluatedProperties = $unevaluatedProperties;
        $this->requiredProperties = $requiredProperties;
        $this->propertyNames = $propertyNames;
        $this->minProperties = $minProperties;
        $this->maxProperties = $maxProperties;

        if ($this->unevaluatedProperties !== null) {
            throw new NotYetImplementedException("unevaluatedProperties is not yet implemented");
        }
    }

    protected function resolveRef(?Schema $root): Schema
    {
        $root ??= $this;

        parent::resolveRef($root);

        /**
         * @var Schema $property
         */
        foreach ($this->properties ?? [] as $property) {
            $property->resolveRef($root);
        }

        /**
         * @var Schema $property
         */
        foreach ($this->patternProperties ?? [] as $property) {
            $property->resolveRef($root);
        }

        if ($this->additionalProperties instanceof Schema) {
            $this->additionalProperties->resolveRef($root);
        }

        if ($this->unevaluatedProperties instanceof Schema) {
            $this->unevaluatedProperties->resolveRef($root);
        }

        if ($this->propertyNames !== null) {
            $this->propertyNames->resolveRef($root);
        }

        return $this;
    }

    public function validate($value, ?Schema $root = null): void
    {
        if (!is_object($value)) {
            throw new InvalidSchemaValueException("Expected object got " . gettype($value));
        }

        $root = $root ?? $this;
        $reflection = new ReflectionClass(get_class($value));

        parent::validate($value, $root);

        if ($this->properties !== null && count($this->properties) > 0) {
            foreach ($this->properties as $key => $schema) {
                try {
                    $schema->validate($reflection->getProperty($key)->getValue($value), $root);
                } catch (ReflectionException $_) {
                    throw new InvalidSchemaValueException("Value has no property " . $key);
                }
            }
        }

        if ($this->patternProperties !== null && count($this->patternProperties) > 0) {
            foreach ($this->patternProperties as $pattern => $schema) {
                foreach ($reflection->getProperties() as $property) {
                    if (preg_match($pattern, $property->getName())) {
                        $schema->validate($property->getValue(), $root);
                    }
                }
            }
        }

        if ($this->additionalProperties !== null) {
            if (is_bool($this->additionalProperties) && !$this->additionalProperties) {
                foreach ($reflection->getProperties() as $property) {
                    if (!isset($this->properties[$property->getName()])) {
                        throw new InvalidSchemaValueException("Additionnal property " . $property->getName() . " is not allowed");
                    }
                }
            } else if ($this->additionalProperties instanceof Schema) {
                foreach ($reflection->getProperties() as $property) {
                    if (!isset($this->properties[$property->getName()])) {
                        $this->additionalProperties->validate($property->getValue());
                    }
                }
            }
        }

        if ($this->unevaluatedProperties !== null) {
            throw new NotYetImplementedException("unevaluatedProperties is not yet implemented");
        }

        if ($this->requiredProperties !== null) {
            foreach ($this->requiredProperties as $property) {
                try {
                    $reflection->getProperty($property);
                } catch (ReflectionException $_) {
                    throw new InvalidSchemaValueException("Property " . $property . " is required");
                }
            }
        }

        if ($this->propertyNames !== null) {
            foreach ($reflection->getProperties() as $property) {
                $this->propertyNames->validate($property->getName(), $root);
            }
        }

        if ($this->minProperties !== null && count($reflection->getProperties()) < $this->minProperties) {
            throw new InvalidSchemaValueException("Should have at least " . $this->minProperties . " properties got " . count($reflection->getProperties()));
        }

        if ($this->maxProperties !== null && count($reflection->getProperties()) > $this->maxProperties) {
            throw new InvalidSchemaValueException("Should have at most " . $this->maxProperties . " properties got " . count($reflection->getProperties()));
        }
    }

    public function jsonSerialize(): array
    {
        $properties = null;
        if ($this->properties !== null) {
            foreach ($this->properties as $name => $property) {
                $properties[$name] = $property->jsonSerialize();
            }
        }

        $patternProperties = null;
        if ($this->patternProperties !== null) {
            foreach ($this->patternProperties as $name => $property) {
                $patternProperties[$name] = $property->jsonSerialize();
            }
        }

        return parent::jsonSerialize()
            + ($properties !== null ? ['properties' => $properties] : [])
            + ($patternProperties !== null ? ['pattern$patternProperties' => $patternProperties] : [])
            + ($this->additionalProperties !== null ?
                [
                    'additionalProperties' => $this->additionalProperties instanceof Schema ?
                        $this->additionalProperties->jsonSerialize()
                        : $this->additionalProperties
                ] : [])
            + ($this->unevaluatedProperties !== null ?
                [
                    'unevaluatedProperties' => $this->unevaluatedProperties instanceof Schema ?
                        $this->unevaluatedProperties->jsonSerialize()
                        : $this->unevaluatedProperties
                ] : [])
            + ($this->requiredProperties !== null ? ['requiredProperties' => $this->requiredProperties] : [])
            + ($this->propertyNames !== null ? ['propertyNames' => $this->propertyNames] : [])
            + ($this->minProperties !== null ? ['minProperties' => $this->minProperties] : [])
            + ($this->maxProperties !== null ? ['maxProperties' => $this->maxProperties] : []);
    }
}
