<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Giann\Schematics\Exception\InvalidSchemaException;
use Giann\Schematics\Exception\InvalidSchemaKeywordValueException;
use Giann\Trunk\Trunk;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class Generator
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->createForNewestSupportedVersion();
    }

    private static function trueExpr(): Expr
    {
        return new ConstFetch(new Name('true'));
    }

    private static function falseExpr(): Expr
    {
        return new ConstFetch(new Name('false'));
    }

    /**
     * @param mixed $value
     * @throws InvalidSchemaException
     * @return Expr
     */
    private function phpValueToExpr(mixed $value): Expr
    {
        $parsed = $this->parser->parse(
            '<?php '
                . var_export($value, true)
                . ';'
        ) ?? [];

        if (count($parsed) == 1 && $parsed[0] instanceof Expression) {
            /** @var Expression */
            $exprStmt = $parsed[0];
            return $exprStmt->expr;
        }

        throw new InvalidSchemaException();
    }

    /**
     * Generate Schema expression from json schema
     * @param array<string, mixed>|Trunk $rawSchema
     * @throws InvalidSchemaException
     * @return Expr
     */
    public function generateSchema(array|Trunk $rawSchema): Expr
    {
        $rawSchema = $rawSchema instanceof Trunk ? $rawSchema : new Trunk($rawSchema);

        // List of parameters to give to the final `new XXXSchema(...)` expression
        /** @var Arg[] */
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

        return new New_(new FullyQualified($baseClass), $parameters);
    }

    /**
     * @param Arg[] $parameters
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
                        // type: [Type::XXX, ...]
                        $parameters[] = new Arg(
                            name: new Identifier('type'),
                            value: new Array_(
                                array_map(
                                    fn (Trunk $type) => new ArrayItem(
                                        new StaticCall(
                                            new FullyQualified(Type::class),
                                            'from',
                                            [
                                                new Arg(new String_($type->stringValue()))
                                            ]
                                        )
                                    ),
                                    $types
                                )
                            )
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

                    $parameters[] = new Arg(
                        name: new Identifier(preg_replace('/\\$/', '', $property) ?? $property),
                        value: new String_($value->stringValue()),
                    );

                    break;
                case '$defs':
                    $rawDefs = $value->map();

                    if ($rawDefs === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a array<string,Schema>'
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier('defs'),
                        value: new Array_(
                            array_map(
                                fn ($key) => new ArrayItem(
                                    key: new String_($key),
                                    value: $this->generateSchema($rawDefs[$key])
                                ),
                                array_keys($rawDefs),
                            ),
                        ),
                    );

                    break;
                case 'examples':
                case 'enum':
                    if ($value->list() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a mixed[]'
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Array_(
                            array_map(
                                fn ($value) => new ArrayItem($this->phpValueToExpr($value)),
                                $value->listRawValue()
                            )
                        )
                    );

                    break;
                case 'default':
                case 'const':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->phpValueToExpr($value->data)
                    );
                    break;
                case 'deprecated':
                case 'readOnly':
                case 'writeOnly':
                    if ($value->bool() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be bool'
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $value->boolValue() ? self::trueExpr() : self::falseExpr(),
                    );

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

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Array_(
                            array_map(
                                fn ($subSchema) => new ArrayItem($this->generateSchema($subSchema)),
                                $subs
                            )
                        )
                    );

                    break;
                case 'not':
                    if ($value->map() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema'
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->generateSchema($value),
                    );

                    break;
            }
        }
    }

    /**
     * @param Arg[] $parameters
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

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new StaticCall(
                            new FullyQualified(Format::class),
                            'from',
                            [
                                new Arg(new String_($value->stringValue()))
                            ]
                        )
                    );

                    break;
                case 'minLength':
                case 'maxLength':
                    if ($value->int() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a int'
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Int_($value->intValue()),
                    );

                    break;
                case 'pattern':
                case 'contentMediaType':
                    if ($value->string() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a string'
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new String_($value->stringValue()),
                    );

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

                    $parameters[] = new StaticCall(
                        new FullyQualified(ContentEncoding::class),
                        'from',
                        [
                            new Arg(new String_($value->stringValue()))
                        ]
                    );

                    break;
                case 'contentSchema':
                    if ($value->map() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema'
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->generateSchema($value),
                    );

                    break;
            }
        }
    }

    /**
     * @param Arg[] $parameters
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

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $value->int() !== null
                            ? new Int_($value->intValue())
                            : new Float_($value->floatValue()),
                    );

                    break;
            }
        }
    }

    /**
     * @param Arg[] $parameters
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
                    $subSchemas = $value->map();

                    if ($subSchemas === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a array<string,Schema>'
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Array_(
                            array_map(
                                fn ($key) => new ArrayItem(
                                    key: new String_($key),
                                    value: $this->generateSchema($subSchemas[$key])
                                ),
                                array_keys($subSchemas),
                            ),
                        ),
                    );

                    break;
                case 'additionalProperties':
                    if ($value->map() === null && ($value->bool() === null || $value->bool() === true)) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema|false'
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $value->bool() !== null
                            ? self::falseExpr()
                            : $this->generateSchema($value)
                    );

                    break;
                case 'unevaluatedProperties':
                case 'propertyNames':
                    if ($value->map() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema'
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->generateSchema($value),
                    );

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

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Array_(
                            array_map(
                                fn ($el) => new ArrayItem(new String_($el->stringValue())),
                                $value->listValue(),
                            )
                        )
                    );

                    break;
                case 'minProperties':
                case 'maxProperties':
                    $number = $value->int();

                    if ($number === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a int'
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Int_($number),
                    );

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
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->phpValueToExpr($value->mapRawValue())
                    );

                    break;
            }
        }
    }

    /**
     * @param Arg[] $parameters
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

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->generateSchema($value),
                    );

                    break;
                case 'prefixItems':
                    $subs = $value->list();

                    if ($subs === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a Schema[]'
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Array_(
                            array_map(
                                fn ($subSchema) => new ArrayItem($this->generateSchema($subSchema)),
                                $subs
                            )
                        )
                    );

                    break;
                case 'minContains':
                case 'maxContains':
                case 'minItems':
                case 'maxItems':
                    $number = $value->int();

                    if ($number === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be a int'
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Int_($number),
                    );

                    break;
                case 'uniqueItems':
                    if ($value->bool() === null) {
                        throw new InvalidSchemaKeywordValueException(
                            '`' . $property . '` must be bool'
                        );
                    }

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $value->boolValue()
                            ? self::trueExpr()
                            : self::falseExpr(),
                    );

                    break;
            }
        }
    }
}
