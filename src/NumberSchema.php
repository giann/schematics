<?php

declare(strict_types=1);

namespace Giann\Schematics;

//#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class NumberSchema extends Schema
{
    /**
     * @param string|null $id
     * @param string|null $anchor
     * @param string|null $ref
     * @param array|null $defs
     * @param string|null $title
     * @param string|null $description
     * @param mixed $default
     * @param boolean|null $deprecated
     * @param boolean|null $readOnly
     * @param boolean|null $writeOnly
     * @param mixed $const
     * @param array|null $enum
     * @param Schema[]|null $allOf
     * @param Schema[]|null $oneOf
     * @param Schema[]|null $anyOf
     * @param Schema|null $not
     * @param string|null $enumPattern
     * 
     * @param boolean $integer
     * @param int|double|null $multipleOf
     * @param int|double|null $minimum
     * @param int|double|null $maximum
     * @param int|double|null $exclusiveMinimum
     * @param int|double|null $exclusiveMaximum
     */
    public function __construct(
        bool $integer = false,

        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        ?array $defs = null,
        ?string $description = null,
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

        $multipleOf = null,
        $minimum = null,
        $maximum = null,
        $exclusiveMinimum = null,
        $exclusiveMaximum = null
    ) {
        parent::__construct(
            $integer ? Schema::TYPE_INTEGER : Schema::TYPE_NUMBER,
            $id,
            $anchor,
            $ref,
            $defs,
            $title,
            $description,
            $default,
            $deprecated,
            $readOnly,
            $writeOnly,
            $const,
            $enum,
            $allOf,
            $oneOf,
            $anyOf,
            $not,
            $enumPattern,

            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,

            $multipleOf,
            $minimum,
            $maximum,
            $exclusiveMinimum,
            $exclusiveMaximum,
        );
    }
}
