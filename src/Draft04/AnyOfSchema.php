<?php

declare(strict_types=1);

namespace Giann\Schematics\Draft04;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class AnyOfSchema extends Schema
{
    /**
     * @param Schema[] $schemas
     * @param string|null $schema Will be ignored if not root of the schema
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
     * @param Schema[]|null $oneOf
     * @param Schema[]|null $allOf
     * @param Schema|null $not
     * @param string|null $enumPattern
     * @param class-string<UnitEnum>|null $enumClass
     */
    public function __construct(
        array $schemas,
        ?string $schema = null,
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
        ?array $oneOf = null,
        ?array $allOf = null,
        ?Schema $not = null,
        ?string $enumPattern = null,
        ?string $enumClass = null,
    ) {
        parent::__construct(
            isRoot: $isRoot,
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
            oneOf: $oneOf,
            allOf: $allOf,
            not: $not,
            enumPattern: $enumPattern,
            enumClass: $enumClass,
            anyOf: $schemas
        );
    }
}
