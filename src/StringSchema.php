<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StringSchema extends Schema
{
    /**
     * @param string|null $id
     * @param string|null $anchor
     * @param string|null $ref
     * @param array<string,Schema|CircularReference|null> $defs
     * @param string|null $title
     * @param string|null $description
     * @param mixed[]|null $examples
     * @param mixed $default
     * @param boolean|null $deprecated
     * @param boolean|null $readOnly
     * @param boolean|null $writeOnly
     * @param mixed $const
     * @param mixed[]|null $enum
     * @param Schema[]|null $allOf
     * @param Schema[]|null $oneOf
     * @param Schema[]|null $anyOf
     * @param Schema|null $not
     * @param string|null $enumPattern
     * @param class-string<UnitEnum>|null $enumClass
     * @param Format|null $format Predefined format to which the string must conform
     * @param integer|null $minLength A string instance is valid against this keyword if its length is greater than, or equal to, the value of this keyword
     * @param integer|null $maxLength A string instance is valid against this keyword if its length is less than, or equal to, the value of this keyword
     * @param string|null $pattern A string instance is considered valid if the regular expression matches the instance successfully
     * @param ContentEnconding|null $contentEncoding If the instance value is a string, this property defines that the string SHOULD be interpreted as binary data and decoded using the encoding named by this property
     * @param string|null $contentMediaType If the instance is a string, this property indicates the media type of the contents of the string. If "contentEncoding" is present, this property describes the decoded string
     * @param Schema|null $contentSchema If the instance is a string, and if "contentMediaType" is present, this property contains a schema which describes the structure of the string
     */
    public function __construct(
        ?string $title = null,
        ?string $id = null,
        ?string $anchor = null,
        ?string $ref = null,
        array $defs = [],
        ?string $description = null,
        ?array $examples = null,
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
        ?string $enumClass = null,

        public ?Format $format = null,
        public ?int $minLength = null,
        public ?int $maxLength = null,
        public ?string $pattern = null,
        public ?ContentEnconding $contentEncoding = null,
        public ?string $contentMediaType = null,
        public ?Schema $contentSchema = null,
    ) {
        parent::__construct(
            [Type::String],
            id: $id,
            anchor: $anchor,
            ref: $ref,
            defs: $defs,
            title: $title,
            description: $description,
            examples: $examples,
            default: $default,
            deprecated: $deprecated,
            readOnly: $readOnly,
            writeOnly: $writeOnly,
            const: $const,
            enum: $enum,
            enumPattern: $enumPattern,
            enumClass: $enumClass,
            allOf: $allOf,
            oneOf: $oneOf,
            anyOf: $anyOf,
            not: $not,
        );
    }

    public function jsonSerialize(): array
    {
        $serialized = parent::jsonSerialize();

        return $serialized
            + ($this->format !== null ? ['format' => $this->format->value] : [])
            + ($this->minLength !== null ? ['minLength' => $this->minLength] : [])
            + ($this->maxLength !== null ? ['maxLength' => $this->maxLength] : [])
            + ($this->pattern !== null ? ['pattern' => $this->pattern] : [])
            + ($this->contentEncoding !== null ? ['contentEncoding' => $this->contentEncoding] : [])
            + ($this->contentMediaType !== null ? ['contentMediaType' => $this->contentMediaType] : [])
            + ($this->contentSchema !== null ? ['contentSchema' => $this->contentSchema->jsonSerialize()] : []);
    }
}
