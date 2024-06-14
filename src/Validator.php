<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Giann\Schematics\Exception\InvalidSchemaValueException;
use Giann\Schematics\Exception\NotYetImplementedException;
use InvalidArgumentException;
use JsonSerializable;
use PhpParser\Node\Expr\Cast\Object_;
use ReflectionException;
use ReflectionObject;
use RuntimeException;

class Validator
{
    // http://en.wikipedia.org/wiki/ISO_8601#Durations
    const DURATION_REGEX = '/^P([0-9]+(?:[,\.][0-9]+)?Y)?([0-9]+(?:[,\.][0-9]+)?M)?([0-9]+(?:[,\.][0-9]+)?D)?(?:T([0-9]+(?:[,\.][0-9]+)?H)?([0-9]+(?:[,\.][0-9]+)?M)?([0-9]+(?:[,\.][0-9]+)?S)?)?$/';

    private function acceptsAll(Schema $schema): bool
    {
        return $schema->getUnilateral() === true
            || ($schema->not !== null && $this->rejectsAll($schema->not))
            || empty(array_filter(
                $schema->jsonSerialize(),
                fn ($el) => $el !== null
            )); // Schema specifies nothing, everything goes even absent data
    }

    private function rejectsAll(Schema $schema): bool
    {
        return $schema->getUnilateral() === false
            || ($schema->not !== null && $this->acceptsAll($schema->not));
    }

    public function validateInstance(object $value): object
    {
        $schema = Schema::classSchema(get_class($value));

        assert($schema instanceof Schema);

        $this->validate($schema, $value);

        return $value;
    }

    /**
     * @param Schema $schema
     * @param mixed $value
     * @param Schema|null $root
     * @param string[] $path
     * @return void
     */
    public function validate(
        Schema $schema,
        mixed $value,
        ?Schema $root = null,
        array $path = ['#']
    ): void {
        $root = $root ?? $schema;

        // Schema is a bool
        if ($schema->getUnilateral() === true) {
            return;
        } else if ($schema->getUnilateral() === false) {
            throw new InvalidSchemaValueException("Schema rejects everything", $path);
        }

        $this->validateCommon($schema, $value, $root, $path);

        // If type is any or one of the type is any, anything goes
        if (empty($schema->type)) {
            return;
        }

        if (count($schema->type) > 1) {
            throw new NotYetImplementedException('Schematics does not handle schema with multiple types yet', $path);
        }

        if ($schema instanceof ArraySchema) {
            $this->validateArray($schema, $value, $root, $path);
        }

        if ($schema instanceof NumberSchema || $schema instanceof IntegerSchema) {
            $this->validateNumber($schema, $value, $path);
        }

        if ($schema instanceof StringSchema) {
            $this->validateString($schema, $value, $path);
        }

        if ($schema instanceof ObjectSchema) {
            $this->validateObject($schema, $value, $root, $path);
        }
    }

    /**
     * @param mixed[] $array
     * @return bool
     */
    private static function is_associative(array $array): bool
    {
        if ([] === $array) {
            return false;
        }

        if (array_keys($array) !== range(0, count($array) - 1)) {
            return true;
        }

        // Dealing with a Sequential array
        return false;
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @return boolean
     */
    private static function equal($a, $b): bool
    {
        if ($a instanceof NullConst && $b instanceof NullConst) {
            return true;
        }

        // Unlike php, json schema expect two object to be equal but its properties to be strictly equal
        if (is_object($a) && is_object($b)) {
            foreach ((array)$a as $key => $value) {
                if (
                    !property_exists($b, $key)
                    || !self::equal($value, $b->{$key})
                ) {
                    return false;
                }
            }

            foreach ((array)$b as $key => $value) {
                if (!property_exists($a, $key)) {
                    return false;
                }
            }

            return true;
        } else if (is_array($a) && is_array($b) && self::is_associative($a) && self::is_associative($b)) {
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

    /**
     * @param mixed $needle
     * @param mixed[] $haystack
     * @return boolean
     */
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

    /**
     * @param Schema $schema
     * @param mixed $value
     * @param Schema|null $root
     * @param string[] $path
     * @return void
     */
    private function validateCommon(
        Schema $schema,
        mixed $value,
        ?Schema $root = null,
        array $path = ['#'],
    ): void {
        // Here we only validate when multiple types, the other validate method will handle the case when there's only one type
        $validates = false;
        foreach ($schema->type as $type) {
            switch ($type) {
                case Type::String:
                    if (is_string($value)) {
                        $validates = true;
                        break;
                    }
                    break;
                case Type::Number:
                    if (is_float($value) || is_int($value)) {
                        $validates = true;
                        break;
                    }
                    break;
                case Type::Integer:
                    // 1.0 is considered to be integer but is_int(1.0) == false
                    if ((is_int($value) || is_float($value)) && floor($value) == $value) {
                        $validates = true;
                        break;
                    }
                    break;
                case Type::Array:
                    if (is_array($value) && !self::is_associative($value)) {
                        $validates = true;
                        break;
                    }
                    break;
                case Type::Object:
                    if (is_object($value)) {
                        $validates = true;
                        break;
                    }
                    break;
                case Type::Boolean:
                    if (is_bool($value)) {
                        $validates = true;
                        break;
                    }
                    break;
                case Type::Null:
                    if ($value === null) {
                        $validates = true;
                        break;
                    }
                    break;
            }

            if (!$validates) {
                throw new InvalidSchemaValueException(
                    'Expected type to be one of [' . implode(', ', array_map(fn ($type) => $type->value, $schema->type)) . '] got ' . gettype($value),
                    $path
                );
            }
        }

        if (count($schema->type) == 1) {
            if ($schema->type[0] === Type::Boolean && !is_bool($value)) {
                throw new InvalidSchemaValueException('Expected type to be boolean got ' . gettype($value), $path);
            } else if ($schema->type[0] === Type::Null && $value !== null) {
                throw new InvalidSchemaValueException('Expected null got ' . gettype($value), $path,);
            }
        }

        if ($schema->const !== null && !self::equal($schema->const, $value)) {
            throw new InvalidSchemaValueException(
                "Expected value to be the constant\n"
                    . json_encode($schema->const, JSON_PRETTY_PRINT)
                    . "\ngot:\n"
                    . json_encode($value, JSON_PRETTY_PRINT),
                $path
            );
        }

        if ($schema->enum !== null && !self::contains($value instanceof JsonSerializable ? $value->jsonSerialize() : $value, $schema->enum)) {
            throw new InvalidSchemaValueException(
                "Expected value within [" . implode(', ', array_map(fn ($el) => json_encode($el), $schema->enum)) . '] got `' . json_encode($value) . '`',
                $path
            );
        }

        // A ref generated by schematics
        $resolvedRef = $schema->getResolvedRef();
        if ($resolvedRef !== null) {
            // Root reference
            if ($schema->ref === '#' && $root !== $schema && $root !== null) {
                $this->validate(
                    $root,
                    $value,
                    $root,
                    $path
                );
            } else {
                $refPath = explode('#', $resolvedRef);
                $basePath = explode('/', $refPath[0]);
                $fragment = count($refPath) > 1 ? explode('/', $refPath[1]) : [];

                if (
                    count($basePath) === 1 && $basePath[0] === ''
                    && count($fragment) > 2 && $fragment[1] === '$defs'
                ) {
                    if (isset($root->defs[$fragment[2]]) && $root->defs[$fragment[2]] instanceof Schema) {
                        $ref = $root->defs[$fragment[2]];

                        $this->validate(
                            $ref,
                            $value,
                            $root,
                            [...$path, $resolvedRef]
                        );
                    } else {
                        throw new InvalidArgumentException('Can\'t resolve $ref ' . $schema->ref);
                    }
                } else {
                    throw new NotYetImplementedException(
                        'Reference other than #/$defs/<name> are not yet implemented: ' . $schema->ref,
                        $path
                    );
                }
            }
            // A regular ref
        } else if ($schema->ref !== null) {
            if (strpos($schema->ref, '#/') === 0) {
                /** @var Schema|object|mixed[]|null */
                $current = $root;
                foreach (explode('/', substr($schema->ref, 2)) as $fragment) {
                    if (in_array($fragment, ['$ref', '$defs'])) {
                        $fragment = substr($fragment, 1);
                    }

                    if ($fragment === '#') {
                        $current = $root;
                    } else {
                        if (self::is_associative($current)) {
                            if (!isset($current[$fragment])) {
                                throw new InvalidSchemaValueException('Could not resolve $ref ' . $schema->ref, $path);
                            }

                            $current = $current[$fragment];
                        } else if (is_array($current)) {
                            $current = $current[intval($fragment)];
                        } else if (is_object($current)) {
                            if (!property_exists($current, $fragment)) {
                                throw new InvalidSchemaValueException('Could not resolve $ref ' . $schema->ref, $path);
                            }

                            $current = $current->{$fragment};
                        } else {
                            throw new InvalidSchemaValueException('Could not resolve $ref ' . $schema->ref, $path);
                        }
                    }
                }

                if ($current instanceof Schema) {
                    $this->validate(
                        $current,
                        $value,
                        $root,
                        $path
                    );
                } else {
                    throw new InvalidSchemaValueException('$ref ' . $schema->ref . ' does not resolve to a schema', $path);
                }
            } else {
                throw new NotYetImplementedException('Reference other than #/../.. are not yet implemented: ' . $schema->ref, $path);
            }
        }

        foreach ($schema->allOf ?? [] as $i => $schema) {
            $this->validate(
                $schema,
                $value,
                $root,
                [...$path, 'allOf', $i]
            );
        }

        if ($schema->oneOf !== null && count($schema->oneOf) > 0) {
            $oneOf = 0;
            $exceptions = [];
            foreach ($schema->oneOf as $i => $schema) {
                try {
                    $this->validate(
                        $schema,
                        $value,
                        $root,
                        [...$path, 'oneOf', $i]
                    );
                    $oneOf++;
                } catch (InvalidSchemaValueException $e) {
                    $exceptions[] = $e;
                }
            }

            if ($oneOf !== 1) {
                throw new InvalidSchemaValueException(
                    "Should validate against one of\n"
                        . json_encode($schema->oneOf, JSON_PRETTY_PRINT)
                        . "\nbut fails with:\n\t- "
                        . implode("\n\t- ", array_map(fn ($e) => $e->getMessage(), $exceptions)),
                    $path
                );
            }
        }

        if ($schema->anyOf !== null && count($schema->anyOf) > 0) {
            $anyOf = false;
            $exceptions = [];
            foreach ($schema->anyOf as $i => $schema) {
                try {
                    $this->validate(
                        $schema,
                        $value,
                        $root,
                        [...$path, 'anyOf', $i]
                    );
                    $anyOf = true;

                    break;
                } catch (InvalidSchemaValueException $e) {
                    $exceptions[] = $e;
                }
            }

            if (!$anyOf) {
                throw new InvalidSchemaValueException(
                    "Should validate against any of\n"
                        . json_encode($schema->anyOf, JSON_PRETTY_PRINT)
                        . "\nbut fails with:\n\t- "
                        . implode("\n\t- ", array_map(fn ($e) => $e->getMessage(), $exceptions)),
                    $path
                );
            }
        }

        if ($schema->not !== null) {
            $validated = false;
            try {
                $this->validate(
                    $schema->not,
                    $value,
                    $root,
                    [...$path, 'not']
                );

                $validated = true;
            } catch (InvalidSchemaValueException $e) {
                // Good
            }

            if ($validated) {
                throw new InvalidSchemaValueException("Should not validate against: " . json_encode($schema->not), $path);
            }
        }
    }

    /**
     * @param ArraySchema $schema
     * @param mixed $value
     * @param Schema|null $root
     * @param string[] $path
     * @return void
     */
    private function validateArray(
        ArraySchema $schema,
        mixed $value,
        ?Schema $root = null,
        array $path = ['#']
    ): void {
        if ((!is_array($value) || self::is_associative($value))) {
            // The schema specifically ask for an array, we throw
            throw new InvalidSchemaValueException("Expected type to be array, got " . gettype($value), $path);
        }

        if ($schema->contains !== null) {
            $contains = 0;
            foreach ($value as $i => $item) {
                try {
                    $this->validate(
                        $schema->contains,
                        $item,
                        $root,
                        [...$path, 'contains', $i]
                    );

                    $contains++;
                } catch (InvalidSchemaValueException $_) {
                }
            }

            if ($schema->minContains !== null && $contains < $schema->minContains) {
                throw new InvalidSchemaValueException('Expected at least ' . $schema->minContains . ' to validate against `contains` elements got ' . $contains, $path);
            }

            if ($schema->maxContains !== null && $contains > $schema->maxContains) {
                throw new InvalidSchemaValueException('Expected at most ' . $schema->maxContains . ' to validate against `contains` elements got ' . $contains, $path);
            }

            if ($schema->minContains === null && $contains === 0) {
                throw new InvalidSchemaValueException('Expected at least one item to validate against:\n' . json_encode($schema->contains, JSON_PRETTY_PRINT), $path);
            }
        }

        if ($schema->minItems !== null && count($value) < $schema->minItems) {
            throw new InvalidSchemaValueException('Expected at least ' . $schema->minItems . ' elements got ' . count($value), $path);
        }

        if ($schema->maxItems !== null && count($value) > $schema->maxItems) {
            throw new InvalidSchemaValueException('Expected at most ' . $schema->maxItems . ' elements got ' . count($value), $path);
        }

        if ($schema->uniqueItems === true) {
            $items = [];
            foreach ($value as $i => $item) {
                if (self::contains($item, $items)) {
                    throw new InvalidSchemaValueException('Expected unique items', $path);
                }

                $items[] = $item;
            }
        }

        if (count($schema->prefixItems ?? []) > 0) {
            foreach (array_slice(($schema->prefixItems ?? []), 0, count($value)) as $i => $prefixItem) {
                $this->validate(
                    $prefixItem,
                    $value[$i],
                    $root,
                    [...$path, 'prefixItems', $i]
                );
            }
        }

        if ($schema->items !== null) {
            // Only test items that after prefixItems
            foreach (array_slice($value, count($schema->prefixItems ?? [])) as $i => $item) {
                $this->validate(
                    $schema->items,
                    $item,
                    $root,
                    [...$path, 'items', $i]
                );
            }
        } else if ($schema->unevaluatedItems !== null) {
            // TODO: schema is not complete
            foreach (array_slice($value, count($schema->prefixItems ?? [])) as $i => $item) {
                $this->validate(
                    $schema->unevaluatedItems,
                    $item,
                    $root,
                    [...$path, 'unevaluatedItems', $i]
                );
            }
        }
    }

    /**
     * @param NumberSchema|IntegerSchema $schema
     * @param mixed $value
     * @param string[] $path
     * @return void
     */
    private function validateNumber(
        NumberSchema|IntegerSchema $schema,
        mixed $value,
        array $path = ['#']
    ): void {
        if (!is_int($value) && !is_float($value)) {
            // The schema specifically ask for an number, we throw
            throw new InvalidSchemaValueException('Expected type to be ' . ($schema instanceof NumberSchema ? 'number' : 'integer') . ', got ' . gettype($value), $path);
        }

        if (floor($value) != $value && $schema instanceof IntegerSchema) {
            throw new InvalidSchemaValueException("Expected an integer got " . gettype($value) . '(' . $value . ')', $path);
        }

        if ($schema->multipleOf !== null) {
            $div = $value / $schema->multipleOf;

            if (is_infinite($div) || floor($div) != $div) {
                throw new InvalidSchemaValueException("Expected a multiple of " . $schema->multipleOf, $path);
            }
        }

        if ($schema->minimum !== null && $value < $schema->minimum) {
            throw new InvalidSchemaValueException("Expected value to be greater or equal to " . $schema->minimum . ', got ' . $value, $path);
        }

        if ($schema->maximum !== null && $value > $schema->maximum) {
            throw new InvalidSchemaValueException("Expected value to be less or equal to " . $schema->maximum . ', got ' . $value, $path);
        }

        if ($schema->exclusiveMinimum !== null && $value <= $schema->exclusiveMinimum) {
            throw new InvalidSchemaValueException("Expected value to be less than " . $schema->exclusiveMinimum . ', got ' . $value, $path);
        }

        if ($schema->exclusiveMaximum !== null && $value >= $schema->exclusiveMaximum) {
            throw new InvalidSchemaValueException("Expected value to be greather than " . $schema->exclusiveMaximum . ', got ' . $value, $path);
        }
    }

    /**
     * @param StringSchema $schema
     * @param mixed $value
     * @param string[] $path
     * @return void
     */
    private function validateString(
        StringSchema $schema,
        mixed $value,
        array $path = ['#']
    ): void {
        if (!is_string($value)) {
            // The schema specifically ask for an array, we throw
            throw new InvalidSchemaValueException('Expected type to be string, got ' . gettype($value), $path);
        }

        // Add regex delimiters /.../ if missing
        if ($schema->pattern !== null) {
            $pattern = preg_match('/\/[^\/]+\//', $schema->pattern) === 0 ? '/' . $schema->pattern . '/' : $schema->pattern;
            if (preg_match($pattern, $value) !== 1) {
                throw new InvalidSchemaValueException('Expected value to match ' . $schema->pattern . ' got `' . $value . '`', $path);
            }
        }

        if ($schema->maxLength !== null && mb_strlen($value, 'UTF-8') > $schema->maxLength) {
            throw new InvalidSchemaValueException('Expected at most ' . $schema->maxLength . ' characters long, got ' . mb_strlen($value, 'UTF-8'), $path);
        }

        if ($schema->minLength !== null && mb_strlen($value, 'UTF-8') < $schema->minLength) {
            throw new InvalidSchemaValueException('Expected at least ' . $schema->minLength . ' characters long, got ' . mb_strlen($value, 'UTF-8'), $path);
        }

        $decodedValue = $value;
        if ($schema->contentEncoding !== null) {
            switch ($schema->contentEncoding) {
                case '7bit':
                    throw new NotYetImplementedException('7bit decoding not yet implemented', $path);
                case '8bit':
                    throw new NotYetImplementedException('8bit decoding not yet implemented', $path);
                case 'binary':
                    throw new NotYetImplementedException('binary decoding not yet implemented', $path);
                case 'quoted-printable':
                    $decodedValue = quoted_printable_decode($value);
                case 'base16':
                    throw new NotYetImplementedException('base16 decoding not yet implemented', $path);
                case 'base32':
                    throw new NotYetImplementedException('base32 decoding not yet implemented', $path);
                case 'base64':
                    $decodedValue = base64_decode($value);
                    break;
            }
        }

        if ($schema->contentMediaType !== null) {
            $tmpfile = tempnam(sys_get_temp_dir(), 'jsonschemavalidation');

            if ($tmpfile === false) {
                throw new RuntimeException('Could not determine MIME type of value');
            }

            file_put_contents($tmpfile, $decodedValue);
            $mimeType = mime_content_type($tmpfile);
            unlink($tmpfile);

            // https://github.com/json-schema-org/JSON-Schema-Test-Suite/blob/master/tests/draft2020-12/content.json#L13-L17
            if ($mimeType === 'text/plain' && $schema->contentMediaType === 'application/json') {
                $mimeType = 'application/json';
            }

            if ($mimeType !== false && $mimeType !== $schema->contentMediaType) {
                throw new InvalidSchemaValueException('Expected content mime type to be ' . $schema->contentMediaType . ' got ' . $mimeType, $path);
            }
        }

        if ($schema->format !== null) {
            switch ($schema->format) {
                case Format::DateTime:
                    if (!preg_match('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d\+\d\d:\d+$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be date-time got `' . $value . '`', $path);
                    }
                    break;
                case Format::Time:
                    if (!preg_match('/^\d\d:\d\d:\d\d\+\d\d:\d+$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be time got `' . $value . '`', $path);
                    }
                    break;
                case Format::Date:
                    if (!preg_match('/^\d{4}-\d\d-\d\d$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be date got `' . $value . '`', $path);
                    }
                    break;
                case Format::Duration:
                    if (!preg_match(self::DURATION_REGEX, $value)) {
                        throw new InvalidSchemaValueException('Expected to be duration got `' . $value . '`', $path);
                    }
                    break;
                case Format::Email:
                case Format::IdnEmail:
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        throw new InvalidSchemaValueException('Expected to be email got `' . $value . '`', $path);
                    }
                    break;
                case Format::Hostname:
                case Format::IdnHostname:
                    if (!filter_var($value, FILTER_VALIDATE_DOMAIN)) {
                        throw new InvalidSchemaValueException('Expected to be hostname got `' . $value . '`', $path);
                    }
                    break;
                case Format::IpV4:
                    if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be ipv4 got `' . $value . '`', $path);
                    }
                    break;
                case Format::IpV6:
                    if (!preg_match('/^[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be ipv6 got `' . $value . '`', $path);
                    }
                    break;
                case Format::Uuid:
                    if (!preg_match('/^[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be uuid got `' . $value . '`', $path);
                    }
                    break;
                case Format::Uri:
                case Format::Iri:
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        throw new InvalidSchemaValueException('Expected to be uri got `' . $value . '`', $path);
                    }
                    break;
                case Format::UriReference:
                case Format::IriReference:
                    if (!self::validateRelativeUri($value)) {
                        throw new InvalidSchemaValueException('Expected to be uri-reference got `' . $value . '`', $path);
                    }
                    break;
                case Format::UriTemplate:
                    if (!preg_match('/^$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be uri-template got `' . $value . '`', $path);
                    }
                    break;
                case Format::JsonPointer:
                case Format::RelativeJsonPointer: // TODO
                    if (!preg_match('/^\/?([^\/]+\/)*[^\/]+$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be json-pointer got `' . $value . '`', $path);
                    }
                    break;
                case Format::Regex:
                    if (!filter_var($value, FILTER_VALIDATE_REGEXP)) {
                        throw new InvalidSchemaValueException('Expected to be email got `' . $value . '`', $path);
                    }
                    break;
            }
        }
    }

    /**
     * @param ObjectSchema $schema
     * @param mixed $value
     * @param Schema|null $root
     * @param string[] $path
     * @return void
     */
    private function validateObject(
        ObjectSchema $schema,
        mixed $value,
        ?Schema $root = null,
        array $path = ['#']
    ): void {
        if (!is_object($value)) {
            // The schema specifically ask for an array, we throw
            throw new InvalidSchemaValueException('Expected type to be object, got ' . gettype($value), $path);
        }

        $root = $root ?? $schema;
        $reflection = new ReflectionObject($value);

        $clearedProperties = [];
        if ($schema->properties !== null && count($schema->properties) > 0) {
            foreach ($schema->properties as $key => $propertySchema) {
                try {
                    $this->validate(
                        $propertySchema,
                        $reflection->getProperty($key)->getValue($value),
                        $root,
                        [...$path, $key]
                    );

                    $clearedProperties[] = $key;
                } catch (ReflectionException $_) {
                    if (
                        $this->acceptsAll($propertySchema)
                        && $propertySchema instanceof ObjectSchema
                        && $propertySchema->required !== null
                        && in_array($key, $propertySchema->required)
                    ) {
                        throw new InvalidSchemaValueException("Value has no property " . $key, $path);
                    }
                }
            }
        }

        if ($schema->patternProperties !== null && count($schema->patternProperties) > 0) {
            foreach ($schema->patternProperties as $pattern => $propertySchema) {
                $pattern = preg_match('/\/[^\/]+\//', $pattern) === 0 ? '/' . $pattern . '/' : $pattern;

                foreach ($reflection->getProperties() as $property) {
                    if (preg_match($pattern, $property->getName())) {
                        $this->validate(
                            $propertySchema,
                            $property->getValue($value),
                            $root,
                            [...$path, $property->getName()]
                        );

                        $clearedProperties[] = $property->getName();
                    }
                }
            }
        }

        if ($schema->additionalProperties !== null) {
            if ($schema->additionalProperties === false) {
                foreach ($reflection->getProperties() as $property) {
                    if (!in_array($property->getName(), $clearedProperties)) {
                        throw new InvalidSchemaValueException("Additionnal property " . $property->getName() . " is not allowed", $path);
                    }
                }
            } else if ($schema->additionalProperties instanceof Schema) {
                foreach ($reflection->getProperties() as $property) {
                    if (!in_array($property->getName(), $clearedProperties)) {
                        $this->validate(
                            $schema->additionalProperties,
                            $property->getValue($value),
                            $root,
                            [...$path, $property->getName()]
                        );
                    }
                }
            }
        }

        if ($schema->dependentSchemas !== null) {
            foreach ($schema->dependentSchemas as $property => $propertySchema) {
                if (property_exists($value, $property)) {
                    $this->validate(
                        $propertySchema,
                        $value,
                        $root,
                        [...$path, 'dependentSchemas']
                    );
                }
            }
        }

        if ($schema->dependentRequired !== null) {
            foreach ($schema->dependentRequired as $property => $properties) {
                if (property_exists($value, $property)) {
                    foreach ($properties as $prop) {
                        if (!property_exists($value, $prop)) {
                            throw new InvalidSchemaValueException(
                                'Since property ' . $property . ' expected property ' . $prop . ' to be present',
                                [...$path, 'dependentRequired']
                            );
                        }
                    }
                }
            }
        }

        if ($schema->unevaluatedProperties !== null) {
            throw new NotYetImplementedException("unevaluatedProperties is not yet implemented", $path);
        }

        if ($schema->required !== null) {
            foreach ($schema->required as $property) {
                try {
                    $reflection->getProperty($property);
                } catch (ReflectionException $_) {
                    throw new InvalidSchemaValueException("Property " . $property . " is required", $path);
                }
            }
        }

        if ($schema->propertyNames !== null) {
            foreach ($reflection->getProperties() as $property) {
                $this->validate(
                    $schema->propertyNames,
                    $property->getName(),
                    $root,
                    [...$path, $property->getName()]
                );
            }
        }

        if ($schema->minProperties !== null && count($reflection->getProperties()) < $schema->minProperties) {
            throw new InvalidSchemaValueException("Should have at least " . $schema->minProperties . " properties got " . count($reflection->getProperties()), $path);
        }

        if ($schema->maxProperties !== null && count($reflection->getProperties()) > $schema->maxProperties) {
            throw new InvalidSchemaValueException("Should have at most " . $schema->maxProperties . " properties got " . count($reflection->getProperties()), $path);
        }
    }
}
