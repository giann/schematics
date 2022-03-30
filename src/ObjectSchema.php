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
    public ?array $properties = null;
    public ?array $patternProperties = null;
    /** @var Schema|bool|null */
    public $additionalProperties = null;
    /** @var Schema|bool|null */
    public $unevaluatedProperties = null;
    /** @var string[] */
    public ?array $requiredProperties = null;
    public ?StringSchema $propertyNames = null;
    public ?int $minProperties = null;
    public ?int $maxProperties = null;

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

        $this->properties = $properties;
        $this->patternProperties = $patternProperties;
        $this->additionalProperties = $additionalProperties;
        $this->unevaluatedProperties = $unevaluatedProperties;
        $this->requiredProperties = $requiredProperties;
        $this->propertyNames = $propertyNames;
        $this->minProperties = $minProperties;
        $this->maxProperties = $maxProperties;

        if ($this->unevaluatedProperties !== null) {
            throw new NotYetImplementedException("unevaluatedProperties is not yet implemented");
        }
    }

    protected function resolveRef(?Schema $root): Schema
    {
        $root ??= $this;

        parent::resolveRef($root);

        /**
         * @var Schema $property
         */
        foreach ($this->properties ?? [] as $property) {
            $property->resolveRef($root);
        }

        /**
         * @var Schema $property
         */
        foreach ($this->patternProperties ?? [] as $property) {
            $property->resolveRef($root);
        }

        if ($this->additionalProperties instanceof Schema) {
            $this->additionalProperties->resolveRef($root);
        }

        if ($this->unevaluatedProperties instanceof Schema) {
            $this->unevaluatedProperties->resolveRef($root);
        }

        if ($this->propertyNames !== null) {
            $this->propertyNames->resolveRef($root);
        }

        return $this;
    }

    public function validate($value, ?Schema $root = null, array $path = ['#']): void
    {
        if (!is_object($value)) {
            throw new InvalidSchemaValueException("Expected object got " . gettype($value), $path);
        }

        $root = $root ?? $this;
        $reflection = new ReflectionClass(get_class($value));

        parent::validate($value, $root, $path);

        if ($this->properties !== null && count($this->properties) > 0) {
            foreach ($this->properties as $key => $schema) {
                try {
                    $schema->validate($reflection->getProperty($key)->getValue($value), $root, [...$path, $key]);
                } catch (ReflectionException $_) {
                    throw new InvalidSchemaValueException("Value has no property " . $key, $path);
                }
            }
        }

        if ($this->patternProperties !== null && count($this->patternProperties) > 0) {
            foreach ($this->patternProperties as $pattern => $schema) {
                foreach ($reflection->getProperties() as $property) {
                    if (preg_match($pattern, $property->getName())) {
                        $schema->validate($property->getValue(), $root, [...$path, $property->getName()]);
                    }
                }
            }
        }

        if ($this->additionalProperties !== null) {
            if (is_bool($this->additionalProperties) && !$this->additionalProperties) {
                foreach ($reflection->getProperties() as $property) {
                    if (!isset($this->properties[$property->getName()])) {
                        throw new InvalidSchemaValueException("Additionnal property " . $property->getName() . " is not allowed", $path);
                    }
                }
            } else if ($this->additionalProperties instanceof Schema) {
                foreach ($reflection->getProperties() as $property) {
                    if (!isset($this->properties[$property->getName()])) {
                        $this->additionalProperties->validate($property->getValue(), $root, [...$path, $property->getName()]);
                    }
                }
            }
        }

        if ($this->unevaluatedProperties !== null) {
            throw new NotYetImplementedException("unevaluatedProperties is not yet implemented");
        }

        if ($this->requiredProperties !== null) {
            foreach ($this->requiredProperties as $property) {
                try {
                    $reflection->getProperty($property);
                } catch (ReflectionException $_) {
                    throw new InvalidSchemaValueException("Property " . $property . " is required", $path);
                }
            }
        }

        if ($this->propertyNames !== null) {
            foreach ($reflection->getProperties() as $property) {
                $this->propertyNames->validate($property->getName(), $root, [...$path, $property->getName()]);
            }
        }

        if ($this->minProperties !== null && count($reflection->getProperties()) < $this->minProperties) {
            throw new InvalidSchemaValueException("Should have at least " . $this->minProperties . " properties got " . count($reflection->getProperties()), $path);
        }

        if ($this->maxProperties !== null && count($reflection->getProperties()) > $this->maxProperties) {
            throw new InvalidSchemaValueException("Should have at most " . $this->maxProperties . " properties got " . count($reflection->getProperties()), $path);
        }
    }

    public function jsonSerialize(): array
    {
        $properties = null;
        if ($this->properties !== null) {
            foreach ($this->properties as $name => $property) {
                $properties[$name] = $property->jsonSerialize();
            }
        }

        $patternProperties = null;
        if ($this->patternProperties !== null) {
            foreach ($this->patternProperties as $name => $property) {
                $patternProperties[$name] = $property->jsonSerialize();
            }
        }

        return parent::jsonSerialize()
            + ($properties !== null ? ['properties' => $properties] : [])
            + ($patternProperties !== null ? ['pattern$patternProperties' => $patternProperties] : [])
            + ($this->additionalProperties !== null ?
                [
                    'additionalProperties' => $this->additionalProperties instanceof Schema ?
                        $this->additionalProperties->jsonSerialize()
                        : $this->additionalProperties
                ] : [])
            + ($this->unevaluatedProperties !== null ?
                [
                    'unevaluatedProperties' => $this->unevaluatedProperties instanceof Schema ?
                        $this->unevaluatedProperties->jsonSerialize()
                        : $this->unevaluatedProperties
                ] : [])
            + ($this->requiredProperties !== null ? ['requiredProperties' => $this->requiredProperties] : [])
            + ($this->propertyNames !== null ? ['propertyNames' => $this->propertyNames] : [])
            + ($this->minProperties !== null ? ['minProperties' => $this->minProperties] : [])
            + ($this->maxProperties !== null ? ['maxProperties' => $this->maxProperties] : []);
    }
}
