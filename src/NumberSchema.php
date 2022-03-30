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
    public bool $integer = false;
    /** @var int|double|null  */
    public $multipleOf = null;
    /** @var int|double|null  */
    public $minimum = null;
    /** @var int|double|null  */
    public $maximum = null;
    /** @var int|double|null  */
    public $exclusiveMinimum = null;
    /** @var int|double|null  */
    public $exclusiveMaximum = null;

    /**
     * @param boolean $integer
     * @param int|double|null $multipleOf
     * @param int|double|null $minimum
     * @param int|double|null $maximum
     * @param int|double|null $exclusiveMinimum
     * @param int|double|null $exclusiveMaximum
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
        bool $integer = false,
        $multipleOf = null,
        $minimum = null,
        $maximum = null,
        $exclusiveMinimum = null,
        $exclusiveMaximum = null,

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
            $integer ? Schema::TYPE_INTEGER : Schema::TYPE_NUMBER,
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
            $enumPattern
        );

        $this->integer = $integer;
        $this->multipleOf = $multipleOf;
        $this->minimum = $minimum;
        $this->maximum = $maximum;
        $this->exclusiveMinimum = $exclusiveMinimum;
        $this->exclusiveMaximum = $exclusiveMaximum;
    }

    public function validate($value, ?Schema $root = null): void
    {
        $root = $root ?? $this;

        parent::validate($value, $root);

        if (!is_int($value) && $this->integer) {
            throw new InvalidSchemaValueException("Expected an integer got " . gettype($value));
        }

        if (!$this->integer && !is_double($value)) {
            throw new InvalidSchemaValueException("Expected a double got " . gettype($value));
        }

        if ($this->multipleOf !== null && $value % $this->multipleOf !== 0) {
            throw new InvalidSchemaValueException("Expected a multiple of " . $this->multipleOf);
        }

        if ($this->minimum !== null && $value < $this->minimum) {
            throw new InvalidSchemaValueException("Expected value to be less or equal to " . $this->minimum);
        }

        if ($this->maximum !== null && $value > $this->maximum) {
            throw new InvalidSchemaValueException("Expected value to be greater or equal to " . $this->maximum);
        }

        if ($this->exclusiveMinimum !== null && $value <= $this->exclusiveMinimum) {
            throw new InvalidSchemaValueException("Expected value to be less than " . $this->exclusiveMinimum);
        }

        if ($this->exclusiveMaximum !== null && $value >= $this->exclusiveMaximum) {
            throw new InvalidSchemaValueException("Expected value to be greather than " . $this->exclusiveMaximum);
        }
    }

    public function jsonSerialize(): array
    {
        return parent::jsonSerialize()
            + ($this->multipleOf !== null ? ['multipleOf' => $this->multipleOf] : [])
            + ($this->minimum !== null ? ['minimum' => $this->minimum] : [])
            + ($this->maximum !== null ? ['maximum' => $this->maximum] : [])
            + ($this->exclusiveMinimum !== null ? ['exclusiveMinimum' => $this->exclusiveMinimum] : [])
            + ($this->exclusiveMaximum !== null ? ['exclusiveMaximum' => $this->exclusiveMaximum] : []);
    }
}
