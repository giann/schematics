<?php

declare(strict_types=1);

namespace Giann\Schematics\December2020;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class BooleanSchema extends Schema
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
     * @param string[]|null $examples
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
    ) {
        parent::__construct(
            [Type::Boolean],
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
        );
    }
}
