<?php

declare(strict_types=1);

namespace Giann\Schematics;

//#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class NullSchema extends Schema
{
    /**
     * @param string|null $title
     * @param string|null $id
     * @param string|null $anchor
     * @param string|null $ref
     * @param array|null $defs
     * @param array|null $definitions
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
     */
    public function __construct(
        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        ?array $defs = null,
        ?array $definitions = null,
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
        ?string $enumPattern = null
    ) {
        parent::__construct(
            Schema::TYPE_NULL,
            $id,
            $anchor,
            $ref,
            $defs,
            $definitions,
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
        );
    }

    public static function fromJson(string $json): Schema
    {
        $decoded = json_decode($json, true);

        return new NullSchema(
            $decoded['id'],
            $decoded['anchor'],
            $decoded['ref'],
            isset($decoded['defs']) ? array_map(fn ($def) => self::fromJson($def), $decoded['defs']) : null,
            isset($decoded['definitions']) ? array_map(fn ($def) => self::fromJson($def), $decoded['definitions']) : null,
            $decoded['title'],
            $decoded['description'],
            $decoded['default'],
            $decoded['deprecated'],
            $decoded['readOnly'],
            $decoded['writeOnly'],
            $decoded['const'],
            $decoded['enum'],
            isset($decoded['allOf']) ? array_map(fn ($def) => self::fromJson($def), $decoded['allOf']) : null,
            isset($decoded['oneOf']) ? array_map(fn ($def) => self::fromJson($def), $decoded['oneOf']) : null,
            isset($decoded['anyOf']) ? array_map(fn ($def) => self::fromJson($def), $decoded['anyOf']) : null,
            isset($decoded['not']) ? self::fromJson($decoded['not']) : null,
        );
    }
}
