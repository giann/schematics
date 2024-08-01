<?php

declare(strict_types=1);

namespace Giann\Schematics\Draft04;

use Attribute;
use UnitEnum;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class StringSchema extends Schema
{
    /**
     * @param string|null $schema Will be ignored if not root of the schema
     * @param string|null $id
     * @param string|null $ref
     * @param array<string,Schema|CircularReference|null> $definitions
     * @param string|null $title
     * @param string|null $description
     * @param mixed[]|null $examples
     * @param mixed $default

     * @param boolean|null $readOnly
     * @param boolean|null $writeOnly
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
     * @param ContentEncoding|null $contentEncoding If the instance value is a string, this property defines that the string SHOULD be interpreted as binary data and decoded using the encoding named by this property
     * @param string|null $contentMediaType If the instance is a string, this property indicates the media type of the contents of the string. If "contentEncoding" is present, this property describes the decoded string
     */
    public function __construct(
        ?string $schema = null,
        ?string $title = null,
        ?string $id = null,
        ?string $ref = null,
        array $definitions = [],
        ?string $description = null,
        ?array $examples = null,
        $default = new NullConst(),

        ?bool $readOnly = null,
        ?bool $writeOnly = null,
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
        public ?ContentEncoding $contentEncoding = null,
        public ?string $contentMediaType = null,
    ) {
        parent::__construct(
            [Type::String],
            schema: $schema,
            id: $id,
            ref: $ref,
            definitions: $definitions,
            title: $title,
            description: $description,
            examples: $examples,
            default: $default,

            readOnly: $readOnly,
            writeOnly: $writeOnly,
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
            + ($this->contentMediaType !== null ? ['contentMediaType' => $this->contentMediaType] : []);
    }
}
