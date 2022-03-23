<?php

declare(strict_types=1);

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

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Schema implements JsonSerializable
{
    public function __construct(
        public SchemaType|array $type,
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
    ) {
    }

    protected function classSchema(string $class): Schema
    {
        $classReflection = new ReflectionClass($class);
        $classAttributes = $classReflection->getAttributes();

        if (count($classAttributes) == 0) {
            throw new InvalidArgumentException('The class ' . $class . ' is not annotated');
        }

        /** @var ObjectSchema */
        $rootSchema = current(
            array_filter(
                $classAttributes,
                fn (ReflectionAttribute $attribute) => $attribute->newInstance() instanceof ObjectSchema
            )
        );

        if ($rootSchema === false) {
            throw new InvalidArgumentException('The class ' . $class . ' is not annotated');
        }

        // Does it extends another class/schema?
        $parentReflection = $classReflection->getParentClass();
        if ($parentReflection !== false) {
            $rootSchema->allOf = ($rootSchema->allOf ?? []) + [self::classSchema($parentReflection->getName())];
        }

        // TODO: we could also use getters
        $properties = $classReflection->getProperties();
        foreach ($properties as $property) {
            $propertyAttributes = $property->getAttributes();

            $propertySchema = current(
                array_filter(
                    $propertyAttributes,
                    fn (ReflectionAttribute $attribute) => $attribute->newInstance() instanceof Schema
                )
            );

            if ($propertySchema !== null) {
                $rootSchema->properties[$property->getName()] = $propertySchema;
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
                        default: // Is it a class?
                            try {
                                $propertySchema = self::classSchema($type);
                            } catch (Exception $_) {
                                // Discard the property
                            }
                    }

                    if ($propertySchema !== null && $propertyType->allowsNull()) {
                        $propertySchema->type = [$propertySchema->type, SchemaType::Null];
                    }
                } else if ($propertySchema instanceof ReflectionUnionType) {
                    throw "Not implemented";
                } else if ($propertySchema instanceof ReflectionIntersectionType) {
                    throw "Not implemented";
                }
            }
        }

        return $rootSchema;
    }

    public function jsonSerialize(): mixed
    {
        $defs = null;
        if ($this->defs !== null) {
            $defs = [];
            foreach ($this->defs as $key => $value) {
                $defs[$key] = self::classSchema($value);
            }
        }

        $definitions = null;
        if ($this->definitions !== null) {
            $definitions = [];
            foreach ($this->definitions as $key => $value) {
                $definitions[$key] = self::classSchema($value);
            }
        }

        return ['type' => $this->type]
            + ($this->id !== null ? ['$id' => $this->id] : [])
            + ($this->anchor !== null ? ['$anchor' => $this->anchor] : [])
            + ($this->ref !== null ? ['$ref' => self::classSchema($this->ref)] : null)
            + ($defs !== null ? ['$defs' => $defs] : [])
            + ($definitions !== null ? ['definitions' => $definitions] : [])
            + ($this->title !== null ? ['title' => $this->title] : [])
            + ($this->description !== null ? ['description' => $this->description] : [])
            + ($this->default !== null ? ['default' => $this->default] : [])
            + ($this->deprecated !== null ? ['deprecated' => $this->deprecated] : [])
            + ($this->readOnly !== null ? ['readOnly' => $this->readOnly] : [])
            + ($this->writeOnly !== null ? ['writeOnly' => $this->writeOnly] : [])
            + ($this->const !== null ? ['const' => $this->const] : [])
            + ($this->enum !== null ? ['enum' => $this->enum] : []);
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

        public Schema | bool | null $items = null,
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
        );
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

        public bool $integer,
        public int | float | null $multipleOf = null,
        public int | float | null $minimum = null,
        public int | float | null $maximum = null,
        public int | float | null $exclusiveMinimum = null,
        public int | float | null $exclusiveMaximum = null,
    ) {
        parent::__construct(
            type: SchemaType::Number,
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
        );
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

        public ?StringFormat $format = null,
        public ?int $minLength = null,
        public ?int $maxLength = null,
        public ?string $pattern = null,
        public ?string $contentType = null,
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
        );
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

        public ?array $properties = null,
        public ?array $patternProperties = null,
        public Schema | bool | null $additionalProperties = null,
        public Schema | bool | null $unevaluatedProperties = null,
        /** @var string[] */
        public ?array $requiredProperties = null,
        public ?StringSchema $propertyNames = null,
        public ?int $minProperties = null,
        public ?int $maxProperties = null,

        public ?array $allOf = null,
        public ?array $oneOf = null,
        public ?array $anyOf = null,
        public ?Schema $not = null,
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
        );
    }
}
