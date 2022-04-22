<?php

declare(strict_types=1);

namespace Giann\Schematics;

use BadMethodCallException;
use JsonSerializable;
use ReflectionClass;
use InvalidArgumentException;
use ReflectionNamedType;
use Exception;
use ReflectionException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use ReflectionObject;
use Throwable;

// Use to differenciate a property with a null value from the absence of the property. ex: { "const": null }
final class NullConst
{
}

class InvalidSchemaValueException extends Exception
{
    public function __construct(string $message = "", array $path, array $dataPath, int $code = 0, ?Throwable $previous = null)
    {
        $message = $message . ' at ' . implode("/", $dataPath);

        parent::__construct($message, $code, $previous);
    }
}

class NotYetImplementedException extends BadMethodCallException
{
    public function __construct(string $message = "Not yet implemented", array $path, int $code = 0, ?Throwable $previous = null)
    {
        $message = $message . ' at ' . implode("/", $path);

        parent::__construct($message, $code, $previous);
    }
}

//#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "PROPERTY", "ANNOTATION"})
 */
class Schema implements JsonSerializable
{
    const TYPE_STRING = 'string';
    const TYPE_NUMBER = 'number';
    const TYPE_INTEGER = 'integer';
    const TYPE_OBJECT = 'object';
    const TYPE_ARRAY = 'array';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_NULL = 'null';

    // http://en.wikipedia.org/wiki/ISO_8601#Durations
    const DURATION_REGEX = '/^P([0-9]+(?:[,\.][0-9]+)?Y)?([0-9]+(?:[,\.][0-9]+)?M)?([0-9]+(?:[,\.][0-9]+)?D)?(?:T([0-9]+(?:[,\.][0-9]+)?H)?([0-9]+(?:[,\.][0-9]+)?M)?([0-9]+(?:[,\.][0-9]+)?S)?)?$/';

    // https://json-schema.org/understanding-json-schema/reference/string.html#id8
    const FORMAT_DATETIME = 'date-time';
    const FORMAT_TIME = 'time';
    const FORMAT_DATE = 'date';
    const FORMAT_DURATION = 'duration';
    const FORMAT_EMAIL = 'email';
    const FORMAT_IDNEMAIL = 'idn-email';
    const FORMAT_HOSTNAME = 'hostname';
    const FORMAT_IDNHOSTNAME = 'idn-hostname';
    const FORMAT_IPV4 = 'ipv4';
    const FORMAT_IPV6 = 'ipv6';
    const FORMAT_UUID = 'uuid';
    const FORMAT_URI = 'uri';
    const FORMAT_URIREFERENCE = 'uri-reference';
    const FORMAT_IRI = 'iri';
    const FORMAT_IRIREFERENCE = 'iri-reference';
    const FORMAT_URITEMPLATE = 'uri-template';
    const FORMAT_JSONPOINTER = 'json-pointer';
    const FORMAT_RELATIVEJSONPOINTER = 'relative-json-pointer';
    const FORMAT_REGEX = 'regex';

    /** @var string|array|null */
    public $type = null;
    public ?string $id = null;
    public ?string $anchor = null;
    public ?string $ref = null;
    // To avoid resolving the ref multiple times
    private ?string $resolvedRef = null;
    public ?array $defs = null;
    public ?string $title = null;
    public ?string $description = null;
    /** @var mixed */
    public $default = null;
    public ?bool $deprecated = null;
    public ?bool $readOnly = null;
    public ?bool $writeOnly = null;
    /** @var mixed */
    public $const = null;
    public ?array $enum = null;
    /**
     * @var Schema[]|null
     */
    public ?array $allOf = null;
    /**
     * @var Schema[]|null
     */
    public ?array $oneOf = null;
    /**
     * @var Schema[]|null
     */
    public ?array $anyOf = null;
    public ?Schema $not = null;

    // Array properties
    /** @var Schema|string|null */
    public $items = null;
    /** @var Schema[] */
    public ?array $prefixItems = null;
    public ?Schema $contains = null;
    public ?int $minContains = null;
    public ?int $maxContains = null;
    public ?int $minItems;
    public ?int $maxItems;
    public ?bool $uniqueItems = null;
    /** @var Schema|null */
    public $unevaluatedItems = null;

    // Number properties
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

    // String properties
    public ?string $format = null;
    public ?int $minLength = null;
    public ?int $maxLength = null;
    public ?string $pattern = null;
    public ?string $contentEncoding = null;
    public ?string $contentMediaType = null;

    // Object properties
    public ?array $properties = null;
    public ?array $patternProperties = null;
    /** @var Schema|null */
    public $additionalProperties = null;
    /** @var Schema|null */
    public $unevaluatedProperties = null;
    /** @var string[] */
    public ?array $required = null;
    public ?Schema $propertyNames = null;
    public ?int $minProperties = null;
    public ?int $maxProperties = null;
    public ?array $dependentSchemas = null;
    public ?object $dependentRequired = null;

    // A boolean is a valid schema: true validates anything and false nothing
    private ?bool $unilateral = null;

    /**
     * @param string|array|null $type
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
     * @param Schema|null $items
     * @param Schema[]|null $prefixItems
     * @param Schema|null $contains
     * @param integer|null $minContains
     * @param integer|null $maxContains
     * @param boolean|null $uniqueItems
     * @param null|Schema $unevaluatedItems
     * 
     * @param int|double|null $multipleOf
     * @param int|double|null $minimum
     * @param int|double|null $maximum
     * @param int|double|null $exclusiveMinimum
     * @param int|double|null $exclusiveMaximum
     * 
     * @param string|null $format
     * @param integer|null $minLength
     * @param integer|null $maxLength
     * @param string|null $pattern
     * @param string|null $contentEncoding
     * @param string|null $contentMediaType
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
        $type = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        ?array $defs = null,
        ?string $title = null,
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

        $items = null,
        ?array $prefixItems = null,
        ?Schema $contains = null,
        ?int $minContains = null,
        ?int $maxContains = null,
        ?int $minItems = null,
        ?int $maxItems = null,
        ?bool $uniqueItems = null,
        $unevaluatedItems = null,

        $multipleOf = null,
        $minimum = null,
        $maximum = null,
        $exclusiveMinimum = null,
        $exclusiveMaximum = null,

        ?string $format = null,
        ?int $minLength = null,
        ?int $maxLength = null,
        ?string $pattern = null,
        ?string $contentEncoding = null,
        ?string $contentMediaType = null,

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
        $this->type = $type;
        $this->id = $id;
        $this->anchor = $anchor;
        $this->ref = $ref;
        $this->defs = $defs;
        $this->title = $title;
        $this->description = $description;
        $this->default = $default;
        $this->deprecated = $deprecated;
        $this->readOnly = $readOnly;
        $this->writeOnly = $writeOnly;
        $this->const = $const;
        $this->enum = $enum;
        $this->allOf = $allOf;
        $this->oneOf = $oneOf;
        $this->anyOf = $anyOf;
        $this->not = $not;

        if ($this->enum === null && $enumPattern !== null) {
            $this->enum = self::patternToEnum($enumPattern);
        }

        $this->items = is_string($items) ? new Schema(null, null, null, $items) : $items;
        $this->prefixItems = $prefixItems;
        $this->contains = $contains;
        $this->minContains = $minContains;
        $this->maxContains = $maxContains;
        $this->minItems = $minItems;
        $this->maxItems = $maxItems;
        $this->uniqueItems = $uniqueItems;
        $this->unevaluatedItems = $unevaluatedItems;

        $this->multipleOf = $multipleOf;
        $this->minimum = $minimum;
        $this->maximum = $maximum;
        $this->exclusiveMinimum = $exclusiveMinimum;
        $this->exclusiveMaximum = $exclusiveMaximum;

        $this->format = $format;
        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
        $this->pattern = $pattern;
        $this->contentEncoding = $contentEncoding;
        $this->contentMediaType = $contentMediaType;

        if ($this->contentEncoding !== null && !in_array($this->contentEncoding, ['7bit', '8bit', 'binary', 'quoted-printable', 'base16', 'base32', 'base64'])) {
            throw new InvalidArgumentException('contentEncoding must be 7bit, 8bit, binary, quoted-printable, base16, base32 or base64');
        }

        $this->properties = $properties;
        $this->patternProperties = $patternProperties;
        $this->additionalProperties = $additionalProperties;
        $this->unevaluatedProperties = $unevaluatedProperties;
        $this->required = $required;
        $this->propertyNames = $propertyNames;
        $this->minProperties = $minProperties;
        $this->maxProperties = $maxProperties;
        $this->dependentSchemas = $dependentSchemas;
        $this->dependentRequired = $dependentRequired;

        if ($this->unevaluatedProperties !== null) {
            throw new NotYetImplementedException("unevaluatedProperties is not yet implemented", ['#']);
        }
    }

    /**
     * @param string|array|bool|object $json
     * @return Schema
     */
    public static function fromJson($json): Schema
    {
        if (is_bool($json) || (is_string($json) && is_bool(json_decode($json)))) {
            $schema = new Schema();
            $schema->unilateral = is_bool($json) ? $json : json_decode($json);

            return $schema;
        }

        $decoded = !is_string($json) ? (array)$json : json_decode($json, true);

        $properties = isset($decoded['properties']) ? [] : null;
        foreach ($decoded['properties'] ?? [] as $key => $schema) {
            $properties[$key] = Schema::fromJson($schema);
        }

        $patternProperties = isset($decoded['patternProperties']) ? [] : null;
        foreach ($decoded['patternProperties'] ?? [] as $key => $schema) {
            $patternProperties[$key] = Schema::fromJson($schema);
        }

        $dependentSchemas = isset($decoded['dependentSchemas']) ? [] : null;
        foreach ($decoded['dependentSchemas'] ?? [] as $key => $schema) {
            $dependentSchemas[$key] = Schema::fromJson($schema);
        }

        return new Schema(
            $decoded['type'],
            $decoded['id'],
            $decoded['$anchor'],
            $decoded['$ref'],
            isset($decoded['$defs']) ? array_map(fn ($def) => self::fromJson($def), (array)$decoded['$defs']) : null,
            $decoded['title'],
            $decoded['description'],
            $decoded['default'],
            $decoded['deprecated'],
            $decoded['readOnly'],
            $decoded['writeOnly'],
            array_key_exists('const', $decoded) && $decoded['const'] === null ? new NullConst() : $decoded['const'],
            $decoded['enum'],
            isset($decoded['allOf']) ? array_map(fn ($def) => self::fromJson($def), $decoded['allOf']) : null,
            isset($decoded['oneOf']) ? array_map(fn ($def) => self::fromJson($def), $decoded['oneOf']) : null,
            isset($decoded['anyOf']) ? array_map(fn ($def) => self::fromJson($def), $decoded['anyOf']) : null,
            isset($decoded['not']) ? self::fromJson($decoded['not']) : null,
            // enumPattern
            null,

            isset($decoded['items']) ? Schema::fromJson($decoded['items']) : null,
            isset($decoded['prefixItems']) ? array_map(fn ($el) => Schema::fromJson($el), $decoded['prefixItems']) : null,
            isset($decoded['contains']) ? Schema::fromJson($decoded['contains']) : null,
            $decoded['minContains'],
            $decoded['maxContains'],
            $decoded['minItems'],
            $decoded['maxItems'],
            $decoded['uniqueItems'],
            isset($decoded['unevaluatedItems']) ? Schema::fromJson($decoded['unevaluatedItems']) : null,

            $decoded['multipleOf'],
            $decoded['minimum'],
            $decoded['maximum'],
            $decoded['exclusiveMinimum'],
            $decoded['exclusiveMaximum'],

            $decoded['format'],
            $decoded['minLength'],
            $decoded['maxLength'],
            $decoded['pattern'],
            $decoded['contentEncoding'],
            $decoded['contentMediaType'],

            $properties,
            $patternProperties,
            isset($decoded['additionalProperties']) ? Schema::fromJson($decoded['additionalProperties']) : null,
            isset($decoded['unevaluatedProperties']) ? Schema::fromJson($decoded['unevaluatedProperties']) : null,
            $decoded['required'],
            isset($decoded['propertyNames']) ? StringSchema::fromJson($decoded['propertyNames']) : null,
            $decoded['minProperties'],
            $decoded['maxProperties'],
            $dependentSchemas,
            $decoded['dependentRequired'],
        );
    }

    // TODO: we miss some ref to resolve
    protected function resolveRef(?Schema $root): Schema
    {
        $root ??= $this;

        if ($this->ref !== null && $this->resolvedRef === null) {
            $root->defs ??= [];

            if ($this->ref == '#') {
                $this->resolvedRef = '#';
            } else {
                $this->resolvedRef = '#/$defs/' . $this->ref;

                if (!isset($root->defs[$this->ref])) {
                    $root->defs[$this->ref] = true; // Avoid circular ref resolving
                    $schema = self::classSchema($this->ref, $root);
                    $root->defs[$this->ref] = $schema instanceof Schema ? $schema->resolveRef($root) : $schema;
                }
            }
        }

        if ($this->not !== null) {
            $this->not->resolveRef($root);
        }

        foreach ($this->allOf ?? [] as $schema) {
            $schema->resolveRef($root);
        }

        foreach ($this->oneOf ?? [] as $schema) {
            $schema->resolveRef($root);
        }

        foreach ($this->anyOf ?? [] as $schema) {
            $schema->resolveRef($root);
        }


        if ($this->items instanceof Schema) {
            $this->items->resolveRef($root);
        }

        foreach ($this->prefixItems ?? [] as $schema) {
            $schema->resolveRef($root);
        }

        if ($this->contains instanceof Schema) {
            $this->contains->resolveRef($root);
        }


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

    private function acceptsAll(): bool
    {
        return $this->unilateral === true
            || ($this->not !== null && $this->not->rejectsAll())
            || array_filter((array)$this, fn ($el) => $el !== null) === []; // Schema specifies nothing, everything goes even absent data
    }

    private function rejectsAll(): bool
    {
        return $this->unilateral === false
            || ($this->not !== null && $this->not->acceptsAll());
    }

    public static function validateInstance(Model $value): object
    {
        $schema = self::classSchema(get_class($value));

        assert($schema instanceof Schema);

        $schema->validate($value);

        return $value;
    }

    /**
     * @param mixed $value
     * @param Schema|null $root
     * @param string[] $path
     * @return void
     */
    public function validate($value, ?Schema $root = null, array $path = ['#'], array $dataPath = ['#']): void
    {
        $root = $root ?? $this;

        // Schema is a bool
        if ($this->unilateral === true) {
            return;
        } else if ($this->unilateral === false) {
            throw new InvalidSchemaValueException("Schema rejects everything", $path, $dataPath);
        }

        $this->validateCommon($value, $root, $path, $dataPath);
        $this->validateArray($value, $root, $path, $dataPath);
        $this->validateNumber($value, $path, $dataPath);
        $this->validateString($value, $path, $dataPath);
        $this->validateObject($value, $root, $path, $dataPath);
    }

    private static function is_associative($array): bool
    {
        if (!is_array($array)) {
            return false;
        }

        if ([] === $array) {
            return false;
        }

        if (array_keys($array) !== range(0, count($array) - 1)) {
            return true;
        }

        // Dealing with a Sequential array
        return false;
    }

    private static function equal($a, $b): bool
    {
        if ($a instanceof NullConst && $b instanceof NullConst) {
            return true;
        }

        // Unlike php, json schema expect two object to be equal but its properties to be strictly equal
        if (is_object($a) && is_object($b)) {
            foreach ($a as $key => $value) {
                if (
                    !property_exists($b, $key)
                    || !self::equal($value, $b->{$key})
                ) {
                    return false;
                }
            }

            foreach ($b as $key => $value) {
                if (!property_exists($a, $key)) {
                    return false;
                }
            }

            return true;
        } else if (self::is_associative($a) && self::is_associative($b)) {
            // stupid php retains map keys order
            ksort($a);
            ksort($b);
        } else if (is_array($a) && is_array($b)) {
            if ($a === $b) {
                return true;
            }

            foreach ($a as $element) {
                if (!self::contains($element, $b)) {
                    return false;
                }
            }

            foreach ($b as $element) {
                if (!self::contains($element, $a)) {
                    return false;
                }
            }

            return true;
        } else if ((is_int($a) || is_float($a)) && (is_int($b) || is_float($b))) {
            return $a == $b;
        }

        return $a === $b;
    }

    // in_array but with json schema semantics
    private static function contains($needle, array $haystack): bool
    {
        foreach ($haystack as $element) {
            if (self::equal($needle, $element)) {
                return true;
            }
        }

        return false;
    }

    private static function validateRelativeUri(string $url): bool
    {
        if (parse_url($url, PHP_URL_SCHEME) != '') {
            // URL is not relative
            return !(filter_var($url, FILTER_VALIDATE_URL) === false);
        } else {
            // PHP filter_var does not support relative urls, so we simulate a full URL
            return !(filter_var('http://www.example.com/' . ltrim($url, '/'), FILTER_VALIDATE_URL) === false);
        }
    }

    private function validateCommon($value, ?Schema $root = null, array $path = ['#'], array $dataPath = ['#']): void
    {
        // Here we only validate when multiple types, the other validate method will handle the case when there's only one type
        if (count((is_array($this->type) ? $this->type : null) ?? []) > 0) {
            $validates = false;
            foreach (($this->type ?? []) as $type) {
                switch ($type) {
                    case 'string':
                        if (is_string($value)) {
                            $validates = true;
                            break;
                        }
                        break;
                    case 'number':
                        if (is_float($value) || is_int($value)) {
                            $validates = true;
                            break;
                        }
                        break;
                    case 'integer':
                        // 1.0 is considered to be integer but is_int(1.0) == false
                        if ((is_int($value) || is_float($value)) && floor($value) == $value) {
                            $validates = true;
                            break;
                        }
                        break;
                    case 'array':
                        if (is_array($value) && !self::is_associative($value)) {
                            $validates = true;
                            break;
                        }
                        break;
                    case 'object':
                        if (is_object($value)) {
                            $validates = true;
                            break;
                        }
                        break;
                    case 'boolean':
                        if (is_bool($value)) {
                            $validates = true;
                            break;
                        }
                        break;
                    case 'null':
                        if ($value === null) {
                            $validates = true;
                            break;
                        }
                        break;
                }
            }

            if (!$validates) {
                throw new InvalidSchemaValueException('Expected type to be one of [' . implode(', ', ($this->type ?? [])) . '] got ' . gettype($value), $path, $dataPath);
            }
        }

        if ($this->type === 'boolean' && !is_bool($value)) {
            throw new InvalidSchemaValueException('Expected type to be boolean got ' . gettype($value), $path, $dataPath);
        } else if ($this->type === 'null' && $value !== null) {
            throw new InvalidSchemaValueException('Expected null got ' . gettype($value), $path, $dataPath);
        }

        if ($this->const !== null && !self::equal($this->const, $value)) {
            throw new InvalidSchemaValueException(
                "Expected value to be the constant\n"
                    . json_encode($this->const, JSON_PRETTY_PRINT)
                    . "\ngot:\n"
                    . json_encode($value, JSON_PRETTY_PRINT),
                $path,
                $dataPath
            );
        }

        if ($this->enum !== null && !self::contains($value instanceof JsonSerializable ? $value->jsonSerialize() : $value, $this->enum)) {
            throw new InvalidSchemaValueException(
                "Expected value within [" . implode(', ', array_map(fn ($el) => json_encode($el), $this->enum)) . '] got `' . json_encode($value) . '`',
                $path,
                $dataPath
            );
        }

        // A ref generated by schematics
        if ($this->resolvedRef !== null) {
            // Root reference
            if ($this->ref === '#' && $root !== $this && $root !== null) {
                $root->validate($value, $root, $path, $dataPath);
            } else {
                $refPath = explode('#', $this->resolvedRef);
                $basePath = explode('/', $refPath[0]);
                $fragment = count($refPath) > 1 ? explode('/', $refPath[1]) : [];

                if (
                    count($basePath) === 1 && $basePath[0] === ''
                    && count($fragment) > 2 && $fragment[1] === '$defs'
                ) {
                    if (isset($root->defs[$fragment[2]])) {
                        $ref = $root->defs[$fragment[2]];

                        $ref->validate($value, $root, [...$path, $this->resolvedRef], $dataPath);
                    } else {
                        throw new InvalidArgumentException('Can\'t resolve $ref ' . ($this->resolvedRef ?? $this->ref ?? ''));
                    }
                } else {
                    throw new NotYetImplementedException('Reference other than #/$defs/<name> are not yet implemented: ' . ($this->resolvedRef ?? $this->ref ?? ''), $path);
                }
            }
            // A regular ref
        } else if ($this->ref !== null) {
            if (strpos($this->ref, '#/') === 0) {
                $current = $root;
                foreach (explode('/', substr($this->ref, 2)) as $fragment) {
                    if (in_array($fragment, ['$ref', '$defs'])) {
                        $fragment = substr($fragment, 1);
                    }

                    if ($fragment === '#') {
                        $current = $root;
                    } else {
                        if (self::is_associative($current)) {
                            if (!isset($current[$fragment])) {
                                throw new InvalidSchemaValueException('Could not resolve $ref ' . $this->ref, $path, $dataPath);
                            }

                            $current = $current[$fragment];
                        } else if (is_array($current)) {
                            $current = $current[intval($fragment)];
                        } else if (is_object($current)) {
                            if (!property_exists($current, $fragment)) {
                                throw new InvalidSchemaValueException('Could not resolve $ref ' . $this->ref, $path, $dataPath);
                            }

                            $current = $current->{$fragment};
                        } else {
                            throw new InvalidSchemaValueException('Could not resolve $ref ' . $this->ref, $path, $dataPath);
                        }
                    }
                }

                if ($current instanceof Schema) {
                    $current->validate($value, $root, $path, $dataPath);
                } else {
                    throw new InvalidSchemaValueException('$ref ' . $this->ref . ' does not resolve to a schema', $path, $dataPath);
                }
            } else {
                throw new NotYetImplementedException('Reference other than #/../.. are not yet implemented: ' . $this->ref, $path);
            }
        }

        foreach ($this->allOf ?? [] as $i => $schema) {
            $schema->validate($value, $root, [...$path, 'allOf', $i], $dataPath);
        }

        if ($this->oneOf !== null && count($this->oneOf) > 0) {
            $oneOf = 0;
            $exceptions = [];
            foreach ($this->oneOf as $i => $schema) {
                try {
                    $schema->validate($value, $root, [...$path, 'oneOf', $i], $dataPath);
                    $oneOf++;
                } catch (InvalidSchemaValueException $e) {
                    $exceptions[] = $e;
                }
            }

            if ($oneOf !== 1) {
                throw new InvalidSchemaValueException(
                    "Should validate against one of\n"
                        . json_encode($this->oneOf, JSON_PRETTY_PRINT)
                        . "\nbut fails with:\n\t- "
                        . implode("\n\t- ", array_map(fn ($e) => $e->getMessage(), $exceptions)),
                    $path,
                    $dataPath
                );
            }
        }

        if ($this->anyOf !== null && count($this->anyOf) > 0) {
            $anyOf = false;
            $exceptions = [];
            foreach ($this->anyOf as $i => $schema) {
                try {
                    $schema->validate($value, $root, [...$path, 'anyOf', $i], $dataPath);
                    $anyOf = true;

                    break;
                } catch (InvalidSchemaValueException $e) {
                    $exceptions[] = $e;
                }
            }

            if (!$anyOf) {
                throw new InvalidSchemaValueException(
                    "Should validate against any of\n"
                        . json_encode($this->anyOf, JSON_PRETTY_PRINT)
                        . "\nbut fails with:\n\t- "
                        . implode("\n\t- ", array_map(fn ($e) => $e->getMessage(), $exceptions)),
                    $path,
                    $dataPath
                );
            }
        }

        if ($this->not !== null) {
            $validated = false;
            try {
                $this->not->validate($value, $root, [...$path, 'not'], $dataPath);

                $validated = true;
            } catch (InvalidSchemaValueException $e) {
                // Good
            }

            if ($validated) {
                throw new InvalidSchemaValueException("Should not validate against: " . json_encode($this->not), $path, $dataPath);
            }
        }
    }

    private function validateArray($value, ?Schema $root = null, array $path = ['#'], array $dataPath): void
    {
        if ((!is_array($value) || self::is_associative($value))) {
            if ($this->type === 'array') {
                // The schema specifically ask for an array, we throw
                throw new InvalidSchemaValueException("Expected type to be array, got " . gettype($value), $path, $dataPath);
            }

            // Schema allows data to be something else, just ignore array validations
            return;
        }

        if ($this->contains !== null) {
            $contains = 0;
            foreach ($value as $i => $item) {
                try {
                    $this->contains->validate($item, $root, [...$path, 'contains', $i], $dataPath);

                    $contains++;
                } catch (InvalidSchemaValueException $_) {
                }
            }

            if ($this->minContains !== null && $contains < $this->minContains) {
                throw new InvalidSchemaValueException('Expected at least ' . $this->minContains . ' to validate against `contains` elements got ' . $contains, $path, $dataPath);
            }

            if ($this->maxContains !== null && $contains > $this->maxContains) {
                throw new InvalidSchemaValueException('Expected at most ' . $this->maxContains . ' to validate against `contains` elements got ' . $contains, $path, $dataPath);
            }

            if ($this->minContains === null && $contains === 0) {
                throw new InvalidSchemaValueException('Expected at least one item to validate against:\n' . json_encode($this->contains, JSON_PRETTY_PRINT), $path, $dataPath);
            }
        }

        if ($this->minItems !== null && count($value) < $this->minItems) {
            throw new InvalidSchemaValueException('Expected at least ' . $this->minItems . ' elements got ' . count($value), $path, $dataPath);
        }

        if ($this->maxItems !== null && count($value) > $this->maxItems) {
            throw new InvalidSchemaValueException('Expected at most ' . $this->maxItems . ' elements got ' . count($value), $path, $dataPath);
        }

        if ($this->uniqueItems === true) {
            $items = [];
            foreach ($value as $i => $item) {
                if (self::contains($item, $items)) {
                    throw new InvalidSchemaValueException('Expected unique items', $path, [...$dataPath, $i]);
                }

                $items[] = $item;
            }
        }

        if (count($this->prefixItems ?? []) > 0) {
            foreach (array_slice(($this->prefixItems ?? []), 0, count($value)) as $i => $prefixItem) {
                $prefixItem->validate($value[$i], $root, [...$path, 'prefixItems', $i], [...$dataPath, $i]);
            }
        }

        if ($this->items !== null) {
            // Only test items that after prefixItems
            foreach (array_slice($value, count($this->prefixItems ?? [])) as $i => $item) {
                $this->items->validate($item, $root, [...$path, 'items', $i], [...$dataPath, $i]);
            }
        } else if ($this->unevaluatedItems !== null) {
            // TODO: this is not complete
            foreach (array_slice($value, count($this->prefixItems ?? [])) as $i => $item) {
                $this->unevaluatedItems->validate($item, $root, [...$path, 'unevaluatedItems', $i], [...$dataPath, $i]);
            }
        }
    }

    private function validateNumber($value, array $path = ['#'], array $dataPath): void
    {
        if (!is_int($value) && !is_float($value)) {
            if ($this->type === 'number' || $this->type === 'integer') {
                // The schema specifically ask for an array, we throw
                throw new InvalidSchemaValueException('Expected type to be ' . $this->type . ', got ' . gettype($value), $path, $dataPath);
            }

            // Schema allows data to be something else, just ignore numeric validations
            return;
        }

        if (floor($value) != $value && $this->type === 'integer') {
            throw new InvalidSchemaValueException("Expected an integer got " . gettype($value) . '(' . $value . ')', $path, $dataPath);
        }

        if ($this->multipleOf !== null) {
            $div = $value / $this->multipleOf;

            if (is_infinite($div) || floor($div) != $div) {
                throw new InvalidSchemaValueException("Expected a multiple of " . $this->multipleOf, $path, $dataPath);
            }
        }

        if ($this->minimum !== null && $value < $this->minimum) {
            throw new InvalidSchemaValueException("Expected value to be greater or equal to " . $this->minimum . ', got ' . $value, $path, $dataPath);
        }

        if ($this->maximum !== null && $value > $this->maximum) {
            throw new InvalidSchemaValueException("Expected value to be less or equal to " . $this->maximum . ', got ' . $value, $path, $dataPath);
        }

        if ($this->exclusiveMinimum !== null && $value <= $this->exclusiveMinimum) {
            throw new InvalidSchemaValueException("Expected value to be less than " . $this->exclusiveMinimum . ', got ' . $value, $path, $dataPath);
        }

        if ($this->exclusiveMaximum !== null && $value >= $this->exclusiveMaximum) {
            throw new InvalidSchemaValueException("Expected value to be greather than " . $this->exclusiveMaximum . ', got ' . $value, $path, $dataPath);
        }
    }

    private function validateString($value, array $path = ['#'], array $dataPath): void
    {
        if (!is_string($value)) {
            if ($this->type === 'string') {
                // The schema specifically ask for an array, we throw
                throw new InvalidSchemaValueException('Expected type to be ' . $this->type . ', got ' . gettype($value), $path, $dataPath);
            }

            // Schema allows data to be something else, just ignore string validations
            return;
        }

        // Add regex delimiters /.../ if missing
        if ($this->pattern !== null) {
            $pattern = preg_match('/\/[^\/]+\//', $this->pattern) === 0 ? '/' . $this->pattern . '/' : $this->pattern;
            if (preg_match($pattern, $value) !== 1) {
                throw new InvalidSchemaValueException('Expected value to match ' . $this->pattern . ' got `' . $value . '`', $path, $dataPath);
            }
        }

        if ($this->maxLength !== null && mb_strlen($value, 'UTF-8') > $this->maxLength) {
            throw new InvalidSchemaValueException('Expected at most ' . $this->maxLength . ' characters long, got ' . mb_strlen($value, 'UTF-8'), $path, $dataPath);
        }

        if ($this->minLength !== null && mb_strlen($value, 'UTF-8') < $this->minLength) {
            throw new InvalidSchemaValueException('Expected at least ' . $this->minLength . ' characters long, got ' . mb_strlen($value, 'UTF-8'), $path, $dataPath);
        }

        $decodedValue = $value;
        if ($this->contentEncoding !== null) {
            switch ($this->contentEncoding) {
                case '7bit':
                    throw new NotYetImplementedException('7bit decoding not yet implemented', $path);
                    break;
                case '8bit':
                    throw new NotYetImplementedException('8bit decoding not yet implemented', $path);
                    break;
                case 'binary':
                    throw new NotYetImplementedException('binary decoding not yet implemented', $path);
                    break;
                case 'quoted-printable':
                    $decodedValue = quoted_printable_decode($value);
                    break;
                case 'base16':
                    throw new NotYetImplementedException('base16 decoding not yet implemented', $path);
                    break;
                case 'base32':
                    throw new NotYetImplementedException('base32 decoding not yet implemented', $path);
                    break;
                case 'base64':
                    $decodedValue = base64_decode($value);
                    break;
            }
        }

        if ($this->contentMediaType !== null) {
            $tmpfile = tempnam(sys_get_temp_dir(), 'jsonschemavalidation');
            file_put_contents($tmpfile, $decodedValue);
            $mimeType = mime_content_type($tmpfile);
            unlink($tmpfile);

            // https://github.com/json-schema-org/JSON-Schema-Test-Suite/blob/master/tests/draft2020-12/content.json#L13-L17
            if ($mimeType === 'text/plain' && $this->contentMediaType === 'application/json') {
                $mimeType = 'application/json';
            }

            if ($mimeType !== false && $mimeType !== $this->contentMediaType) {
                throw new InvalidSchemaValueException('Expected content mime type to be ' . $this->contentMediaType . ' got ' . $mimeType, $path, $dataPath);
            }
        }

        if ($this->format !== null) {
            switch ($this->format) {
                case self::FORMAT_DATETIME:
                    if (!preg_match('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d\+\d\d:\d+$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be date-time got `' . $value . '`', $path, $dataPath);
                    }
                    break;
                case self::FORMAT_TIME:
                    if (!preg_match('/^\d\d:\d\d:\d\d\+\d\d:\d+$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be time got `' . $value . '`', $path, $dataPath);
                    }
                    break;
                case self::FORMAT_DATE:
                    if (!preg_match('/^\d{4}-\d\d-\d\d$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be date got `' . $value . '`', $path, $dataPath);
                    }
                    break;
                case self::FORMAT_DURATION:
                    if (!preg_match(self::DURATION_REGEX, $value)) {
                        throw new InvalidSchemaValueException('Expected to be duration got `' . $value . '`', $path, $dataPath);
                    }
                    break;
                case self::FORMAT_EMAIL:
                case self::FORMAT_IDNEMAIL:
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        throw new InvalidSchemaValueException('Expected to be email got `' . $value . '`', $path, $dataPath);
                    }
                    break;
                case self::FORMAT_HOSTNAME:
                case self::FORMAT_IDNHOSTNAME:
                    if (!filter_var($value, FILTER_VALIDATE_DOMAIN)) {
                        throw new InvalidSchemaValueException('Expected to be hostname got `' . $value . '`', $path, $dataPath);
                    }
                    break;
                case self::FORMAT_IPV4:
                    if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be ipv4 got `' . $value . '`', $path, $dataPath);
                    }
                    break;
                case self::FORMAT_IPV6:
                    if (!preg_match('/^[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be ipv6 got `' . $value . '`', $path, $dataPath);
                    }
                    break;
                case self::FORMAT_UUID:
                    if (!preg_match('/^[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be uuid got `' . $value . '`', $path, $dataPath);
                    }
                    break;
                case self::FORMAT_URI:
                case self::FORMAT_IRI:
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        throw new InvalidSchemaValueException('Expected to be uri got `' . $value . '`', $path, $dataPath);
                    }
                    break;
                case self::FORMAT_URIREFERENCE:
                case self::FORMAT_IRIREFERENCE:
                    if (!self::validateRelativeUri($value)) {
                        throw new InvalidSchemaValueException('Expected to be uri-reference got `' . $value . '`', $path, $dataPath);
                    }
                    break;
                case self::FORMAT_URITEMPLATE:
                    if (!preg_match('/^$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be uri-template got `' . $value . '`', $path, $dataPath);
                    }
                    break;
                case self::FORMAT_JSONPOINTER:
                case self::FORMAT_RELATIVEJSONPOINTER: // TODO
                    if (!preg_match('/^\/?([^\/]+\/)*[^\/]+$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be json-pointer got `' . $value . '`', $path, $dataPath);
                    }
                    break;
                case self::FORMAT_REGEX:
                    if (!filter_var($value, FILTER_VALIDATE_REGEXP)) {
                        throw new InvalidSchemaValueException('Expected to be email got `' . $value . '`', $path, $dataPath);
                    }
                    break;
            }
        }
    }

    private function validateObject($value, ?Schema $root = null, array $path = ['#'], array $dataPath): void
    {
        if (!is_object($value)) {
            if ($this->type === 'object') {
                // The schema specifically ask for an array, we throw
                throw new InvalidSchemaValueException('Expected type to be ' . $this->type . ', got ' . gettype($value), $path, $dataPath);
            }

            // Schema allows data to be something else, just ignore numeric validations
            return;
        }

        $root = $root ?? $this;
        $reflection = new ReflectionObject($value);

        $clearedProperties = [];
        if ($this->properties !== null && count($this->properties) > 0) {
            foreach ($this->properties as $key => $schema) {
                try {
                    $schema->validate($reflection->getProperty($key)->getValue($value), $root, [...$path, $key], [...$dataPath, $key]);

                    $clearedProperties[] = $key;
                } catch (ReflectionException $_) {
                    if ($schema->acceptsAll() && $this->required !== null && in_array($key, $this->required)) {
                        throw new InvalidSchemaValueException("Value has no property " . $key, $path, $dataPath);
                    }
                }
            }
        }

        if ($this->patternProperties !== null && count($this->patternProperties) > 0) {
            foreach ($this->patternProperties as $pattern => $schema) {
                $pattern = preg_match('/\/[^\/]+\//', $pattern) === 0 ? '/' . $pattern . '/' : $pattern;

                foreach ($reflection->getProperties() as $property) {
                    if (preg_match($pattern, $property->getName())) {
                        $schema->validate($property->getValue($value), $root, [...$path, $property->getName()], [...$dataPath, $property->getName()]);

                        $clearedProperties[] = $property->getName();
                    }
                }
            }
        }

        if ($this->additionalProperties !== null) {
            if ($this->additionalProperties === false) {
                foreach ($reflection->getProperties() as $property) {
                    if (!in_array($property->getName(), $clearedProperties)) {
                        throw new InvalidSchemaValueException("Additionnal property " . $property->getName() . " is not allowed", $path, [...$dataPath, $property->getName()]);
                    }
                }
            } else if ($this->additionalProperties instanceof Schema) {
                foreach ($reflection->getProperties() as $property) {
                    if (!in_array($property->getName(), $clearedProperties)) {
                        $this->additionalProperties->validate($property->getValue($value), $root, [...$path, $property->getName()], [...$dataPath, $property->getName()]);
                    }
                }
            }
        }

        if ($this->dependentSchemas !== null) {
            /** @var Schema $schema */
            foreach ($this->dependentSchemas as $property => $schema) {
                if (property_exists($value, $property)) {
                    $schema->validate($value, $root, [...$path, 'dependentSchemas'], [...$dataPath, $property]);
                }
            }
        }

        if ($this->dependentRequired !== null) {
            /** @var Schema $schema */
            foreach ($this->dependentRequired as $property => $properties) {
                if (property_exists($value, $property)) {
                    foreach ($properties as $prop) {
                        if (!property_exists($value, $prop)) {
                            throw new InvalidSchemaValueException('Since property ' . $property . ' expected property ' . $prop . ' to be present', [...$path, 'dependentRequired'], [...$dataPath, $property]);
                        }
                    }
                }
            }
        }

        if ($this->unevaluatedProperties !== null) {
            throw new NotYetImplementedException("unevaluatedProperties is not yet implemented", $path);
        }

        if ($this->required !== null) {
            foreach ($this->required as $property) {
                try {
                    $reflection->getProperty($property);
                } catch (ReflectionException $_) {
                    throw new InvalidSchemaValueException("Property " . $property . " is required", $path, $dataPath);
                }
            }
        }

        if ($this->propertyNames !== null) {
            foreach ($reflection->getProperties() as $property) {
                $this->propertyNames->validate($property->getName(), $root, [...$path, $property->getName()], [...$dataPath, $property->getName()]);
            }
        }

        if ($this->minProperties !== null && count($reflection->getProperties()) < $this->minProperties) {
            throw new InvalidSchemaValueException("Should have at least " . $this->minProperties . " properties got " . count($reflection->getProperties()), $path, $dataPath);
        }

        if ($this->maxProperties !== null && count($reflection->getProperties()) > $this->maxProperties) {
            throw new InvalidSchemaValueException("Should have at most " . $this->maxProperties . " properties got " . count($reflection->getProperties()), $path, $dataPath);
        }
    }

    private static function patternToEnum(string $constantPattern): ?array
    {
        if (strpos($constantPattern, '::')) {
            list($cls, $constantPattern) = explode("::", $constantPattern);
            try {
                assert(class_exists($cls));
                $refl = new ReflectionClass($cls);
                $constants = $refl->getConstants();
            } catch (ReflectionException $e) {
                return null;
            }
        } else {
            $constants = get_defined_constants();
        }

        /** @var string[] */
        $values = [];

        /**
         * @var string $val
         * @var string $name 
         */
        foreach ($constants as $name => $val) {
            if (fnmatch($constantPattern, $name)) {
                $values[] = $val;
            }
        }

        /*
            Ensure there is no duplicate values in enums
         */
        if (count(array_unique($values)) !== count($values)) {
            $duplicatedKeys = array_keys(
                array_filter(
                    array_count_values($values),
                    static function ($value): bool {
                        return $value > 1;
                    }
                )
            );

            throw new InvalidArgumentException('Invalid duplicate values for enum ' . $constantPattern . ' for items : ' . implode(', ', $duplicatedKeys));
        }

        return $values;
    }

    /** @return Schema | string */
    public static function classSchema(string $class, ?Schema $root = null)
    {
        if ($class === '#') {
            return '#';
        }

        assert(class_exists($class));

        $classReflection = new ReflectionClass($class);

        $reader = new AnnotationReader();
        /** @var ?ObjectSchema */
        $schema = $reader->getClassAnnotation($classReflection, ObjectSchema::class);

        if ($schema === null) {
            throw new InvalidArgumentException('The class ' . $class . ' is not annotated');
        }

        // Attribute annotations
        $attributes = array_filter(
            $reader->getClassAnnotations($classReflection),
            fn (object $annotation) => $annotation instanceof SchemaAttribute
        );

        /**
         * @var SchemaAttribute $attribute
         */
        foreach ($attributes as $attribute) {
            $schema->{$attribute->key} = $attribute->value;
        }

        $root = $root ?? $schema;

        // Does it extends another class/schema?
        $parentReflection = $classReflection->getParentClass();
        if ($parentReflection !== false) {
            $parent = $parentReflection->getName();

            $root->defs ??= [];
            if (!isset($root->defs[$parent])) {
                $root->defs[$parent] = true; // Avoid circular ref resolving
                $parentSchema = self::classSchema($parent, $root);
                $root->defs[$parent] = $parentSchema instanceof Schema ? $parentSchema->resolveRef($root) : null;
            }

            $ref = new Schema(null, null, null, $parent);
            $ref->resolvedRef = '#/$defs/' . $parent;
            $schema->allOf = ($schema->allOf ?? [])
                + [$ref];
        }

        $properties = $classReflection->getProperties();
        foreach ($properties as $property) {
            // Ignore properties coming from parent class
            if (
                $property->getDeclaringClass()->getNamespaceName() . '\\' . $property->getDeclaringClass()->getName()
                !== $classReflection->getNamespaceName() . '\\' . $classReflection->getName()
            ) {
                continue;
            }

            /** @var ?Schema */
            $propertySchema = $reader->getPropertyAnnotation($property, Schema::class);

            if ($propertySchema !== null) {
                $schema->properties[$property->getName()] = $propertySchema->resolveRef($root);
            } else {
                // Not annotated, try to infer something
                $propertyType = $property->getType();
                /** @var ?Schema */
                $propertySchema = null;

                if ($propertyType instanceof ReflectionNamedType) {
                    $type = $propertyType->getName();

                    switch ($type) {
                        case 'string':
                            $propertySchema = new StringSchema();
                            break;
                        case 'int':
                            $propertySchema = new NumberSchema(true);
                            break;
                        case 'double':
                            $propertySchema = new NumberSchema(false);
                            break;
                        case 'array':
                            $propertySchema = new ArraySchema();
                            break;
                        case 'bool':
                            $propertySchema = new BooleanSchema();
                            break;
                        case 'object':
                            $propertySchema = new ObjectSchema();
                            break;
                        default:
                            // Is it a class?

                            // Is it a circular reference to root schema ?
                            if ($type === $classReflection->getName()) {
                                $type = $root == $schema ? '#' : $type;
                            }

                            $propertySchema = (new Schema(null, null, null, $type))->resolveRef($root);
                    }

                    if ($propertyType->allowsNull()) {
                        $propertySchema = new Schema(
                            // Stupid php 7.4
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
                            // oneOf
                            [
                                new NullSchema(),
                                $propertySchema
                            ]
                        );
                    }

                    $schema->properties[$property->getName()] = $propertySchema;
                }
            }

            $propertyAttributes = array_filter(
                $reader->getPropertyAnnotations($property),
                fn (object $annotation) => $annotation instanceof SchemaAttribute
            );

            /**
             * @var SchemaAttribute $attribute
             */
            foreach ($propertyAttributes as $attribute) {
                $propertySchema->{$attribute->key} = $attribute->value;
            }
        }

        return $schema;
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

        $dependentSchemas = null;
        if ($this->dependentSchemas !== null) {
            foreach ($this->dependentSchemas as $name => $property) {
                $dependentSchemas[$name] = $property->jsonSerialize();
            }
        }

        return ($this->type !== null ? [
            'type' => $this->type
        ] : [])
            + ($this->id !== null ? ['$id' => $this->id] : [])
            + ($this->anchor !== null ? ['$anchor' => $this->anchor] : [])
            + ($this->resolvedRef !== null ? ['$ref' => $this->resolvedRef] : [])
            + ($this->resolvedRef === null && $this->ref !== null ? ['$ref' => $this->ref] : [])
            + ($this->defs !== null ? ['$defs' => array_map(fn ($el) => $el->jsonSerialize(), $this->defs)] : [])
            + ($this->title !== null ? ['title' => $this->title] : [])
            + ($this->description !== null ? ['description' => $this->description] : [])
            + ($this->default !== null ? ['default' => $this->default] : [])
            + ($this->deprecated !== null ? ['deprecated' => $this->deprecated] : [])
            + ($this->readOnly !== null ? ['readOnly' => $this->readOnly] : [])
            + ($this->writeOnly !== null ? ['writeOnly' => $this->writeOnly] : [])
            + ($this->const !== null ? ['const' => $this->const instanceof NullConst ? null : $this->const] : [])
            + ($this->enum !== null ? ['enum' => $this->enum] : [])
            + ($this->allOf !== null ? ['allOf' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->allOf)] : [])
            + ($this->oneOf !== null ? ['oneOf' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->oneOf)] : [])
            + ($this->anyOf !== null ? ['anyOf' => array_map(fn (Schema $element) => $element->jsonSerialize(), $this->anyOf)] : [])
            + ($this->not !== null ? ['not' => $this->not->jsonSerialize()] : [])
            + ($this->items !== null ? ['items' => $this->items->jsonSerialize()] : [])
            + ($this->prefixItems !== null ? ['prefixItems' => $this->prefixItems] : [])
            + ($this->contains !== null ? ['contains' => $this->contains->jsonSerialize()] : [])
            + ($this->minContains !== null ? ['minContains' => $this->minContains] : [])
            + ($this->maxContains !== null ? ['maxContains' => $this->maxContains] : [])
            + ($this->uniqueItems !== null ? ['uniqueItems' => $this->uniqueItems] : [])
            + ($this->unevaluatedItems !== null ? ['unevaluatedItems' => $this->unevaluatedItems->jsonSerialize()] : [])
            + ($this->multipleOf !== null ? ['multipleOf' => $this->multipleOf] : [])
            + ($this->minimum !== null ? ['minimum' => $this->minimum] : [])
            + ($this->maximum !== null ? ['maximum' => $this->maximum] : [])
            + ($this->exclusiveMinimum !== null ? ['exclusiveMinimum' => $this->exclusiveMinimum] : [])
            + ($this->exclusiveMaximum !== null ? ['exclusiveMaximum' => $this->exclusiveMaximum] : [])
            + ($this->format !== null ? ['format' => $this->format] : [])
            + ($this->minLength !== null ? ['minLength' => $this->minLength] : [])
            + ($this->maxLength !== null ? ['maxLength' => $this->maxLength] : [])
            + ($this->pattern !== null ? ['pattern' => $this->pattern] : [])
            + ($this->contentEncoding !== null ? ['contentEncoding' => $this->contentEncoding] : [])
            + ($this->contentMediaType !== null ? ['contentMediaType' => $this->contentMediaType] : [])
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
            + ($this->required !== null ? ['required' => $this->required] : [])
            + ($this->propertyNames !== null ? ['propertyNames' => $this->propertyNames] : [])
            + ($this->minProperties !== null ? ['minProperties' => $this->minProperties] : [])
            + ($this->maxProperties !== null ? ['maxProperties' => $this->maxProperties] : [])
            + ($dependentSchemas !== null ? $dependentSchemas : [])
            + ($this->dependentRequired !== null ? ['dependentRequired' => $this->dependentRequired] : []);
    }
}
