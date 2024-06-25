<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class ObjectSchema extends Schema
{
    /**
     * @param string|null $id
     * @param string|null $anchor
     * @param string|null $ref
     * @param array<string,Schema|CircularReference|null> $defs
     * @param string|null $title
     * @param string|null $description
     * @param mixed[]|null $examples
     * @param mixed $default
     * @param boolean|null $deprecated
     * @param boolean|null $readOnly
     * @param boolean|null $writeOnly
     * @param mixed $const
     * @param mixed[]|null $enum
     * @param Schema[]|null $allOf
     * @param Schema[]|null $oneOf
     * @param Schema[]|null $anyOf
     * @param Schema|null $not
     * @param string|null $enumPattern
     * @param class-string<UnitEnum>|null $enumClass
     * @param array<string,Schema>|null $properties
     * @param array<string,Schema>|null $patternProperties
     * @param Schema|false|null $additionalProperties
     * @param Schema|null $unevaluatedProperties
     * @param string[]|null $required
     * @param Schema|null $propertyNames
     * @param integer|null $minProperties
     * @param integer|null $maxProperties
     * @param array<string,Schema>|null $dependentSchemas
     * @param ?array<string,string[]> $dependentRequired
     */
    public function __construct(
        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        array $defs = [],
        ?string $description = null,
        ?array $examples = null,
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
        ?string $enumClass = null,

        public ?array $properties = null,
        public ?array $patternProperties = null,
        public Schema|false|null $additionalProperties = null,
        public ?Schema $unevaluatedProperties = null,
        public ?array $required = null,
        public ?Schema $propertyNames = null,
        public ?int $minProperties = null,
        public ?int $maxProperties = null,
        public ?array $dependentSchemas = null,
        public ?array $dependentRequired = null
    ) {
        parent::__construct(
            [Type::Object],
            id: $id,
            anchor: $anchor,
            ref: $ref,
            defs: $defs,
            title: $title,
            description: $description,
            examples: $examples,
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
            enumClass: $enumClass,
        );
    }

    protected function resolveRef(?Schema $root): Schema
    {
        parent::resolveRef($root);

        foreach ($this->properties ?? [] as $property) {
            $property->resolveRef($root);
        }

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

    public function jsonSerialize(): array
    {
        $serialized = parent::jsonSerialize();

        $properties = null;
        if ($this->properties !== null) {
            foreach ($this->properties as $name => $property) {
                $properties[$name] = $property->jsonSerialize();
            }
        }

        if (!empty($properties)) {
            $serialized['properties'] = $properties;
        }

        $patternProperties = null;
        if ($this->patternProperties !== null) {
            foreach ($this->patternProperties as $name => $property) {
                $patternProperties[$name] = $property->jsonSerialize();
            }
        }

        if (!empty($patternProperties)) {
            $serialized['patternProperties'] = $patternProperties;
        }

        $dependentSchemas = null;
        if ($this->dependentSchemas !== null) {
            foreach ($this->dependentSchemas as $name => $property) {
                $dependentSchemas[$name] = $property->jsonSerialize();
            }
        }

        if (!empty($dependentSchemas)) {
            $serialized['dependentSchemas'] = $dependentSchemas;
        }

        return $serialized
            + ($properties !== null ? ['properties' => $properties] : [])
            + ($this->additionalProperties !== null ?
                [
                    'additionalProperties' => $this->additionalProperties
                ] : [])
            + ($this->unevaluatedProperties !== null ?
                [
                    'unevaluatedProperties' => $this->unevaluatedProperties
                ] : [])
            + ($this->required !== null ? ['required' => $this->required] : [])
            + ($this->propertyNames !== null ? ['propertyNames' => $this->propertyNames] : [])
            + ($this->minProperties !== null ? ['minProperties' => $this->minProperties] : [])
            + ($this->maxProperties !== null ? ['maxProperties' => $this->maxProperties] : [])
            + ($dependentSchemas !== null ? $dependentSchemas : [])
            + ($this->dependentRequired !== null ? ['dependentRequired' => $this->dependentRequired] : []);
    }
}
