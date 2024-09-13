<?php

declare(strict_types=1);

namespace Giann\Schematics\Draft04;

use Giann\Schematics\Draft04\ArraySchema;
use Giann\Schematics\Draft04\BooleanSchema;
use Giann\Schematics\Draft04\ContentEncoding;
use Giann\Schematics\Draft04\Format;
use Giann\Schematics\Draft04\NullSchema;
use Giann\Schematics\Draft04\NumberSchema;
use Giann\Schematics\Draft04\ObjectSchema;
use Giann\Schematics\Draft04\Schema;
use Giann\Schematics\Draft04\StringSchema;
use Giann\Schematics\Draft04\Type;
use Giann\Schematics\Exception\InvalidSchemaException;
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
    private SchemaValidator $validator;

    public function __construct()
    {
        $this->helper = new GeneratorHelper;
        $this->validator = new SchemaValidator;
    }

    /**
     * Generate Schema expression from json schema
     * @param array<string, mixed>|Trunk $rawSchema
     * @param bool $root
     * @throws InvalidSchemaException
     * @return Expr
     */
    public function generateSchema(array|Trunk $rawSchema, bool $root = true): Expr
    {
        if ($root) {
            $this->validator->validate($rawSchema, enforceSingleType: true);
        }

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
                fn(Trunk $type) => Type::from($type->stringValue()),
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
                case Type::Number:
                    $baseClass = NumberSchema::class;
                    $this->buildNumberKeywords($rawSchema, $parameters);
                    break;
                case Type::Integer:
                    $baseClass = IntegerSchema::class;
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
     * @param Trunk $rawSchema
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
                    $types = $value->string() !== null ? [new Trunk($value->stringValue())] : $value->listValue();

                    // No need to set the type if there's only one
                    if (count($types) > 1) {
                        // type: [Type::XXX, ...]
                        $parameters[] = new Arg(
                            name: new Identifier('type'),
                            value: new Array_(
                                array_map(
                                    fn(Trunk $type) => new ArrayItem(
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
                    break;
                case 'id':
                case '$ref':
                case 'title':
                case 'description':
                    $parameters[] = new Arg(
                        name: new Identifier(preg_replace('/\\$/', '', $property) ?? $property),
                        value: new String_($value->stringValue()),
                    );
                    break;
                case 'definitions':
                    $rawDefs = $value->mapValue();

                    $parameters[] = new Arg(
                        name: new Identifier('definitions'),
                        value: new Array_(
                            array_map(
                                fn($key) => new ArrayItem(
                                    key: new String_((string)$key),
                                    value: $this->generateSchema($rawDefs[$key], root: false)
                                ),
                                array_keys($rawDefs),
                            ),
                        ),
                    );

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

                    break;
                case 'examples':
                case 'enum':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Array_(
                            array_map(
                                fn($value) => new ArrayItem($this->helper->phpValueToExpr($value)),
                                $value->listRawValue()
                            )
                        )
                    );

                    break;
                case 'default':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->helper->phpValueToExpr($value->data)
                    );
                    break;
                case 'readOnly':
                case 'writeOnly':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->helper->boolExpr($value->boolValue()),
                    );
                    break;
                case 'allOf':
                case 'oneOf':
                case 'anyOf':
                    $subs = $value->listValue();

                    if (isset($rawSchema['type'])) {
                        foreach ($subs as &$sub) {
                            if (!isset($sub['type']) && !isset($sub['$ref'])) {
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
                                fn($subSchema) => new ArrayItem($this->generateSchema($subSchema, root: false)),
                                $subs
                            )
                        )
                    );

                    break;
                case 'not':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->generateSchema($value, root: false),
                    );

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
     * @param Arg[] $parameters
     * @throws InvalidSchemaException
     * @return void
     */
    public function buildStringKeywords(Trunk $rawSchema, array &$parameters): void
    {
        foreach ($rawSchema->mapValue() as $property => $value) {
            // Generate parameters
            switch ($property) {
                case 'format':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new ClassConstFetch(
                            new FullyQualified(Format::class),
                            self::dashToCamel($value->stringValue())
                        )
                    );
                    break;
                case 'minLength':
                case 'maxLength':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new LNumber($value->intValue()),
                    );
                    break;
                case 'pattern':
                case 'contentMediaType':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new String_($value->stringValue()),
                    );
                    break;
                case 'contentEncoding':
                    $parameters[] = new ClassConstFetch(
                        new FullyQualified(ContentEncoding::class),
                        self::dashToCamel($value->stringValue())
                    );
                    break;
            }
        }
    }

    /**
     * @param Trunk $rawSchema
     * @param Arg[] $parameters
     * @throws InvalidSchemaException
     * @return void
     */
    public function buildNumberKeywords(Trunk $rawSchema, array &$parameters): void
    {
        foreach ($rawSchema->mapValue() as $property => $value) {
            // Generate parameters
            switch ($property) {
                case 'multipleOf':
                case 'minimum':
                case 'maximum':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $value->int() !== null
                            ? new LNumber($value->intValue())
                            : new DNumber($value->floatValue()),
                    );

                    break;
                case 'exclusiveMinimum':
                case 'exclusiveMaximum':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->helper->boolExpr($value->boolValue())
                    );
                    break;
            }
        }
    }

    /**
     * @param Trunk $rawSchema
     * @param Arg[] $parameters
     * @throws InvalidSchemaException
     * @return void
     */
    public function buildObjectKeywords(Trunk $rawSchema, array &$parameters): void
    {
        foreach ($rawSchema->mapValue() as $property => $value) {
            // Generate parameters
            switch ($property) {
                case 'patternProperties':
                case 'properties':
                    $subSchemas = $value->mapValue();

                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Array_(
                            array_map(
                                fn($key) => new ArrayItem(
                                    key: new String_((string)$key),
                                    value: $this->generateSchema($subSchemas[$key], root: false)
                                ),
                                array_keys($subSchemas),
                            ),
                        ),
                    );
                    break;
                case 'dependencies':
                    $subSchemas = $value->mapValue();

                    // Either string[]
                    if ($subSchemas[array_keys($subSchemas)[0]]->string() !== null) {
                        $parameters[] = new Arg(
                            name: new Identifier($property),
                            value: new Array_(
                                array_map(
                                    fn($key) => new ArrayItem(
                                        key: new String_((string)$key),
                                        value: new Array_(
                                            array_map(
                                                fn(Trunk $string) => new ArrayItem(
                                                    new String_($string->stringValue())
                                                ),
                                                $subSchemas[$key]->arrayValue(),
                                            )
                                        )
                                    ),
                                    array_keys($subSchemas),
                                ),
                            ),
                        );
                    } else {
                        // Or Schema
                        $parameters[] = new Arg(
                            name: new Identifier($property),
                            value: new Array_(
                                array_map(
                                    fn($key) => new ArrayItem(
                                        key: new String_((string)$key),
                                        value: $this->generateSchema($subSchemas[$key], root: false)
                                    ),
                                    array_keys($subSchemas),
                                ),
                            ),
                        );
                    }
                    break;
                case 'additionalProperties':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $value->bool() !== null
                            ? $this->helper->falseExpr()
                            : $this->generateSchema($value, root: false)
                    );
                    break;
                case 'required':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new Array_(
                            array_map(
                                fn($el) => new ArrayItem(new String_($el->stringValue())),
                                $value->listValue(),
                            )
                        )
                    );
                    break;
                case 'minProperties':
                case 'maxProperties':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new LNumber($value->intValue()),
                    );
                    break;
            }
        }
    }

    /**
     * @param Trunk $rawSchema
     * @param Arg[] $parameters
     * @throws InvalidSchemaException
     * @return void
     */
    public function buildArrayKeywords(Trunk $rawSchema, array &$parameters): void
    {
        foreach ($rawSchema->mapValue() as $property => $value) {
            // Generate parameters
            switch ($property) {
                case 'items':
                    if ($value->map() !== null) {
                        $parameters[] = new Arg(
                            name: new Identifier($property),
                            value: $this->generateSchema($value, root: false),
                        );
                    } elseif ($value->list() !== null) {
                        $parameters[] = new Arg(
                            name: new Identifier($property),
                            value: new Array_(
                                array_map(
                                    fn($el) => new ArrayItem(
                                        $this->generateSchema($el, root: false),
                                    ),
                                    $value->listValue(),
                                )
                            )
                        );
                    }
                    break;
                case 'additionalItems':
                    if ($value->map() !== null) {
                        $parameters[] = new Arg(
                            name: new Identifier($property),
                            value: $this->generateSchema($value, root: false),
                        );
                    } else {
                        $parameters[] = new Arg(
                            name: new Identifier($property),
                            value: $this->helper->boolExpr($value->boolValue()),
                        );
                    }
                    break;
                case 'minItems':
                case 'maxItems':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: new LNumber($value->intValue()),
                    );
                    break;
                case 'uniqueItems':
                    $parameters[] = new Arg(
                        name: new Identifier($property),
                        value: $this->helper->boolExpr($value->boolValue()),
                    );
                    break;
            }
        }
    }
}
