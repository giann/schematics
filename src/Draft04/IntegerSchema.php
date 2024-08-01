<?php

declare(strict_types=1);

namespace Giann\Schematics\Draft04;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class IntegerSchema extends Schema
{
    /**
     * @param string|null $schema Will be ignored if not root of the schema
     * @param string|null $id
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
     * @param int|null $multipleOf A numeric instance is valid only if division by this keyword's value results in an integer
     * @param int|null $minimum Validates only if the instance is greater than or exactly equal to "minimum"
     * @param int|null $maximum Validates only if the instance is less than or exactly equal to "maximum"
     * @param bool|null $exclusiveMinimum If the instance is a number, then the instance is valid only if it has a value strictly greater than (not equal to) "exclusiveMinimum"
     * @param bool|null $exclusiveMaximum If the instance is a number, then the instance is valid only if it has a value strictly less than (not equal to) "exclusiveMaximum"
     */
    public function __construct(
        ?string $schema = null,
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

        public int|null $multipleOf = null,
        public int|null $minimum = null,
        public int|null $maximum = null,
        public bool|null $exclusiveMinimum = null,
        public bool|null $exclusiveMaximum = null
    ) {
        parent::__construct(
            [Type::Integer],
            schema: $schema,
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
