<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IntegerSchema extends Schema
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
     * @param int|null $multipleOf A numeric instance is valid only if division by this keyword's value results in an integer
     * @param int|null $minimum Validates only if the instance is greater than or exactly equal to "minimum"
     * @param int|null $maximum Validates only if the instance is less than or exactly equal to "maximum"
     * @param int|null $exclusiveMinimum If the instance is a number, then the instance is valid only if it has a value strictly greater than (not equal to) "exclusiveMinimum"
     * @param int|null $exclusiveMaximum If the instance is a number, then the instance is valid only if it has a value strictly less than (not equal to) "exclusiveMaximum"
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

        public int|float|null $multipleOf = null,
        public int|float|null $minimum = null,
        public int|float|null $maximum = null,
        public int|float|null $exclusiveMinimum = null,
        public int|float|null $exclusiveMaximum = null
    ) {
        parent::__construct(
            [Type::Integer],
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

    public function jsonSerialize(): array
    {
        $serialized = parent::jsonSerialize();

        return $serialized
            + ($this->multipleOf !== null ? ['multipleOf' => $this->multipleOf] : [])
            + ($this->minimum !== null ? ['minimum' => $this->minimum] : [])
            + ($this->maximum !== null ? ['maximum' => $this->maximum] : [])
            + ($this->exclusiveMinimum !== null ? ['exclusiveMinimum' => $this->exclusiveMinimum] : [])
            + ($this->exclusiveMaximum !== null ? ['exclusiveMaximum' => $this->exclusiveMaximum] : []);
    }
}
