<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Attribute;
use JsonSerializable;
use ReflectionClass;
use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionIntersectionType;
use Exception;

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
    ) {
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

        $rootSchemaAnnotation = current(
            array_filter(
                $classAttributes,
                fn (ReflectionAttribute $attribute) => $attribute->newInstance() instanceof ObjectSchema
            )
        );

        if ($rootSchemaAnnotation === false) {
            throw new InvalidArgumentException('The class ' . $class . ' is not annotated');
        }

        /** @var Schema */
        $rootSchema = $rootSchemaAnnotation->newInstance();
        $root = $root ?? $rootSchema;

        // Does it extends another class/schema?
        $parentReflection = $classReflection->getParentClass();
        if ($parentReflection !== false) {
            $parent = $parentReflection->getName();

            $root->definitions = $root->definitions ?? [];
            $root->definitions[$parent] = $root->definitions[$parent] ?? self::classSchema($parent, $root);

            $rootSchema->allOf = ($rootSchema->allOf ?? [])
                + [new Schema(ref: $parent)];
        }

        // TODO: we could also use getters
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
                $rootSchema->properties[$property->getName()] = $propertySchema->newInstance();
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
                                // Self reference
                                if ($type === $classReflection->getName()) {
                                    $type = $root == $rootSchema ? '#' : $type;
                                }

                                $propertySchema = new Schema(ref: $type);
                            } catch (Exception $_) {
                                // Discard the property
                            }
                    }

                    if ($propertySchema !== null) {
                        if ($propertyType->allowsNull()) {
                            $propertySchema->type = [$propertySchema->type, SchemaType::Null];
                        }

                        $rootSchema->properties[$property->getName()] = $propertySchema;
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

        return $rootSchema;
    }

    public function jsonSerialize(): mixed
    {
        return ($this->type !== null ? ['type' => $this->type->value] : [])
            + ($this->id !== null ? ['$id' => $this->id] : [])
            + ($this->anchor !== null ? ['$anchor' => $this->anchor] : [])
            + ($this->ref !== null ? ['$ref' => '#/definitions/' . $this->ref] : [])
            + ($this->defs !== null ? ['$defs' => $this->defs] : [])
            + ($this->definitions !== null ? ['definitions' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->definitions)] : [])
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
        );
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
        );
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

    public function jsonSerialize(): mixed
    {
        return parent::jsonSerialize()
            + ($this->format !== null ? ['format' => $this->format->value] : [])
            + ($this->minLength !== null ? ['minLength' => $this->minLength] : [])
            + ($this->maxLength !== null ? ['maxLength' => $this->maxLength] : [])
            + ($this->pattern !== null ? ['pattern' => $this->pattern] : [])
            + ($this->contentType !== null ? ['contentType' => $this->contentType] : [])
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
            + ($this->maxProperties !== null ? ['maxProperties' => $this->maxProperties] : [])
            + ($this->allOf !== null ? ['allOf' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->allOf)] : [])
            + ($this->oneOf !== null ? ['oneOf' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->oneOf)] : [])
            + ($this->anyOf !== null ? ['anyOf' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->anyOf)] : [])
            + ($this->not !== null ? ['not' => $this->not->jsonSerialize()] : []);
    }
}
