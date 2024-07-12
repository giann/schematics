<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Giann\Schematics\Exception\InvalidSchemaException;
use Giann\Schematics\Exception\InvalidSchemaKeywordValueException;
use Giann\Trunk\Trunk;
use Nette\PhpGenerator\Literal;

class Generator
{
    /**
     * Generate Schema expression from json schema
     * @param array<string, mixed>|Trunk $rawSchema
     * @throws InvalidSchemaException
     * @return Literal
     */
    public function generateSchema(array|Trunk $rawSchema): Literal
    {
        $rawSchema = $rawSchema instanceof Trunk ? $rawSchema : new Trunk($rawSchema);

        // List of parameters to give to the final `new XXXSchema(...)` expression
        /** @var string[] */
        $parameters = [];

        // Common properties
        $this->buildCommonKeywords($rawSchema, $parameters);

        // Get base type
        $baseClass = Schema::class;
        /** @var Type[] */
        $types = isset($rawSchema['type'])
            ? array_map(
                fn (Trunk $type) => Type::from($type->stringValue()),
                $rawSchema['type']->list() !== null
                    ? $rawSchema['type']->listValue()
                    : [$rawSchema['type']]
            )
            : [];
        if (count($types) === 1) {
            switch ($types[0]) {
                case Type::String:
                    $baseClass = StringSchema::class;
                    $this->buildStringKeywords($rawSchema, $parameters);
                    break;
                case Type::Integer:
                    $baseClass = IntegerSchema::class;
                    $this->buildNumberKeywords($rawSchema, $parameters);
                    break;
                case Type::Number:
                    $baseClass = NumberSchema::class;
                    $this->buildNumberKeywords($rawSchema, $parameters);
                    break;
                case Type::Array:
                    $baseClass = ArraySchema::class;
                    $this->buildArrayKeywords($rawSchema, $parameters);
                    break;
                case Type::Boolean:
                    $baseClass = BooleanSchema::class;
                    break;
                case Type::Object:
                    $baseClass = ObjectSchema::class;
                    $this->buildObjectKeywords($rawSchema, $parameters);
                    break;
                case Type::Null:
                    $baseClass = NullSchema::class;
                    break;
                default:
                    $baseClass = Schema::class;
            }
        }

        return Literal::new($baseClass, $parameters);
    }

    /**
     * @param array<string,mixed> $parameters
     * @throws InvalidSchemaException
     * @return void
     */
    private function buildCommonKeywords(Trunk $rawSchema, array &$parameters): void
    {
        foreach ($rawSchema->mapValue() as $property => $value) {
            // Generate parameters
            switch ($property) {
                case 'type':
                    if (
                        // A list
                        ($types = $value->string() !== null ? [new Trunk($value->stringValue())] : $value->list()) === null
                        // Of allowed types
                        || !empty(array_filter(
                            $types,
                            fn (Trunk $type) => !in_array(
                                $type->string(),
                                array_map(fn ($case) => $case->value, Type::cases())
                            )
                        ))
                    ) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string|string[] of those possible values: '
                                . implode(', ', array_map(fn ($case) => $case->value, Type::cases()))
                        );
                    }

                    // No need to set the type if there's only one
                    if (count($types) > 1) {
                        $parameters[$property] = array_map(
                            fn (Trunk $type) => Type::from($type->stringValue()),
                            $types
                        );
                    }

                    break;
                case 'id':
                case 'anchor':
                case '$ref':
                case 'title':
                case 'description':
                    if ($value->string() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string'
                        );
                    }

                    $parameters[preg_replace('/\\$/', '', $property)] = $value->string();

                    break;
                case '$defs':
                    $rawDefs = $value->map();

                    if ($rawDefs === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a array<string,Schema>'
                        );
                    }

                    $parameters['defs'] = [];
                    foreach ($rawDefs as $name => $rawDefSchema) {
                        $parameters['defs'][$name] = $this->generateSchema($rawDefSchema);
                    }

                    break;
                case 'examples':
                case 'enum':
                    if ($value->list() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a mixed[]'
                        );
                    }

                    $parameters[$property] = $value->listRawValue();

                    break;
                case 'default':
                case 'const':
                    $parameters[$property] = $value->data;
                    break;
                case 'deprecated':
                case 'readOnly':
                case 'writeOnly':
                    if ($value->bool() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be bool'
                        );
                    }

                    $parameters[$property] = $value->boolValue();

                    break;
                case 'allOf':
                case 'oneOf':
                case 'anyOf':
                    $subs = $value->list();

                    if ($subs === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema[]'
                        );
                    }

                    $subSchemas = [];
                    foreach ($subs as $subSchema) {
                        $subSchemas[] = $this->generateSchema($subSchema);
                    }
                    $parameters[$property] = $subSchemas;

                    break;
                case 'not':
                    if ($value->map() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema'
                        );
                    }

                    $parameters[$property] = $this->generateSchema($value);

                    break;
            }
        }
    }

    /**
     * @param array<string,mixed> $parameters
     * @throws InvalidSchemaException
     * @return void
     */
    private function buildStringKeywords(Trunk $rawSchema, array &$parameters): void
    {
        foreach ($rawSchema->mapValue() as $property => $value) {
            // Generate parameters
            switch ($property) {
                case 'format':
                    if (
                        !in_array(
                            $value->string(),
                            array_map(fn ($case) => $case->value, Format::cases())
                        )
                    ) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string of those possible values: '
                                . implode(', ', array_map(fn ($case) => $case->value, Format::cases()))
                        );
                    }

                    $parameters[$property] = Format::from($value->stringValue());

                    break;
                case 'minLength':
                case 'maxLength':
                    if ($value->int() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a int'
                        );
                    }

                    $parameters[$property] = $value->intValue();

                    break;
                case 'pattern':
                case 'contentMediaType':
                    if ($value->string() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string'
                        );
                    }

                    $parameters[$property] = $value->stringValue();

                    break;
                case 'contentEncoding':
                    if (
                        !in_array(
                            $value->string(),
                            array_map(fn ($case) => $case->value, ContentEncoding::cases())
                        )
                    ) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string of those possible values: '
                                . implode(', ', array_map(fn ($case) => $case->value, ContentEncoding::cases()))
                        );
                    }

                    $parameters[$property] = ContentEncoding::from($value->stringValue());
                    break;
                case 'contentSchema':
                    if ($value->map() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema'
                        );
                    }

                    $parameters[$property] = $this->generateSchema($value);
                    break;
            }
        }
    }

    /**
     * @param array<string,mixed> $parameters
     * @throws InvalidSchemaException
     * @return void
     */
    private function buildNumberKeywords(Trunk $rawSchema, array &$parameters): void
    {
        foreach ($rawSchema->mapValue() as $property => $value) {
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
                            '`' . $property . '` must be a int|float'
                        );
                    }

                    $parameters[$property] = $number;


                    break;
            }
        }
    }

    /**
     * @param array<string,mixed> $parameters
     * @throws InvalidSchemaException
     * @return void
     */
    private function buildObjectKeywords(Trunk $rawSchema, array &$parameters): void
    {
        foreach ($rawSchema->mapValue() as $property => $value) {
            // Generate parameters
            switch ($property) {
                case 'properties':
                case 'patternProperties':
                case 'dependentSchemas':
                    if ($value->map() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a array<string,Schema>'
                        );
                    }

                    $subSchemas = [];
                    foreach ($value->mapValue() as $key => $subSchema) {
                        $subSchemas[$key] = $this->generateSchema($subSchema);
                    }
                    $parameters[$property] = $subSchemas;

                    break;
                case 'additionalProperties':
                    if ($value->map() === null && ($value->bool() === null || $value->bool() === true)) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema|false'
                        );
                    }

                    $parameters[$property] = $value->bool() !== null
                        ? true
                        : $this->generateSchema($value);

                    break;
                case 'unevaluatedProperties':
                case 'propertyNames':
                    if ($value->map() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema'
                        );
                    }

                    $parameters[$property] = $this->generateSchema($value);

                    break;
                case 'required':
                    if (
                        $value->list() === null
                        || !empty(array_filter(
                            $value->listValue(),
                            fn (Trunk $el) => $el->string() === null
                        ))
                    ) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string[]'
                        );
                    }

                    $parameters[$property] = $value->listRawValue();

                    break;
                case 'minProperties':
                case 'maxProperties':
                    $number = $value->int() ?? $value->float();

                    if ($number === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a int|float'
                        );
                    }

                    $parameters[$property] = $number;

                    break;
                case 'dependentRequired':
                    if (
                        $value->map() === null
                        || !empty(array_filter(
                            $value->mapValue(),
                            fn ($content) => $content->list() === null
                                || array_filter(
                                    $content->listValue(),
                                    fn (Trunk $el) => $el->string() === null
                                )
                        ))
                    ) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a array<string,string[]>'
                        );
                    }

                    $parameters[$property] = $value->mapRawValue();

                    break;
            }
        }
    }

    /**
     * @param array<string,mixed> $parameters
     * @throws InvalidSchemaException
     * @return void
     */
    private function buildArrayKeywords(Trunk $rawSchema, array &$parameters): void
    {
        foreach ($rawSchema->mapValue() as $property => $value) {
            // Generate parameters
            switch ($property) {
                case 'items':
                case 'contains':
                case 'unevaluatedItems':
                    if ($value->map() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema'
                        );
                    }

                    $parameters[$property] = $this->generateSchema($value);

                    break;
                case 'prefixItems':
                    $subs = $value->list();

                    if ($subs === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema[]'
                        );
                    }

                    $subSchemas = [];
                    foreach ($subs as $subSchema) {
                        $subSchemas[] = $this->generateSchema($subSchema);
                    }
                    $parameters[$property] = $subSchemas;

                    break;
                case 'minContains':
                case 'maxContains':
                case 'minItems':
                case 'maxItems':
                    $number = $value->int() ?? $value->float();

                    if ($number === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a int|float'
                        );
                    }

                    $parameters[$property] = $number;

                    break;
                case 'uniqueItems':
                    if ($value->bool() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be bool'
                        );
                    }

                    $parameters[$property] = $value->boolValue();

                    break;
            }
        }
    }
}
