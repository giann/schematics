<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArraySchema extends Schema
{
    /**
     * @param string|null $id
     * @param bool $isRoot
     * @param string|null $draft
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
     * @param Schema|null $items Applies its subschema to all instance elements at indexes greater than the length of the "prefixItems" array in the same schema object, as reported by the annotation result of that "prefixItems" keyword. If no such annotation result exists, "items" applies its subschema to all instance array elements
     * @param Schema[]|null $prefixItems Validation succeeds if each element of the instance validates against the schema at the same position, if any
     * @param Schema|null $contains An array instance is valid against "contains" if at least one of its elements is valid against the given schema
     * @param integer|null $minContains An instance array is valid against "minContains" in two ways, depending on the form of the annotation result of an adjacent "contains" keyword. The first way is if the annotation result is an array and the length of that array is greater than or equal to the "minContains" value. The second way is if the annotation result is a boolean "true" and the instance array length is greater than or equal to the "minContains" value
     * @param integer|null $maxContains An instance array is valid against "maxContains" in two ways, depending on the form of the annotation result of an adjacent "contains" keyword. The first way is if the annotation result is an array and the length of that array is less than or equal to the "maxContains" value. The second way is if the annotation result is a boolean "true" and the instance array length is less than or equal to the "maxContains" value
     * @param boolean|null $uniqueItems If this keyword has boolean value false, the instance validates successfully. If it has boolean value true, the instance validates successfully if all of its elements are unique
     * @param null|Schema $unevaluatedItems Applies to items not evaluated by "prefixItems", "items" or "contains"
     */
    public function __construct(
        bool $isRoot = false,
        ?string $draft = Draft::December2020->value,
        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        array $defs = [],
        ?string $description = null,
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
        ?string $enumPattern = null,
        ?string $enumClass = null,

        public ?Schema $items = null,
        public ?array $prefixItems = null,
        public ?Schema $contains = null,
        public ?int $minContains = null,
        public ?int $maxContains = null,
        public ?int $minItems = null,
        public ?int $maxItems = null,
        public ?bool $uniqueItems = null,
        public ?Schema $unevaluatedItems = null
    ) {
        parent::__construct(
            [Type::Array],
            isRoot: $isRoot,
            draft: $draft,
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
            enumPattern: $enumPattern,
            enumClass: $enumClass,
            allOf: $allOf,
            oneOf: $oneOf,
            anyOf: $anyOf,
            not: $not,
        );
    }

    protected function resolveRef(?Schema $root): Schema
    {
        parent::resolveRef($root);

        if ($this->items instanceof Schema) {
            $this->items->resolveRef($root);
        }

        foreach ($this->prefixItems ?? [] as $schema) {
            $schema->resolveRef($root);
        }

        if ($this->contains instanceof Schema) {
            $this->contains->resolveRef($root);
        }

        return $this;
    }

    public function jsonSerialize(): array
    {
        $serialized = parent::jsonSerialize();

        return $serialized
            + ($this->items !== null ? ['items' => $this->items->jsonSerialize()] : [])
            + ($this->prefixItems !== null ? ['prefixItems' => $this->prefixItems] : [])
            + ($this->contains !== null ? ['contains' => $this->contains->jsonSerialize()] : [])
            + ($this->minContains !== null ? ['minContains' => $this->minContains] : [])
            + ($this->maxContains !== null ? ['maxContains' => $this->maxContains] : [])
            + ($this->uniqueItems !== null ? ['uniqueItems' => $this->uniqueItems] : [])
            + ($this->unevaluatedItems !== null ? ['unevaluatedItems' => $this->unevaluatedItems->jsonSerialize()] : []);
    }
}
