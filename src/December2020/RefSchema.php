<?php

declare(strict_types=1);

namespace Giann\Schematics\December2020;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class RefSchema extends Schema
{
    /**
     * @param string|null $schema Will be ignored if not root of the schema
     * @param string|null $id
     * @param string|null $anchor
     * @param string $ref
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
     */
    public function __construct(
        string $ref,
        ?string $schema = null,
        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
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
    ) {
        parent::__construct(
            schema: $schema,
            ref: $ref,
            id: $id,
            anchor: $anchor,
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
}
