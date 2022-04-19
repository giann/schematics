<?php

declare(strict_types=1);

namespace Giann\Schematics;

//#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "PROPERTY", "ANNOTATION"})
 */
class ObjectSchema extends Schema
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
     * @param array|null $properties
     * @param array|null $patternProperties
     * @param Schema|null $additionalProperties
     * @param Schema|null $unevaluatedProperties
     * @param string[]|null $required
     * @param Schema|null $propertyNames
     * @param integer|null $minProperties
     * @param integer|null $maxProperties
     * @param ?array $dependentSchemas
     * @param ?object $dependentRequired
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
        ?array $required = null,
        ?Schema $propertyNames = null,
        ?int $minProperties = null,
        ?int $maxProperties = null,
        ?array $dependentSchemas = null,
        ?object $dependentRequired = null
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

            $properties,
            $patternProperties,
            $additionalProperties,
            $unevaluatedProperties,
            $required,
            $propertyNames,
            $minProperties,
            $maxProperties,
            $dependentSchemas,
            $dependentRequired,
        );
    }
}
