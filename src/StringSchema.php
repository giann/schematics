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

        ?string $format = null,
        ?int $minLength = null,
        ?int $maxLength = null,
        ?string $pattern = null,
        ?string $contentEncoding = null,
        ?string $contentMediaType = null

    ) {
        parent::__construct(
            Schema::TYPE_STRING,
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
            $pattern = preg_match('/\/[^\/]+\//', $this->pattern) === 1 ? '/' . $this->pattern . '/' : $this->pattern;
            if (preg_match($pattern, $value) !== 1) {
                throw new InvalidSchemaValueException('Expected value to match ' . $this->pattern, $path);
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

            if ($mimeType !== false && $mimeType !== $this->contentMediaType) {
                throw new InvalidSchemaValueException('Expected content mime type to be ' . $this->contentMediaType . ' got ' . $mimeType, $path);
            }
        }

        if ($this->format !== null) {
            switch ($this->format) {
                case self::FORMAT_DATETIME:
                    if (!preg_match('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d\+\d\d:\d+$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be date-time', $path);
                    }
                    break;
                case self::FORMAT_TIME:
                    if (!preg_match('/^\d\d:\d\d:\d\d\+\d\d:\d+$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be time', $path);
                    }
                    break;
                case self::FORMAT_DATE:
                    if (!preg_match('/^\d{4}-\d\d-\d\d$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be date', $path);
                    }
                    break;
                case self::FORMAT_DURATION:
                    if (!preg_match(self::DURATION_REGEX, $value)) {
                        throw new InvalidSchemaValueException('Expected to be duration', $path);
                    }
                    break;
                case self::FORMAT_EMAIL:
                case self::FORMAT_IDNEMAIL:
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        throw new InvalidSchemaValueException('Expected to be email', $path);
                    }
                    break;
                case self::FORMAT_HOSTNAME:
                case self::FORMAT_IDNHOSTNAME:
                    if (!filter_var($value, FILTER_VALIDATE_DOMAIN)) {
                        throw new InvalidSchemaValueException('Expected to be hostname', $path);
                    }
                    break;
                case self::FORMAT_IPV4:
                    if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be ipv4', $path);
                    }
                    break;
                case self::FORMAT_IPV6:
                    if (!preg_match('/^[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}::[0-9a-fA-F]{4}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be ipv6', $path);
                    }
                    break;
                case self::FORMAT_UUID:
                    if (!preg_match('/^[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}$/', $value)) {
                        throw new InvalidSchemaValueException('Expected to be uuid', $path);
                    }
                    break;
                case self::FORMAT_URI:
                case self::FORMAT_URIREFERENCE:
                case self::FORMAT_IRI:
                case self::FORMAT_IRIREFERENCE:
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        throw new InvalidSchemaValueException('Expected to be uri', $path);
                    }
                    break;
                case self::FORMAT_URITEMPLATE:
                    if (!preg_match('/^$/', $value)) {
                        throw new InvalidSchemaValueException('uri-template', $path);
                    }
                    break;
                case self::FORMAT_JSONPOINTER:
                case self::FORMAT_RELATIVEJSONPOINTER:
                    if (!preg_match('/^\/?([^\/]+\/)*[^\/]+$/', $value)) {
                        throw new InvalidSchemaValueException('json-pointer', $path);
                    }
                    break;
                case self::FORMAT_REGEX:
                    if (!filter_var($value, FILTER_VALIDATE_REGEXP)) {
                        throw new InvalidSchemaValueException('Expected to be email', $path);
                    }
                    break;
            }
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
