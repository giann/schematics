<?php

declare(strict_types=1);

namespace Giann\Schematics\Draft04;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class ObjectSchema extends Schema
{
    /**
     * @param string|null $id
     * @param bool $isRoot
     * @param string|null $ref
     * @param array<string,Schema|CircularReference|null> $definitions
     * @param string|null $title
     * @param string|null $description
     * @param mixed[]|null $examples
     * @param mixed $default

     * @param boolean|null $readOnly
     * @param boolean|null $writeOnly
     * @param mixed[]|null $enum
     * @param Schema[]|null $allOf
     * @param Schema[]|null $oneOf
     * @param Schema[]|null $anyOf
     * @param Schema|null $not
     * @param string|null $enumPattern
     * @param class-string<UnitEnum>|null $enumClass
     * @param array<string,Schema>|null $properties Validation succeeds if, for each name that appears in both the instance and as a name within this keyword's value, the child instance for that name successfully validates against the corresponding schema
     * @param array<string,Schema>|null $patternProperties Validation succeeds if, for each instance name that matches any regular expressions that appear as a property name in this keyword's value, the child instance for that name successfully validates against each schema that corresponds to a matching regular expression
     * @param Schema|false|null $additionalProperties Validation succeeds if, for properties not matched by "properties" and "patternProperties", the child instance validates against the "additionalProperties" schema
     * @param string[]|null $required An object instance is valid against this keyword if every item in the array is the name of a property in the instance
     * @param integer|null $minProperties An object instance is valid against "minProperties" if its number of properties is greater than, or equal to, the value of this keyword
     * @param integer|null $maxProperties An object instance is valid against "maxProperties" if its number of properties is less than, or equal to, the value of this keyword
     * @param array<string,Schema|string[]> $dependencies For all (name, schema) pair of schema dependencies, if the instance has a property by this name, then it must also validate successfully against the schema. For each (name, propertyset) pair of property dependencies, if the instance has a property by this name, then it must also have properties with the same names as propertyset
     */
    public function __construct(
        bool $isRoot = false,
        ?string $title = null,
        ?string $id = null,
        ?string $ref = null,
        array $definitions = [],
        ?string $description = null,
        ?array $examples = null,
        $default = new NullConst(),

        ?bool $readOnly = null,
        ?bool $writeOnly = null,
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
        public ?array $required = null,
        public ?int $minProperties = null,
        public ?int $maxProperties = null,
        public ?array $dependencies = null,
    ) {
        parent::__construct(
            [Type::Object],
            isRoot: $isRoot,
            id: $id,
            ref: $ref,
            definitions: $definitions,
            title: $title,
            description: $description,
            examples: $examples,
            default: $default,

            readOnly: $readOnly,
            writeOnly: $writeOnly,
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

        $dependencies = null;
        if (!empty($this->dependencies)) {
            $dependencies = $this->dependencies[array_keys($this->dependencies)[0]] instanceof Schema
                ? array_map(function ($dep) {
                    assert($dep instanceof Schema);
                    return $dep->jsonSerialize();
                }, $this->dependencies)
                : $this->dependencies;
        }

        return $serialized
            + ($properties !== null ? ['properties' => $properties] : [])
            + ($this->additionalProperties !== null ?
                [
                    'additionalProperties' => $this->additionalProperties
                ] : [])
            + ($this->required !== null ? ['required' => $this->required] : [])
            + ($this->minProperties !== null ? ['minProperties' => $this->minProperties] : [])
            + ($this->maxProperties !== null ? ['maxProperties' => $this->maxProperties] : [])
            + ($dependencies !== null ? ['dependencies' => $dependencies] : []);
    }
}
