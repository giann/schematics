<?php

declare(strict_types=1);

namespace Giann\Schematics\December2020;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class ObjectSchema extends Schema
{
    /**
     * @param string|null $schema Will be ignored if not root of the schema
     * @param string|null $id
     * @param string|null $anchor
     * @param string|null $ref
     * @param array<string,Schema|CircularReference|null> $defs
     * @param string|null $title
     * @param string|null $description
     * @param string|null $comment
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
     * @param Schema|null $if
     * @param Schema|null $then
     * @param Schema|null $else
     * @param string|null $enumPattern
     * @param class-string<UnitEnum>|null $enumClass
     * @param array<string,Schema>|null $properties Validation succeeds if, for each name that appears in both the instance and as a name within this keyword's value, the child instance for that name successfully validates against the corresponding schema
     * @param array<string,Schema>|null $patternProperties Validation succeeds if, for each instance name that matches any regular expressions that appear as a property name in this keyword's value, the child instance for that name successfully validates against each schema that corresponds to a matching regular expression
     * @param Schema|false|null $additionalProperties Validation succeeds if, for properties not matched by "properties" and "patternProperties", the child instance validates against the "additionalProperties" schema
     * @param Schema|null $unevaluatedProperties Applies to properties not matched by "properties", "patternProperties" and "additionalProperties"
     * @param string[]|null $required An object instance is valid against this keyword if every item in the array is the name of a property in the instance
     * @param Schema|null $propertyNames If the instance is an object, this keyword validates if every property name in the instance validates against the provided schema. Note the property name that the schema is testing will always be a string
     * @param integer|null $minProperties An object instance is valid against "minProperties" if its number of properties is greater than, or equal to, the value of this keyword
     * @param integer|null $maxProperties An object instance is valid against "maxProperties" if its number of properties is less than, or equal to, the value of this keyword
     * @param array<string,Schema>|null $dependentSchemas If the object key is a property in the instance, the entire instance must validate against the subschema. Its use is dependent on the presence of the property
     * @param ?array<string,string[]> $dependentRequired Validation succeeds if, for each name that appears in both the instance and as a name within this keyword's value, every item in the corresponding array is also the name of a property in the instance
     */
    public function __construct(
        ?string $schema = null,
        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        array $defs = [],
        ?string $description = null,
        ?string $comment = null,
        ?array $examples = null,
        $default = new NullConst(),
        ?bool $deprecated = null,
        ?bool $readOnly = null,
        ?bool $writeOnly = null,
        $const = null,
        ?array $enum = null,
        ?array $allOf = null,
        ?array $oneOf = null,
        ?array $anyOf = null,
        ?Schema $not = null,
        ?Schema $if = null,
        ?Schema $then = null,
        ?Schema $else = null,
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
            schema: $schema,
            id: $id,
            anchor: $anchor,
            ref: $ref,
            defs: $defs,
            title: $title,
            description: $description,
            comment: $comment,
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
            if: $if,
            then: $then,
            else: $else,
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
                $property->isRoot = false;
                $properties[$name] = $property->jsonSerialize();
            }
        }

        if (!empty($properties)) {
            $serialized['properties'] = $properties;
        }

        $patternProperties = null;
        if ($this->patternProperties !== null) {
            foreach ($this->patternProperties as $name => $property) {
                $property->isRoot = false;
                $patternProperties[$name] = $property->jsonSerialize();
            }
        }

        if (!empty($patternProperties)) {
            $serialized['patternProperties'] = $patternProperties;
        }

        $dependentSchemas = null;
        if ($this->dependentSchemas !== null) {
            foreach ($this->dependentSchemas as $name => $property) {
                $property->isRoot = false;
                $dependentSchemas[$name] = $property->jsonSerialize();
            }
        }

        if (!empty($dependentSchemas)) {
            $serialized['dependentSchemas'] = $dependentSchemas;
        }

        if ($this->propertyNames !== null) {
            $this->propertyNames->isRoot = false;
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
