<?php

declare(strict_types=1);

namespace Giann\Schematics\Draft04;

use Giann\Schematics\Exception\InvalidSchemaException;
use Giann\Schematics\Exception\InvalidSchemaKeywordValueException;
use Giann\Schematics\Exception\InvalidSchemaTypeException;
use Giann\Trunk\Trunk;

class SchemaValidator
{
    /**
     * Validate schema
     *
     * @param array<string,mixed>|Trunk|Schema $schema
     * @param bool $enforceSingleType If true will not allow several type in a schema with other keywords (which can't be expressed with schematics)
     * @return void
     * @throws InvalidSchemaException
     */
    public function validate(array|Trunk|Schema $schema, string $path = '#', bool $enforceSingleType = false): void
    {
        if ($schema instanceof Schema) {
            $schema = new Trunk($schema->jsonSerialize());
        } elseif (is_array($schema)) {
            $schema = new Trunk($schema);
        }

        $keywords = array_fill_keys(
            array_keys($schema->mapRawValue()),
            false
        );

        unset($keywords['$schema']);

        $this->validateCommonKeywords($schema, $path, $keywords);

        $schemaType = $this->getSchemaType($schema);
        $singleTypeEnforce = !$enforceSingleType || count($schemaType) === 1;

        if (in_array(Type::String, $schemaType) && $singleTypeEnforce) {
            $this->validateStringKeywords($schema, $path, $keywords, $enforceSingleType);
        } elseif (in_array(Type::Number, $schemaType) && $singleTypeEnforce) {
            $this->validateNumberKeywords($schema, $path, $keywords, $enforceSingleType);
        } elseif (in_array(Type::Integer, $schemaType) && $singleTypeEnforce) {
            $this->validateIntegerKeywords($schema, $path, $keywords, $enforceSingleType);
        } elseif (in_array(Type::Array, $schemaType) && $singleTypeEnforce) {
            $this->validateArrayKeywords($schema, $path, $keywords, $enforceSingleType);
        } elseif (in_array(Type::Object, $schemaType) && $singleTypeEnforce) {
            $this->validateObjectKeywords($schema, $path, $keywords, $enforceSingleType);
        }

        // If someObject were not processed, it means the schema is invalid
        $unprocessed = array_filter(
            array_keys($keywords),
            fn($keyword) => $keywords[$keyword] === false
        );
        if (count($unprocessed) > 0) {
            throw new InvalidSchemaException('Invalid or misplaced keywords at ' . $path . ': ' . implode(', ', $unprocessed));
        }
    }


    /**
     * @param Trunk $schema
     * @return Type[]
     */
    private function getSchemaType(Trunk $schema): array
    {
        $types = $schema['type']->string() !== null
            ? [$schema['type']->stringValue()]
            : $schema['type']->listOfStringValue();

        // If no type specified, find them out from the used keywords
        if (empty($types)) {
            foreach (array_keys($schema->mapValue()) as $keyword) {
                if (in_array($keyword, [
                    'format',
                    'minLength',
                    'maxLength',
                    'pattern',
                    'contentMediaType',
                    'contentEncoding',
                ])) {
                    $types[] = Type::String->value;
                }

                if (in_array($keyword, [
                    'multipleOf',
                    'minimum',
                    'maximum',
                    'exclusiveMinimum',
                    'exclusiveMaximum',
                ])) {
                    $types[] = Type::Number->value;
                }

                if (in_array($keyword, [
                    'items',
                    'prefixItems',
                    'minItems',
                    'maxItems',
                    'uniqueItems',
                ])) {
                    $types[] = Type::Array->value;
                }

                if (in_array($keyword, [
                    'patternProperties',
                    'properties',
                    'dependencies',
                    'additionalProperties',
                    'required',
                    'minProperties',
                    'maxProperties',
                ])) {
                    $types[] = Type::Object->value;
                }
            }
        }

        $types = array_unique($types);

        sort($types);

        $types = array_filter(
            array_map(
                fn($type) => in_array(
                    $type,
                    array_map(fn($case) => $case->value, Type::cases())
                ) ? Type::from($type) : null,
                $types
            ),
            fn($type) => $type !== null
        );

        return $types;
    }

    /**
     * @param Trunk $schema
     * @param string $path
     * @param array<string,bool> $keywords
     * @return void
     * @throws InvalidSchemaException
     */
    private function validateCommonKeywords(Trunk $schema, string $path, array &$keywords): void
    {
        $schemaType = $this->getSchemaType($schema);

        foreach ($schema->mapValue() as $property => $value) {
            switch ($property) {
                case '$schema':
                case 'default':
                    $keywords[$property] = true;
                    break;
                case 'type':
                    if (
                        // A list
                        ($types = $value->string() !== null ? [new Trunk($value->stringValue())] : $value->list()) === null
                        // Of allowed types
                        || (
                            !empty($types)
                            && !empty(array_filter(
                                $types,
                                fn(Trunk $type) => !in_array(
                                    $type->string(),
                                    array_map(fn($case) => $case->value, Type::cases())
                                )
                            ))
                        )
                    ) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string|string[] of those possible values at ' . $path . ': '
                                . implode(', ', array_map(fn($case) => $case->value, Type::cases()))
                        );
                    }

                    $keywords[$property] = true;
                    break;
                case 'id':
                case '$ref':
                case 'title':
                case 'description':
                    if ($value->string() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string at ' . $path
                        );
                    }

                    $keywords[$property] = true;
                    break;
                case 'definitions':
                    if ($value->map() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a array<string,Schema> at ' . $path
                        );
                    }

                    foreach ($value->mapValue() as $key => $subschema) {
                        $this->validate($subschema, $path . '/$definitions/' . $key);
                    }

                    $keywords[$property] = true;
                    break;
                case 'examples':
                case 'enum':
                    if ($value->list() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a mixed[] at ' . $path
                        );
                    }

                    $keywords[$property] = true;
                    break;
                case 'readOnly':
                case 'writeOnly':
                    if ($value->bool() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be bool at ' . $path
                        );
                    }

                    $keywords[$property] = true;
                    break;
                case 'allOf':
                case 'oneOf':
                case 'anyOf':
                    $subs = $value->list();

                    if ($subs === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema[] at ' . $path
                        );
                    }

                    foreach ($subs as $index => $subschema) {
                        $subschemaType = $this->getSchemaType($subschema);
                        // FIXME: we should resolve references to find out if types are consistent
                        if (
                            !empty($schemaType)
                            && $schemaType !== $subschemaType
                            && (!empty($subschemaType) || $subschema['$ref']->string() === null)
                        ) {
                            throw new InvalidSchemaTypeException(
                                'Property ' . $property . '/' . $index .
                                    ' has an inconsistent type from its parent, expecting type [ ' .
                                    implode(', ', array_map(fn(Type $type) => $type->value, $schemaType)) . ' ]' .
                                    ' but got type [ ' .
                                    implode(', ', array_map(fn(Type $type) => $type->value, $subschemaType)) . ' ]'
                            );
                        }

                        $this->validate($subschema, $path . '/' . $property . '/' . $index);
                    }

                    $keywords[$property] = true;
                    break;
                case 'not':
                    if ($value->map() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema at ' . $path
                        );
                    }

                    $this->validate($value, $path . '/' . $property);

                    $keywords[$property] = true;
                    break;
            }
        }
    }

    /**
     * @param Trunk $schema
     * @param string $path
     * @param array<string,bool> $keywords
     * @param bool $enforceSingleType
     * @return void
     * @throws InvalidSchemaException
     */
    private function validateStringKeywords(Trunk $schema, string $path, array &$keywords, bool $enforceSingleType): void
    {
        foreach ($schema->mapValue() as $property => $value) {
            switch ($property) {
                case 'format':
                    if (
                        !in_array(
                            $value->string(),
                            array_map(fn($case) => $case->value, Format::cases())
                        )
                    ) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string of those possible values at ' . $path . ': '
                                . implode(', ', array_map(fn($case) => $case->value, Format::cases()))
                        );
                    }

                    $keywords[$property] = true;
                    break;
                case 'minLength':
                case 'maxLength':
                    if ($value->int() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a int at ' . $path
                        );
                    }

                    $keywords[$property] = true;
                    break;
                case 'pattern':
                case 'contentMediaType':
                    if ($value->string() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string at ' . $path
                        );
                    }

                    $keywords[$property] = true;
                    break;
                case 'contentEncoding':
                    if (
                        !in_array(
                            $value->string(),
                            array_map(fn($case) => $case->value, ContentEncoding::cases())
                        )
                    ) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string of those possible values at ' . $path . ': '
                                . implode(', ', array_map(fn($case) => $case->value, ContentEncoding::cases()))
                        );
                    }

                    $keywords[$property] = true;
                    break;
            }
        }
    }

    /**
     * @param Trunk $schema
     * @param string $path
     * @param array<string,bool> $keywords
     * @param bool $enforceSingleType
     * @return void
     * @throws InvalidSchemaException
     */
    private function validateNumberKeywords(Trunk $schema, string $path, array &$keywords, bool $enforceSingleType): void
    {
        foreach ($schema->mapValue() as $property => $value) {
            // Generate parameters
            switch ($property) {
                case 'multipleOf':
                case 'minimum':
                case 'maximum':
                case 'exclusiveMinimum':
                case 'exclusiveMaximum':
                    $number = $value->int() ?? $value->float();

                    if ($number === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a int|float at ' . $path
                        );
                    }

                    $keywords[$property] = true;
                    break;
            }
        }
    }

    /**
     * @param Trunk $schema
     * @param string $path
     * @param array<string,bool> $keywords
     * @param bool $enforceSingleType
     * @return void
     * @throws InvalidSchemaException
     */
    private function validateIntegerKeywords(Trunk $schema, string $path, array &$keywords, bool $enforceSingleType): void
    {
        foreach ($schema->mapValue() as $property => $value) {
            // Generate parameters
            switch ($property) {
                case 'multipleOf':
                case 'minimum':
                case 'maximum':
                case 'exclusiveMinimum':
                case 'exclusiveMaximum':
                    $number = $value->int();

                    if ($number === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a int at ' . $path
                        );
                    }

                    $keywords[$property] = true;
                    break;
            }
        }
    }

    /**
     * @param Trunk $schema
     * @param string $path
     * @param array<string,bool> $keywords
     * @param bool $enforceSingleType
     * @return void
     * @throws InvalidSchemaException
     */
    private function validateArrayKeywords(Trunk $schema, string $path, array &$keywords, bool $enforceSingleType): void
    {
        foreach ($schema->mapValue() as $property => $value) {
            switch ($property) {
                case 'items':
                    if ($value->map() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema at ' . $path
                        );
                    }

                    $this->validate($value, $path . '/' . $property);

                    $keywords[$property] = true;
                    break;
                case 'prefixItems':
                    $subs = $value->list();

                    if ($subs === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema[] at ' . $path
                        );
                    }

                    foreach ($subs as $index => $subschema) {
                        $this->validate($subschema, $path . '/' . $property . '/' . $index);
                    }

                    $keywords[$property] = true;
                    break;
                case 'minItems':
                case 'maxItems':
                    $number = $value->int();

                    if ($number === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a int ' . $path
                        );
                    }

                    $keywords[$property] = true;
                    break;
                case 'uniqueItems':
                    if ($value->bool() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be bool ' . $path
                        );
                    }

                    $keywords[$property] = true;
                    break;
            }
        }
    }

    /**
     * @param Trunk $schema
     * @param string $path
     * @param array<string,bool> $keywords
     * @param bool $enforceSingleType
     * @return void
     * @throws InvalidSchemaException
     */
    private function validateObjectKeywords(Trunk $schema, string $path, array &$keywords, bool $enforceSingleType): void
    {
        foreach ($schema->mapValue() as $property => $value) {
            switch ($property) {
                case 'patternProperties':
                case 'properties':
                    $subs = $value->map();

                    if ($subs === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a array<string,Schema> at ' . $path
                        );
                    }

                    foreach ($subs as $key => $subschema) {
                        $this->validate($subschema, $path . '/' . $property . '/' . $key);
                    }

                    $keywords[$property] = true;
                    break;
                case 'dependencies':
                    $subs = $value->map();

                    if (empty($subs)) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a non empty array<string,Schema|string[]> at ' . $path
                        );
                    }

                    /** @var ?bool */
                    $is_string_array = null;
                    foreach ($subs as $key => $subschema) {
                        if ($subschema->string() !== null) {
                            if ($is_string_array === false) {
                                throw new InvalidSchemaException(
                                    '`' . $property . '` must be a non empty array<string,Schema|string[]> at ' . $path
                                );
                            }

                            $is_string_array = true;
                        } else {
                            if ($is_string_array === true || $subschema->map() === null) {
                                throw new InvalidSchemaException(
                                    '`' . $property . '` must be a non empty array<string,Schema|string[]> at ' . $path
                                );
                            }

                            $is_string_array = false;
                        }

                        if ($is_string_array === false) {
                            $this->validate($subschema, $path . '/' . $property . '/' . $key);
                        }
                    }

                    $keywords[$property] = true;
                    break;
                case 'additionalProperties':
                    if ($value->map() === null && ($value->bool() === null || $value->bool() === true)) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema|false at ' . $path
                        );
                    }

                    if ($subschema = $value->map()) {
                        $this->validate($subschema, $path . '/' . $property);
                    }

                    $keywords[$property] = true;
                    break;
                case 'required':
                    if (
                        $value->list() === null
                        || !empty(array_filter(
                            $value->listValue(),
                            fn(Trunk $el) => $el->string() === null
                        ))
                    ) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string[] at ' . $path
                        );
                    }

                    $keywords[$property] = true;
                    break;
                case 'minProperties':
                case 'maxProperties':
                    $number = $value->int();

                    if ($number === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a int at ' . $path
                        );
                    }

                    $keywords[$property] = true;
                    break;
            }
        }
    }
}
