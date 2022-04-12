<?php

declare(strict_types=1);

namespace Giann\Schematics;

//#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class ArraySchema extends Schema
{
    public ?Schema $items = null;
    /** @var Schema[] */
    public ?array $prefixItems = null;
    public ?Schema $contains = null;
    public ?int $minContains = null;
    public ?int $maxContains = null;
    public ?bool $uniqueItems = null;

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
     * @param Schema|string|null $items
     * @param array|null $prefixItems
     * @param Schema|null $contains
     * @param integer|null $minContains
     * @param integer|null $maxContains
     * @param boolean|null $uniqueItems
     */
    public function __construct(
        $items = null,
        /** @var Schema[] */
        ?array $prefixItems = null,
        ?Schema $contains = null,
        ?int $minContains = null,
        ?int $maxContains = null,
        ?bool $uniqueItems = null,

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
            Schema::TYPE_ARRAY,
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

        $this->items = is_string($items) ? new Schema(null, null, null, $items) : $items;
        $this->prefixItems = $prefixItems;
        $this->contains = $contains;
        $this->minContains = $minContains;
        $this->maxContains = $maxContains;
        $this->uniqueItems = $uniqueItems;
    }

    public static function fromJson(string $json): Schema
    {
        $decoded = json_decode($json, true);

        return new ArraySchema(
            is_array($decoded['items']) ? Schema::fromJson($decoded['items']) : $decoded['items'],
            isset($decoded['prefixItems']) ? array_map(fn ($el) => Schema::fromJson($el), $decoded['prefixItems']) : null,
            isset($decoded['contains']) ? Schema::fromJson($decoded['contains']) : null,
            $decoded['minContains'],
            $decoded['maxContains'],
            $decoded['uniqueItems'],

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

    protected function resolveRef(?Schema $root): Schema
    {
        $root ??= $this;

        parent::resolveRef($root);

        if ($this->items !== null) {
            $this->items->resolveRef($root);
        }

        foreach ($this->prefixItems ?? [] as $schema) {
            $schema->resolveRef($root);
        }

        return $this;
    }

    public function validate($value, ?Schema $root = null, array $path = ['#']): void
    {
        $root = $root ?? $this;

        parent::validate($value, $root, $path);

        if ($this->minContains !== null && count($value) < $this->minContains) {
            throw new InvalidSchemaValueException("Expected at least ' . $this->minContains . ' elements got " . count($value), $path);
        }

        if ($this->maxContains !== null && count($value) > $this->maxContains) {
            throw new InvalidSchemaValueException("Expected at most ' . $this->maxContains . ' elements got " . count($value), $path);
        }

        if ($this->uniqueItems === true) {
            $items = [];
            foreach ($value as $item) {
                if (in_array($item, $items)) {
                    throw new InvalidSchemaValueException('Expected unique items', $path);
                }

                $items[] = $item;
            }
        }

        if ($this->prefixItems !== null && count($this->prefixItems) > 0) {
            foreach ($this->prefixItems as $i => $prefixItem) {
                $prefixItem->validate($value[$i], $root, [...$path, 'prefixItems', $i]);
            }
        }

        if ($this->contains !== null) {
            $contains = false;
            foreach ($value as $i => $item) {
                try {
                    $this->contains->validate($item, $root, [...$path, 'contains', $i]);

                    $contains = true;

                    break;
                } catch (InvalidSchemaValueException $_) {
                }
            }

            if (!$contains) {
                throw new InvalidSchemaValueException('Expected at least one item to validate against:\n' . json_encode($this->contains, JSON_PRETTY_PRINT), $path);
            }
        }

        if ($this->items !== null) {
            foreach ($value as $i => $item) {
                $this->items->validate($item, $root, [...$path, 'items', $i]);
            }
        }
    }

    public function jsonSerialize(): array
    {
        return parent::jsonSerialize()
            + ($this->items !== null ? ['items' => $this->items->jsonSerialize()] : [])
            + ($this->prefixItems !== null ? ['prefixItems' => $this->prefixItems] : [])
            + ($this->contains !== null ? ['contains' => $this->contains->jsonSerialize()] : [])
            + ($this->minContains !== null ? ['minContains' => $this->minContains] : [])
            + ($this->maxContains !== null ? ['maxContains' => $this->maxContains] : [])
            + ($this->uniqueItems !== null ? ['uniqueItems' => $this->uniqueItems] : []);
    }
}
