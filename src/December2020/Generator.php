<?php

declare(strict_types=1);

namespace Giann\Schematics\December2020;

use Giann\Schematics\December2020\ArraySchema;
use Giann\Schematics\December2020\BooleanSchema;
use Giann\Schematics\December2020\ContentEncoding;
use Giann\Schematics\December2020\Format;
use Giann\Schematics\December2020\IntegerSchema;
use Giann\Schematics\December2020\NullSchema;
use Giann\Schematics\December2020\NumberSchema;
use Giann\Schematics\December2020\ObjectSchema;
use Giann\Schematics\December2020\Schema;
use Giann\Schematics\December2020\StringSchema;
use Giann\Schematics\December2020\Type;
use Giann\Schematics\Exception\InvalidSchemaException;
use Giann\Schematics\Exception\InvalidSchemaKeywordValueException;
use Giann\Schematics\Exception\InvalidSchemaTypeException;
use Giann\Schematics\GeneratorHelper;
use Giann\Trunk\Trunk;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;

class Generator
{
    private GeneratorHelper $helper;

    public function __construct()
    {
        $this->helper = new GeneratorHelper;
    }

    /**
     * Generate Schema expression from json schema
     * @param array<string, mixed>|Trunk $rawSchema
     * @param string $path
     * @throws InvalidSchemaException
     * @return Expr
     */
    public function generateSchema(array|Trunk $rawSchema, string $path = '#'): Expr
    {
        $rawSchema = $rawSchema instanceof Trunk ? $rawSchema : new Trunk($rawSchema);
        $keywords = array_fill_keys(
            array_keys($rawSchema->mapRawValue()),
            false
        );

        // List of parameters to give to the final `new XXXSchema(...)` expression
        /** @var Arg[] */
        $parameters = [];

        // Common properties
        $this->buildCommonKeywords($rawSchema, $path, $parameters, $keywords);

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
                    $this->buildStringKeywords($rawSchema, $path, $parameters, $keywords);
                    break;
                case Type::Integer:
                    $baseClass = IntegerSchema::class;
                    $this->buildNumberKeywords($rawSchema, $path, $parameters, $keywords);
                    break;
                case Type::Number:
                    $baseClass = NumberSchema::class;
                    $this->buildNumberKeywords($rawSchema, $path, $parameters, $keywords);
                    break;
                case Type::Array:
                    $baseClass = ArraySchema::class;
                    $this->buildArrayKeywords($rawSchema, $path, $parameters, $keywords);
                    break;
                case Type::Boolean:
                    $baseClass = BooleanSchema::class;
                    break;
                case Type::Object:
                    $baseClass = ObjectSchema::class;
                    $this->buildObjectKeywords($rawSchema, $path, $parameters, $keywords);
                    break;
                case Type::Null:
                    $baseClass = NullSchema::class;
                    break;
                default:
                    $baseClass = Schema::class;
            }
        }

        // If some keywords were not processed, it means the schema is invalid
        $unprocessed = array_filter(
            array_keys($keywords),
            fn ($keyword) => $keywords[$keyword] === false && $keyword !== '$schema'
        );
        if (count($unprocessed) > 0) {
            throw new InvalidSchemaException('Invalid or misplaced keywords at ' . $path . ': ' . implode(', ', $unprocessed));
        }

        return new New_(new FullyQualified($baseClass), $parameters);
    }

    /**
     * @param Trunk $rawSchema
     * @param string $path
     * @param Arg[] $parameters
     * @param array<string,bool> $keywords
     * @throws InvalidSchemaException
     * @return void
     */
    private function buildCommonKeywords(Trunk $rawSchema, string $path, array &$parameters, array &$keywords): void
    {
        foreach ($rawSchema->mapValue() as $property => $value) {
            // Generate parameters
            switch ($property) {
                case '$schema':
                    $keywords[$property] = true;
                    break;
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
                            '`' . $property . '` must be a string|string[] of those possible values at ' . $path . ': '
                                . implode(', ', array_map(fn ($case) => $case->value, Type::cases()))
                        );
                    }

                    // No need to set the type if there's only one
                    if (count($types) > 1) {
                        // type: [Type::XXX, ...]
                        $parameters[] = new Arg(
                            name: new Identifier('type'),
                            value: new Array_(
                                array_map(
                                    fn (Trunk $type) => new ArrayItem(
                                        new ClassConstFetch(
                                            new FullyQualified(Type::class),
                                            self::dashToCamel($type->stringValue())
                                        )
                                    ),
                                    $types
                                )
                            )
                        );
                    }

                    $keywords[$property] = true;
                    break;
                case 'id':
                case 'anchor':
                case '$ref':
                case 'title':
                case 'description':
                case '$comment':
                    if ($value->string() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string at ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier(preg_replace('/\\$/', '', $property) ?? $property),
                        value: new String_($value->stringValue()),
                    );

                    $keywords[$property] = true;
                    break;
                case '$defs':
                    $rawDefs = $value->map();

                    if ($rawDefs === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a array<string,Schema> at ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier('defs'),
                        value: new Array_(
                            array_map(
                                fn ($key) => new ArrayItem(
                                    key: new String_((string)$key),
                                    value: $this->generateSchema($rawDefs[$key], $path . '/$defs/' . $key)
                                ),
                                array_keys($rawDefs),
                            ),
                        ),
                    );

                    $keywords[$property] = true;
                    break;
                case 'example': // Not a valid json schema keyword but we do it anyway, converting it to "examples": [single_examplel]
                    $parameters[] = new Arg(
                        name: new Identifier('examples'),
                        value: new Array_(
                            [
                                new ArrayItem($this->helper->phpValueToExpr($value->data))
                            ]
                        )
                    );

                    $keywords[$property] = true;
                    break;
                case 'examples':
                case 'enum':
                    if ($value->list() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a mixed[] at ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Array_(
                            array_map(
                                fn ($value) => new ArrayItem($this->helper->phpValueToExpr($value)),
                                $value->listRawValue()
                            )
                        )
                    );

                    $keywords[$property] = true;
                    break;
                case 'default':
                case 'const':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->helper->phpValueToExpr($value->data)
                    );
                    $keywords[$property] = true;
                    break;
                case 'deprecated':
                case 'readOnly':
                case 'writeOnly':
                    if ($value->bool() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be bool at ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->helper->boolExpr($value->boolValue()),
                    );

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

                    if (isset($rawSchema['type'])) {
                        foreach ($subs as &$sub) {
                            if (
                                !array_key_exists('type', $sub->arrayRawValue()) &&
                                !array_key_exists('$ref', $sub->arrayRawValue())
                            ) {
                                $sub = new Trunk(array_merge(
                                    ['type' => $rawSchema['type']->stringValue()],
                                    $sub->arrayRawValue()
                                ));
                            }
                        }
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Array_(
                            array_map(
                                fn ($index, $subSchema) => new ArrayItem($this->generateSchema($subSchema, $path . '/' . $property . '/' . $index)),
                                array_keys($subs),
                                $subs
                            )
                        )
                    );

                    $keywords[$property] = true;
                    break;
                case 'not':
                case 'if':
                case 'then':
                case 'else':
                    if ($value->map() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema at ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->generateSchema($value, $path . '/' . $property),
                    );

                    $keywords[$property] = true;
                    break;
            }
        }
    }

    private static function dashToCamel(string $input): string
    {
        return ucfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $input))));
    }

    /**
     * @param Trunk $rawSchema
     * @param string $path
     * @param Arg[] $parameters
     * @param array<string,bool> $keywords
     * @throws InvalidSchemaException
     * @return void
     */
    public function buildStringKeywords(Trunk $rawSchema, string $path, array &$parameters, array &$keywords): void
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
                            '`' . $property . '` must be a string of those possible values at ' . $path . ': '
                                . implode(', ', array_map(fn ($case) => $case->value, Format::cases()))
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new ClassConstFetch(
                            new FullyQualified(Format::class),
                            self::dashToCamel($value->stringValue())
                        )
                    );

                    $keywords[$property] = true;
                    break;
                case 'minLength':
                case 'maxLength':
                    if ($value->int() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a int at ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new LNumber($value->intValue()),
                    );

                    $keywords[$property] = true;
                    break;
                case 'pattern':
                case 'contentMediaType':
                    if ($value->string() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string at ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new String_($value->stringValue()),
                    );

                    $keywords[$property] = true;
                    break;
                case 'contentEncoding':
                    if (
                        !in_array(
                            $value->string(),
                            array_map(fn ($case) => $case->value, ContentEncoding::cases())
                        )
                    ) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string of those possible values at ' . $path . ': '
                                . implode(', ', array_map(fn ($case) => $case->value, ContentEncoding::cases()))
                        );
                    }

                    $parameters[] = new ClassConstFetch(
                        new FullyQualified(ContentEncoding::class),
                        self::dashToCamel($value->stringValue())
                    );

                    $keywords[$property] = true;
                    break;
                case 'contentSchema':
                    if ($value->map() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema at ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->generateSchema($value, $path . '/' . $property),
                    );

                    $keywords[$property] = true;
                    break;
            }
        }
    }

    /**
     * @param Trunk $rawSchema
     * @param string $path
     * @param Arg[] $parameters
     * @param array<string,bool> $keywords
     * @throws InvalidSchemaException
     * @return void
     */
    public function buildNumberKeywords(Trunk $rawSchema, string $path, array &$parameters, array &$keywords): void
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
                            '`' . $property . '` must be a int|float at ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $value->int() !== null
                            ? new LNumber($value->intValue())
                            : new DNumber($value->floatValue()),
                    );

                    $keywords[$property] = true;
                    break;
            }
        }
    }

    /**
     * @param Trunk $rawSchema
     * @param string $path
     * @param Arg[] $parameters
     * @param array<string,bool> $keywords
     * @throws InvalidSchemaException
     * @return void
     */
    public function buildObjectKeywords(Trunk $rawSchema, string $path, array &$parameters, array &$keywords): void
    {
        foreach ($rawSchema->mapValue() as $property => $value) {
            // Generate parameters
            switch ($property) {
                case 'properties':
                case 'patternProperties':
                case 'dependentSchemas':
                    $subSchemas = $value->map();

                    if ($subSchemas === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a array<string,Schema> at ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Array_(
                            array_map(
                                fn ($key) => new ArrayItem(
                                    key: new String_((string)$key),
                                    value: $this->generateSchema($subSchemas[$key], $path . '/' . $property . '/' . $key)
                                ),
                                array_keys($subSchemas),
                            ),
                        ),
                    );

                    $keywords[$property] = true;
                    break;
                case 'additionalProperties':
                    if ($value->map() === null && ($value->bool() === null || $value->bool() === true)) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema|false at ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $value->bool() !== null
                            ? $this->helper->falseExpr()
                            : $this->generateSchema($value, $path . '/' . $property)
                    );

                    $keywords[$property] = true;
                    break;
                case 'unevaluatedProperties':
                case 'propertyNames':
                    if ($value->map() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema at ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->generateSchema($value, $path . '/' . $property),
                    );

                    $keywords[$property] = true;
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
                            '`' . $property . '` must be a string[] at ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Array_(
                            array_map(
                                fn ($el) => new ArrayItem(new String_($el->stringValue())),
                                $value->listValue(),
                            )
                        )
                    );

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

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new LNumber($number),
                    );

                    $keywords[$property] = true;
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
                            '`' . $property . '` must be a array<string,string[]> at ' . $path
                        );
                    }

                    $parameters[$property] = $value->mapRawValue();
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->helper->phpValueToExpr($value->mapRawValue())
                    );

                    $keywords[$property] = true;
                    break;
            }
        }
    }

    /**
     * @param Trunk $rawSchema
     * @param string $path
     * @param Arg[] $parameters
     * @param array<string,bool> $keywords
     * @throws InvalidSchemaException
     * @return void
     */
    public function buildArrayKeywords(Trunk $rawSchema, string $path, array &$parameters, array &$keywords): void
    {
        foreach ($rawSchema->mapValue() as $property => $value) {
            // Generate parameters
            switch ($property) {
                case 'items':
                case 'contains':
                case 'unevaluatedItems':
                    if ($value->map() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema at ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->generateSchema($value, $path . '/' . $property),
                    );

                    $keywords[$property] = true;
                    break;
                case 'prefixItems':
                    $subs = $value->list();

                    if ($subs === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema[] at ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Array_(
                            array_map(
                                fn ($index, $subSchema) => new ArrayItem($this->generateSchema($subSchema, $path . '/' . $property . '/' . $index)),
                                array_keys($subs),
                                $subs
                            )
                        )
                    );

                    $keywords[$property] = true;
                    break;
                case 'minContains':
                case 'maxContains':
                case 'minItems':
                case 'maxItems':
                    $number = $value->int();

                    if ($number === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a int ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new LNumber($number),
                    );

                    $keywords[$property] = true;
                    break;
                case 'uniqueItems':
                    if ($value->bool() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be bool ' . $path
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->helper->boolExpr($value->boolValue()),
                    );

                    $keywords[$property] = true;
                    break;
            }
        }
    }
}
