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
    /** @var Schema|string|null|bool */
    public $items = null;
    /** @var Schema[] */
    public ?array $prefixItems = null;
    public ?bool $additionalItems = null;
    public ?Schema $contains = null;
    public ?int $minContains = null;
    public ?int $maxContains = null;
    public ?int $minItems;
    public ?int $maxItems;
    public ?bool $uniqueItems = null;

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
     * @param Schema|string|null|bool $items
     * @param Schema[]|null $prefixItems
     * @param boolean]|null $additionalItems
     * @param Schema|null $contains
     * @param integer|null $minContains
     * @param integer|null $maxContains
     * @param boolean|null $uniqueItems
     */
    public function __construct(
        $items = null,
        /** @var Schema[] */
        ?array $prefixItems = null,
        ?bool $additionalItems = null,
        ?Schema $contains = null,
        ?int $minContains = null,
        ?int $maxContains = null,
        ?int $minItems = null,
        ?int $maxItems = null,
        ?bool $uniqueItems = null,

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
        ?string $enumPattern = null
    ) {
        parent::__construct(
            Schema::TYPE_ARRAY,
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
        );

        $this->items = is_string($items) ? new Schema(null, null, null, $items) : $items;
        $this->prefixItems = $prefixItems;
        $this->additionalItems = $additionalItems;
        $this->contains = $contains;
        $this->minContains = $minContains;
        $this->maxContains = $maxContains;
        $this->minItems = $minItems;
        $this->maxItems = $maxItems;
        $this->uniqueItems = $uniqueItems;
    }

    public static function fromJson($json): Schema
    {
        $decoded = is_array($json) ? $json : json_decode($json, true);

        return new ArraySchema(
            is_array($decoded['items']) ? Schema::fromJson($decoded['items']) : $decoded['items'],
            isset($decoded['prefixItems']) ? array_map(fn ($el) => Schema::fromJson($el), $decoded['prefixItems']) : null,
            $decoded['additionalItems'],
            isset($decoded['contains']) ? Schema::fromJson($decoded['contains']) : null,
            $decoded['minContains'],
            $decoded['maxContains'],
            $decoded['minItems'],
            $decoded['maxItems'],
            $decoded['uniqueItems'],

            $decoded['id'],
            $decoded['$anchor'],
            $decoded['ref'],
            isset($decoded['$defs']) ? array_map(fn ($def) => self::fromJson($def), $decoded['$defs']) : null,
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

    private static function is_associative($array): bool
    {
        if (!is_array($array)) {
            return false;
        }

        if ([] === $array) {
            return true;
        }

        if (array_keys($array) !== range(0, count($array) - 1)) {
            return true;
        }

        // Dealing with a Sequential array
        return false;
    }

    public function validate($value, ?Schema $root = null, array $path = ['#']): void
    {
        $root = $root ?? $this;

        parent::validate($value, $root, $path);

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

            if ($this->minContains !== null && count($value) < $this->minContains) {
                throw new InvalidSchemaValueException("Expected at least ' . $this->minContains . ' to validate against `contains` elements got " . count($value), $path);
            }

            if ($this->maxContains !== null && count($value) > $this->maxContains) {
                throw new InvalidSchemaValueException("Expected at most ' . $this->maxContains . ' to validate against `contains` elements got " . count($value), $path);
            }
        }

        if ($this->minItems !== null && count($value) < $this->minItems) {
            throw new InvalidSchemaValueException("Expected at least ' . $this->minItems . ' elements got " . count($value), $path);
        }

        if ($this->maxItems !== null && count($value) > $this->maxItems) {
            throw new InvalidSchemaValueException("Expected at most ' . $this->maxItems . ' elements got " . count($value), $path);
        }

        if ($this->uniqueItems === true) {
            $items = [];
            foreach ($value as $item) {
                if (self::is_associative($item)) {
                    ksort($item);
                }

                if (in_array($item, $items, true)) {
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

        if ($this->items !== null) {
            if ($this->items instanceof Schema) {
                foreach ($value as $i => $item) {
                    $this->items->validate($item, $root, [...$path, 'items', $i]);
                }
            } else if (is_bool($this->items)) {
                throw new NotYetImplementedException();
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
