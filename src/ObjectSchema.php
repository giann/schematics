<?php

declare(strict_types=1);

namespace Giann\Schematics;

use ReflectionClass;
use ReflectionException;

//#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "PROPERTY", "ANNOTATION"})
 */
class ObjectSchema extends Schema
{
    /**
     * @param string|null $title
     * @param string|null $id
     * @param string|null $anchor
     * @param string|null $ref
     * @param array|null $defs
     * @param string|null $description
     * @param mixed $default
     * @param boolean|null $deprecated
     * @param boolean|null $readOnly
     * @param boolean|null $writeOnly
     * @param mixed $const
     * @param array|null $enum
     * @param array|null $allOf
     * @param array|null $oneOf
     * @param array|null $anyOf
     * @param Schema|null $not
     * @param string|null $enumPattern
     * @param array|null $properties
     * @param array|null $patternProperties
     * @param Schema|bool|null $additionalProperties
     * @param Schema|bool|null $unevaluatedProperties
     * @param string[]|null $requiredProperties
     * @param StringSchema|null $propertyNames
     * @param integer|null $minProperties
     * @param integer|null $maxProperties
     */
    public function __construct(
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

        ?array $properties = null,
        ?array $patternProperties = null,
        $additionalProperties = null,
        $unevaluatedProperties = null,
        ?array $requiredProperties = null,
        ?StringSchema $propertyNames = null,
        ?int $minProperties = null,
        ?int $maxProperties = null
    ) {
        parent::__construct(
            Schema::TYPE_OBJECT,
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
            null,

            null,
            null,
            null,
            null,
            null,
            null,

            null,
            null,
            null,
            null,
            null,
            null,

            $properties,
            $patternProperties,
            $additionalProperties,
            $unevaluatedProperties,
            $requiredProperties,
            $propertyNames,
            $minProperties,
            $maxProperties,
        );
    }
}
