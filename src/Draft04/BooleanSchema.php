<?php

declare(strict_types=1);

namespace Giann\Schematics\Draft04;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class BooleanSchema extends Schema
{
    /**
     * @param string|null $schema Will be ignored if not root of the schema
     * @param string|null $id
     * @param string|null $ref
     * @param array<string,Schema|CircularReference|null> $definitions
     * @param string|null $title
     * @param string|null $description
     * @param string[]|null $examples
     * @param mixed $default

     * @param boolean|null $readOnly
     * @param boolean|null $writeOnly
     * @param mixed[]|null $enum
     * @param Schema[]|null $allOf
     * @param Schema[]|null $oneOf
     * @param Schema[]|null $anyOf
     * @param Schema|null $not
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
    ) {
        parent::__construct(
            [Type::Boolean],
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
        );
    }
}
