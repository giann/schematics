<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class OneOfSchema extends Schema
{
    /**
     * @param Schema[] $schemas
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
     * @param Schema[]|null $anyOf
     * @param Schema|null $not
     * @param string|null $enumPattern
     * @param class-string<UnitEnum>|null $enumClass
     */
    public function __construct(
        array $schemas,
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
        ?array $anyOf = null,
        ?Schema $not = null,
        ?string $enumPattern = null,
        ?string $enumClass = null,
    ) {
        parent::__construct(
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
            allOf: $allOf,
            anyOf: $anyOf,
            not: $not,
            enumPattern: $enumPattern,
            enumClass: $enumClass,
            oneOf: $schemas
        );
    }
}
