<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Attribute;
use BadMethodCallException;
use JsonSerializable;
use ReflectionClass;
use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionIntersectionType;
use Exception;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionEnumPureCase;
use ReflectionException;

enum SchemaType: string
{
    case String = 'string';
    case Number = 'number';
    case Integer = 'integer';
    case Object = 'object';
    case Array = 'array';
    case Boolean = 'boolean';
    case Null = 'null';
}

class InvalidSchemaValueException extends Exception
{
}

class NotYetImplementedException extends BadMethodCallException
{
}

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Schema implements JsonSerializable
{
    public function __construct(
        public SchemaType|array|null $type = null,
        public ?string $id = null,
        public ?string $anchor = null,
        public ?string $ref = null,
        public ?array $defs = null,
        public ?array $definitions = null,
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
    ) {
        if ($this->enum === null && $enumPattern !== null) {
            $this->enum = self::patternToEnum($enumPattern);
        }
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

    public static function validateInstance(object $value): void
    {
        $schema = self::classSchema(get_class($value));

        assert($schema instanceof Schema);

        $schema->validate($value);
    }

    public function validate(mixed $value, ?Schema $root = null): void
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
        } else if ($this->type !== null && self::typeCorrespondance[$this->type->value] !== gettype($value)) {
            if ($this->enum !== null) {
                if (!in_array($value, $this->enum)) {
                    throw new InvalidSchemaValueException("Expected type " . self::typeCorrespondance[$this->type->value] . " got " . gettype($value));
                }
            } else {
                throw new InvalidSchemaValueException("Expected type " . self::typeCorrespondance[$this->type->value] . " got " . gettype($value));
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
                        var_dump($root);
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

    public static function classSchema(string $class, ?Schema $root = null): Schema | string
    {
        if ($class === '#') {
            return '#';
        }

        $classReflection = new ReflectionClass($class);
        $classAttributes = $classReflection->getAttributes();

        if (count($classAttributes) == 0) {
            throw new InvalidArgumentException('The class ' . $class . ' is not annotated');
        }

        $schemaAnnotation = current(
            array_filter(
                $classAttributes,
                fn (ReflectionAttribute $attribute) => $attribute->newInstance() instanceof ObjectSchema
            )
        );

        if ($schemaAnnotation === false) {
            throw new InvalidArgumentException('The class ' . $class . ' is not annotated');
        }

        /** @var Schema */
        $schema = $schemaAnnotation->newInstance();
        $root = $root ?? $schema;

        // Does it extends another class/schema?
        $parentReflection = $classReflection->getParentClass();
        if ($parentReflection !== false) {
            $parent = $parentReflection->getName();

            $root->definitions = $root->definitions ?? [];
            $root->definitions[$parent] = $root->definitions[$parent] ?? self::classSchema($parent, $root);

            $schema->allOf = ($schema->allOf ?? [])
                + [new Schema(ref: '#/definitions/' . $parent)];
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

            $propertyAttributes = $property->getAttributes();

            $propertySchema = current(
                array_filter(
                    $propertyAttributes,
                    fn (ReflectionAttribute $attribute) => $attribute->newInstance() instanceof Schema
                )
            );

            if ($propertySchema !== false) {
                $schema->properties[$property->getName()] = $propertySchema->newInstance();
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
                            $propertySchema = new NumberSchema(integer: true);
                            break;
                        case 'float':
                            $propertySchema = new NumberSchema(integer: false);
                            break;
                        case 'array':
                            $propertySchema = new ArraySchema();
                            break;
                        case 'bool':
                            $propertySchema = new BooleanSchema();
                            break;
                        default:
                            // Is it an enum?
                            try {
                                $enumReflection = new ReflectionEnum($type);

                                // Backed enum can only be string or integer
                                if ($enumReflection->isBacked()) {
                                    if (gettype($enumReflection->getCases()[0]->getBackingValue()) == 'string') {
                                        $propertySchema = new StringSchema(
                                            enum: array_map(
                                                fn (ReflectionEnumBackedCase $case) => $case->getValue(),
                                                $enumReflection->getCases()
                                            )
                                        );
                                    } else { // int
                                        $propertySchema = new NumberSchema(
                                            integer: true,
                                            enum: array_map(
                                                fn (ReflectionEnumBackedCase $case) => $case->getValue(),
                                                $enumReflection->getCases()
                                            )
                                        );
                                    }
                                } else {
                                    // Pure enum, we use cases names as value
                                    $cases = array_map(
                                        fn (ReflectionEnumPureCase $case) => $case->getName(),
                                        $enumReflection->getCases()
                                    );

                                    $propertySchema = new StringSchema(enum: $cases);
                                }
                            } catch (ReflectionException $_) {
                                // Is it a class?
                                try {
                                    // Self reference
                                    if ($type === $classReflection->getName()) {
                                        $type = $root == $schema ? '#' : $type;
                                    }

                                    $propertySchema = new Schema(ref: '#/definitions/' . $type);
                                } catch (Exception $_) {
                                    // Discard the property
                                }
                            }
                    }

                    if ($propertySchema !== null) {
                        if ($propertyType->allowsNull()) {
                            if ($propertySchema->ref !== null) {
                                $propertySchema->oneOf = [
                                    new Schema(type: SchemaType::Null),
                                    new Schema(ref: $propertySchema->ref)
                                ];

                                $propertySchema->type = null;
                                $propertySchema->ref = null;
                            } else {
                                assert($propertySchema->type !== null);
                                $propertySchema->type = [$propertySchema->type, SchemaType::Null];
                            }
                        }

                        $schema->properties[$property->getName()] = $propertySchema;
                    }
                } else if ($propertySchema instanceof ReflectionUnionType) {
                    // TODO: oneOf: [ type1, type2, ... ]
                    throw "Not implemented";
                } else if ($propertySchema instanceof ReflectionIntersectionType) {
                    // TODO: allOf: [ propTypeA, propTypeB, ... ]
                    throw "Not implemented";
                }
            }
        }

        return $schema;
    }

    public function jsonSerialize(): mixed
    {
        return ($this->type !== null ? [
            'type' => is_array($this->type) ? array_map(
                fn (SchemaType $type) => $type->value,
                $this->type
            ) : $this->type->value
        ] : [])
            + ($this->id !== null ? ['$id' => $this->id] : [])
            + ($this->anchor !== null ? ['$anchor' => $this->anchor] : [])
            + ($this->ref !== null ? ['$ref' => $this->ref] : [])
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

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArraySchema extends Schema
{
    public function __construct(
        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        ?array $defs = null,
        ?array $definitions = null,
        ?string $description = null,
        mixed $default = null,
        ?bool $deprecated = null,
        ?bool $readOnly = null,
        ?bool $writeOnly = null,
        mixed $const = null,
        ?array $enum = null,
        ?array $allOf = null,
        ?array $oneOf = null,
        ?array $anyOf = null,
        ?Schema $not = null,
        ?string $enumPattern = null,

        public Schema | null $items = null,
        /** @var Schema[] */
        public ?array $prefixItems = null,
        public ?Schema $contains = null,
        public ?int $minContains = null,
        public ?int $maxContains = null,
        public ?bool $uniqueItems = null,
    ) {
        parent::__construct(
            type: SchemaType::Array,
            id: $id,
            anchor: $anchor,
            ref: $ref,
            defs: $defs,
            definitions: $definitions,
            title: $title,
            description: $description,
            default: $default,
            deprecated: $deprecated,
            readOnly: $readOnly,
            writeOnly: $writeOnly,
            const: $const,
            enum: $enum,
            allOf: $allOf,
            oneOf: $oneOf,
            anyOf: $anyOf,
            not: $not,
            enumPattern: $enumPattern,
        );
    }

    public function validate(mixed $value, ?Schema $root = null): void
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

    public function jsonSerialize(): mixed
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

#[Attribute(Attribute::TARGET_PROPERTY)]
class NumberSchema extends Schema
{
    public function __construct(
        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        ?array $defs = null,
        ?array $definitions = null,
        ?string $description = null,
        mixed $default = null,
        ?bool $deprecated = null,
        ?bool $readOnly = null,
        ?bool $writeOnly = null,
        mixed $const = null,
        ?array $enum = null,
        ?array $allOf = null,
        ?array $oneOf = null,
        ?array $anyOf = null,
        ?Schema $not = null,
        ?string $enumPattern = null,

        public bool $integer = false,
        public int | float | null $multipleOf = null,
        public int | float | null $minimum = null,
        public int | float | null $maximum = null,
        public int | float | null $exclusiveMinimum = null,
        public int | float | null $exclusiveMaximum = null,
    ) {
        parent::__construct(
            type: $integer ? SchemaType::Integer : SchemaType::Number,
            id: $id,
            anchor: $anchor,
            ref: $ref,
            defs: $defs,
            definitions: $definitions,
            title: $title,
            description: $description,
            default: $default,
            deprecated: $deprecated,
            readOnly: $readOnly,
            writeOnly: $writeOnly,
            const: $const,
            enum: $enum,
            allOf: $allOf,
            oneOf: $oneOf,
            anyOf: $anyOf,
            not: $not,
            enumPattern: $enumPattern,
        );
    }

    public function validate(mixed $value, ?Schema $root = null): void
    {
        $root = $root ?? $this;

        parent::validate($value, $root);

        if (!is_int($value) && $this->integer) {
            throw new InvalidSchemaValueException("Expected an integer got " . gettype($value));
        }

        if (!$this->integer && !is_float($value)) {
            throw new InvalidSchemaValueException("Expected a float got " . gettype($value));
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

    public function jsonSerialize(): mixed
    {
        return parent::jsonSerialize()
            + ($this->multipleOf !== null ? ['multipleOf' => $this->multipleOf] : [])
            + ($this->minimum !== null ? ['minimum' => $this->minimum] : [])
            + ($this->maximum !== null ? ['maximum' => $this->maximum] : [])
            + ($this->exclusiveMinimum !== null ? ['exclusiveMinimum' => $this->exclusiveMinimum] : [])
            + ($this->exclusiveMaximum !== null ? ['exclusiveMaximum' => $this->exclusiveMaximum] : []);
    }
}


#[Attribute(Attribute::TARGET_PROPERTY)]
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
        mixed $default = null,
        ?bool $deprecated = null,
        ?bool $readOnly = null,
        ?bool $writeOnly = null,
        mixed $const = null,
        ?array $enum = null,
        ?array $allOf = null,
        ?array $oneOf = null,
        ?array $anyOf = null,
        ?Schema $not = null,
        ?string $enumPattern = null,
    ) {
        parent::__construct(
            type: SchemaType::Boolean,
            id: $id,
            anchor: $anchor,
            ref: $ref,
            defs: $defs,
            definitions: $definitions,
            title: $title,
            description: $description,
            default: $default,
            deprecated: $deprecated,
            readOnly: $readOnly,
            writeOnly: $writeOnly,
            const: $const,
            enum: $enum,
            allOf: $allOf,
            oneOf: $oneOf,
            anyOf: $anyOf,
            not: $not,
            enumPattern: $enumPattern,
        );
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
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
        mixed $default = null,
        ?bool $deprecated = null,
        ?bool $readOnly = null,
        ?bool $writeOnly = null,
        mixed $const = null,
        ?array $enum = null,
        ?array $allOf = null,
        ?array $oneOf = null,
        ?array $anyOf = null,
        ?Schema $not = null,
        ?string $enumPattern = null,
    ) {
        parent::__construct(
            type: SchemaType::Null,
            id: $id,
            anchor: $anchor,
            ref: $ref,
            defs: $defs,
            definitions: $definitions,
            title: $title,
            description: $description,
            default: $default,
            deprecated: $deprecated,
            readOnly: $readOnly,
            writeOnly: $writeOnly,
            const: $const,
            enum: $enum,
            allOf: $allOf,
            oneOf: $oneOf,
            anyOf: $anyOf,
            not: $not,
            enumPattern: $enumPattern,
        );
    }
}

// https://json-schema.org/understanding-json-schema/reference/string.html#id8
enum StringFormat: string
{
    case DateTime = 'date-time';
    case Time = 'time';
    case Date = 'date';
    case Duration = 'duration';
    case Email = 'email';
    case IdnEmail = 'idn-email';
    case Hostname = 'hostname';
    case IdnHostname = 'idn-hostname';
    case Ipv4 = 'ipv4';
    case Ipv6 = 'ipv6';
    case Uuid = 'uuid';
    case Uri = 'uri';
    case UriReference = 'uri-reference';
    case Iri = 'iri';
    case IriReference = 'iri-reference';
    case UriTemplate = 'uri-template';
    case JsonPointer = 'json-pointer';
    case RelativeJsonPointer = 'relative-json-pointer';
    case Regex = 'regex';
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class StringSchema extends Schema
{
    // http://en.wikipedia.org/wiki/ISO_8601#Durations
    const DURATION_REGEX = '/^P([0-9]+(?:[,\.][0-9]+)?Y)?([0-9]+(?:[,\.][0-9]+)?M)?([0-9]+(?:[,\.][0-9]+)?D)?(?:T([0-9]+(?:[,\.][0-9]+)?H)?([0-9]+(?:[,\.][0-9]+)?M)?([0-9]+(?:[,\.][0-9]+)?S)?)?$/';


    public function __construct(
        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        ?array $defs = null,
        ?array $definitions = null,
        ?string $description = null,
        mixed $default = null,
        ?bool $deprecated = null,
        ?bool $readOnly = null,
        ?bool $writeOnly = null,
        mixed $const = null,
        ?array $enum = null,
        ?array $allOf = null,
        ?array $oneOf = null,
        ?array $anyOf = null,
        ?Schema $not = null,
        ?string $enumPattern = null,

        public ?StringFormat $format = null,
        public ?int $minLength = null,
        public ?int $maxLength = null,
        public ?string $pattern = null,
        public ?string $contentEncoding = null,
        public ?string $contentMediaType = null,

    ) {
        parent::__construct(
            type: SchemaType::String,
            id: $id,
            anchor: $anchor,
            ref: $ref,
            defs: $defs,
            definitions: $definitions,
            title: $title,
            description: $description,
            default: $default,
            deprecated: $deprecated,
            readOnly: $readOnly,
            writeOnly: $writeOnly,
            const: $const,
            enum: $enum,
            allOf: $allOf,
            oneOf: $oneOf,
            anyOf: $anyOf,
            not: $not,
            enumPattern: $enumPattern,
        );

        if ($this->pattern !== null && !filter_var($this->pattern, FILTER_VALIDATE_REGEXP)) {
            throw new InvalidArgumentException('pattern is not a valid regexp');
        }

        if ($this->contentEncoding && !in_array($this->contentEncoding, ['7bit', '8bit', 'binary', 'quoted-printable', 'base16', 'base32', 'base64'])) {
            throw new InvalidArgumentException('contentEncoding must be 7bit, 8bit, binary, quoted-printable, base16, base32 or base64');
        }
    }

    public function validate(mixed $value, ?Schema $root = null): void
    {
        $root = $root ?? $this;

        parent::validate($value, $root);

        if ($this->maxLength !== null && strlen($value) > $this->maxLength) {
            throw new InvalidSchemaValueException('Expected at most ' . $this->maxLength . ' characters long, got ' . strlen($value));
        }

        if ($this->minLength !== null && strlen($value) < $this->minLength) {
            throw new InvalidSchemaValueException('Expected at least ' . $this->minLength . ' characters long, got ' . strlen($value));
        }

        if ($this->pattern !== null && !preg_match($this->pattern, $value)) {
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
                case StringFormat::DateTime:
                    if (!preg_match('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d\+\d\d:\d+$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be date-time');
                    }
                    break;
                case StringFormat::Time:
                    if (!preg_match('/^\d\d:\d\d:\d\d\+\d\d:\d+$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be time');
                    }
                    break;
                case StringFormat::Date:
                    if (!preg_match('/^\d{4}-\d\d-\d\d$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be date');
                    }
                    break;
                case StringFormat::Duration:
                    if (!preg_match(self::DURATION_REGEX, $value)) {
                        throw new InvalidSchemaValueException('Expected to be duration');
                    }
                    break;
                case StringFormat::Email:
                case StringFormat::IdnEmail:
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        throw new InvalidSchemaValueException('Expected to be email');
                    }
                    break;
                case StringFormat::Hostname:
                case StringFormat::IdnHostname:
                    if (!filter_var($value, FILTER_VALIDATE_DOMAIN)) {
                        throw new InvalidSchemaValueException('Expected to be hostname');
                    }
                    break;
                case StringFormat::Ipv4:
                    if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be ipv4');
                    }
                    break;
                case StringFormat::Ipv6:
                    if (!preg_match('/^[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be ipv6');
                    }
                    break;
                case StringFormat::Uuid:
                    if (!preg_match('/^[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be uuid');
                    }
                    break;
                case StringFormat::Uri:
                case StringFormat::UriReference:
                case StringFormat::Iri:
                case StringFormat::IriReference:
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        throw new InvalidSchemaValueException('Expected to be uri');
                    }
                    break;
                case StringFormat::UriTemplate:
                    if (!preg_match('/^$/', $value)) {
                        throw new InvalidSchemaValueException('uri-template');
                    }
                    break;
                case StringFormat::JsonPointer:
                case StringFormat::RelativeJsonPointer:
                    if (!preg_match('/^\/?([^\/]+\/)*[^\/]+$/', $value)) {
                        throw new InvalidSchemaValueException('json-pointer');
                    }
                    break;
                case StringFormat::Regex:
                    if (!filter_var($value, FILTER_VALIDATE_REGEXP)) {
                        throw new InvalidSchemaValueException('Expected to be email');
                    }
                    break;
            }
        }
    }

    public function jsonSerialize(): mixed
    {
        return parent::jsonSerialize()
            + ($this->format !== null ? ['format' => $this->format->value] : [])
            + ($this->minLength !== null ? ['minLength' => $this->minLength] : [])
            + ($this->maxLength !== null ? ['maxLength' => $this->maxLength] : [])
            + ($this->pattern !== null ? ['pattern' => $this->pattern] : [])
            + ($this->contentEncoding !== null ? ['contentEncoding' => $this->contentEncoding] : [])
            + ($this->contentMediaType !== null ? ['contentMediaType' => $this->contentMediaType] : []);
    }
}

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class ObjectSchema extends Schema
{
    public function __construct(
        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        ?array $defs = null,
        ?array $definitions = null,
        ?string $description = null,
        mixed $default = null,
        ?bool $deprecated = null,
        ?bool $readOnly = null,
        ?bool $writeOnly = null,
        mixed $const = null,
        ?array $enum = null,
        ?array $allOf = null,
        ?array $oneOf = null,
        ?array $anyOf = null,
        ?Schema $not = null,
        ?string $enumPattern = null,

        /** @var Schema[]|null */
        public ?array $properties = null,
        public ?array $patternProperties = null,
        public Schema | bool | null $additionalProperties = null,
        public Schema | bool | null $unevaluatedProperties = null,
        /** @var string[] */
        public ?array $requiredProperties = null,
        public ?StringSchema $propertyNames = null,
        public ?int $minProperties = null,
        public ?int $maxProperties = null,
    ) {
        parent::__construct(
            type: SchemaType::Object,
            id: $id,
            anchor: $anchor,
            ref: $ref,
            defs: $defs,
            definitions: $definitions,
            title: $title,
            description: $description,
            default: $default,
            deprecated: $deprecated,
            readOnly: $readOnly,
            writeOnly: $writeOnly,
            const: $const,
            enum: $enum,
            allOf: $allOf,
            oneOf: $oneOf,
            anyOf: $anyOf,
            not: $not,
            enumPattern: $enumPattern,
        );

        if ($this->unevaluatedProperties !== null) {
            throw new NotYetImplementedException("unevaluatedProperties is not yet implemented");
        }
    }

    public function validate(mixed $value, ?Schema $root = null): void
    {
        if (!is_object($value)) {
            throw new InvalidSchemaValueException("Expected object");
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

    public function jsonSerialize(): mixed
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
