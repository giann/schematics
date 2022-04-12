<?php

declare(strict_types=1);

namespace Giann\Schematics;

use InvalidArgumentException;

//#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class StringSchema extends Schema
{
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

    public ?string $format = null;
    public ?int $minLength = null;
    public ?int $maxLength = null;
    public ?string $pattern = null;
    public ?string $contentEncoding = null;
    public ?string $contentMediaType = null;

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
     * @param string|null $format
     * @param integer|null $minLength
     * @param integer|null $maxLength
     * @param string|null $pattern
     * @param string|null $contentEncoding
     * @param string|null $contentMediaType
     */
    public function __construct(
        ?string $format = null,
        ?int $minLength = null,
        ?int $maxLength = null,
        ?string $pattern = null,
        ?string $contentEncoding = null,
        ?string $contentMediaType = null,

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
            Schema::TYPE_STRING,
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

        $this->format = $format;
        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
        $this->pattern = $pattern;
        $this->contentEncoding = $contentEncoding;
        $this->contentMediaType = $contentMediaType;

        if ($this->contentEncoding && !in_array($this->contentEncoding, ['7bit', '8bit', 'binary', 'quoted-printable', 'base16', 'base32', 'base64'])) {
            throw new InvalidArgumentException('contentEncoding must be 7bit, 8bit, binary, quoted-printable, base16, base32 or base64');
        }
    }

    public static function fromJson($json): Schema
    {
        $decoded = is_array($json) ? $json : json_decode($json, true);

        return new StringSchema(
            $decoded['format'],
            $decoded['minLength'],
            $decoded['maxLength'],
            $decoded['pattern'],
            $decoded['contentEncoding'],
            $decoded['contentMediaType'],

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

    public function validate($value, ?Schema $root = null, array $path = ['#']): void
    {
        $root = $root ?? $this;

        parent::validate($value, $root, $path);

        if ($this->maxLength !== null && strlen($value) > $this->maxLength) {
            throw new InvalidSchemaValueException('Expected at most ' . $this->maxLength . ' characters long, got ' . strlen($value), $path);
        }

        if ($this->minLength !== null && strlen($value) < $this->minLength) {
            throw new InvalidSchemaValueException('Expected at least ' . $this->minLength . ' characters long, got ' . strlen($value), $path);
        }

        // Add regex delimiters /.../ if missing
        if ($this->pattern !== null) {
            $pattern = preg_match('/\/[^\/]+\//', $this->pattern) === 0 ? '/' . $this->pattern . '/' : $this->pattern;
            if (preg_match($pattern, $value) !== 1) {
                throw new InvalidSchemaValueException('Expected value to match ' . $this->pattern . ' got `' . $value . '`', $path);
            }
        }

        $decodedValue = $value;
        if ($this->contentEncoding !== null) {
            switch ($this->contentEncoding) {
                case '7bit':
                    throw new NotYetImplementedException('7bit decoding not yet implemented');
                    break;
                case '8bit':
                    throw new NotYetImplementedException('8bit decoding not yet implemented');
                    break;
                case 'binary':
                    throw new NotYetImplementedException('binary decoding not yet implemented');
                    break;
                case 'quoted-printable':
                    $decodedValue = quoted_printable_decode($value);
                    break;
                case 'base16':
                    throw new NotYetImplementedException('base16 decoding not yet implemented');
                    break;
                case 'base32':
                    throw new NotYetImplementedException('base32 decoding not yet implemented');
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
                throw new InvalidSchemaValueException('Expected content mime type to be ' . $this->contentMediaType . ' got ' . $mimeType, $path);
            }
        }

        if ($this->format !== null) {
            switch ($this->format) {
                case self::FORMAT_DATETIME:
                    if (!preg_match('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d\+\d\d:\d+$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be date-time got `' . $value . '`', $path);
                    }
                    break;
                case self::FORMAT_TIME:
                    if (!preg_match('/^\d\d:\d\d:\d\d\+\d\d:\d+$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be time got `' . $value . '`', $path);
                    }
                    break;
                case self::FORMAT_DATE:
                    if (!preg_match('/^\d{4}-\d\d-\d\d$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be date got `' . $value . '`', $path);
                    }
                    break;
                case self::FORMAT_DURATION:
                    if (!preg_match(self::DURATION_REGEX, $value)) {
                        throw new InvalidSchemaValueException('Expected to be duration got `' . $value . '`', $path);
                    }
                    break;
                case self::FORMAT_EMAIL:
                case self::FORMAT_IDNEMAIL:
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        throw new InvalidSchemaValueException('Expected to be email got `' . $value . '`', $path);
                    }
                    break;
                case self::FORMAT_HOSTNAME:
                case self::FORMAT_IDNHOSTNAME:
                    if (!filter_var($value, FILTER_VALIDATE_DOMAIN)) {
                        throw new InvalidSchemaValueException('Expected to be hostname got `' . $value . '`', $path);
                    }
                    break;
                case self::FORMAT_IPV4:
                    if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be ipv4 got `' . $value . '`', $path);
                    }
                    break;
                case self::FORMAT_IPV6:
                    if (!preg_match('/^[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be ipv6 got `' . $value . '`', $path);
                    }
                    break;
                case self::FORMAT_UUID:
                    if (!preg_match('/^[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be uuid got `' . $value . '`', $path);
                    }
                    break;
                case self::FORMAT_URI:
                case self::FORMAT_IRI:
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        throw new InvalidSchemaValueException('Expected to be uri got `' . $value . '`', $path);
                    }
                    break;
                case self::FORMAT_URIREFERENCE:
                case self::FORMAT_IRIREFERENCE:
                    if (!self::validateRelativeUri($value)) {
                        throw new InvalidSchemaValueException('Expected to be uri-reference got `' . $value . '`', $path);
                    }
                    break;
                case self::FORMAT_URITEMPLATE:
                    if (!preg_match('/^$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be uri-template got `' . $value . '`', $path);
                    }
                    break;
                case self::FORMAT_JSONPOINTER:
                case self::FORMAT_RELATIVEJSONPOINTER: // TODO
                    if (!preg_match('/^\/?([^\/]+\/)*[^\/]+$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be json-pointer got `' . $value . '`', $path);
                    }
                    break;
                case self::FORMAT_REGEX:
                    if (!filter_var($value, FILTER_VALIDATE_REGEXP)) {
                        throw new InvalidSchemaValueException('Expected to be email got `' . $value . '`', $path);
                    }
                    break;
            }
        }
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

    public function jsonSerialize(): array
    {
        return parent::jsonSerialize()
            + ($this->format !== null ? ['format' => $this->format] : [])
            + ($this->minLength !== null ? ['minLength' => $this->minLength] : [])
            + ($this->maxLength !== null ? ['maxLength' => $this->maxLength] : [])
            + ($this->pattern !== null ? ['pattern' => $this->pattern] : [])
            + ($this->contentEncoding !== null ? ['contentEncoding' => $this->contentEncoding] : [])
            + ($this->contentMediaType !== null ? ['contentMediaType' => $this->contentMediaType] : []);
    }
}
