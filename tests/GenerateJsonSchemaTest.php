<?php

declare(strict_types=1);

use Giann\Schematics\December2020\NumberSchema;
use Giann\Schematics\Exception\InvalidSchemaTypeException;
use PHPUnit\Framework\TestCase;
use PhpParser\PrettyPrinter;
use Giann\Schematics\December2020\ArraySchema;
use Giann\Schematics\December2020\BooleanSchema;
use Giann\Schematics\December2020\EntityGenerator;
use Giann\Schematics\Draft;
use Giann\Schematics\Exception\InvalidSchemaException;
use Giann\Schematics\ExcludedFromSchema;
use Giann\Schematics\December2020\Format;
use Giann\Schematics\Generator;
use Giann\Schematics\December2020\IntegerSchema;
use Giann\Schematics\NotRequired;
use Giann\Schematics\December2020\ObjectSchema;
use Giann\Schematics\December2020\Property\Description;
use Giann\Schematics\December2020\Schema;
use Giann\Schematics\December2020\StringSchema;
use Giann\Schematics\Draft04\ArraySchema as Draft04ArraySchema;
use Giann\Schematics\Draft04\BooleanSchema as Draft04BooleanSchema;
use Giann\Schematics\Draft04\ConstSchema as Draft04ConstSchema;
use Giann\Schematics\Draft04\EntityGenerator as Draft04EntityGenerator;
use Giann\Schematics\Draft04\IntegerSchema as Draft04IntegerSchema;
use Giann\Schematics\Draft04\NumberSchema as Draft04NumberSchema;
use Giann\Schematics\Draft04\ObjectSchema as Draft04ObjectSchema;
use Giann\Schematics\Draft04\Property\Description as PropertyDescription;
use Giann\Schematics\Draft04\Schema as Draft04Schema;
use Giann\Schematics\Draft04\StringSchema as Draft04StringSchema;
use Giann\Trunk\Trunk;

enum Sex: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';
}

#[ObjectSchema]
class Person implements JsonSerializable
{
    public function __construct(
        #[StringSchema(format: Format::Uuid)]
        #[Description('unique id of the person')]
        public string $id,

        #[ArraySchema(
            items: new StringSchema(),
            minContains: 1
        )]
        public array $names,

        #[IntegerSchema(minimum: 0)]
        public int $age,

        public Sex $sex,

        #[ExcludedFromSchema]
        public string $ignoreMe,

        // Inferred oneOf type
        public string|int $height = 180,

        // Inferred $ref to self
        #[NotRequired]
        public ?Person $father = null,
    ) {}

    #[IntegerSchema]
    public function getInheritedComputedProperty(): int
    {
        return 12;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'names' => $this->names,
            'age' => $this->age,
            'sex' => $this->sex->value,
            'height' => $this->height,
            'inheritedComputedProperty' => $this->getInheritedComputedProperty(),
        ] + ($this->father !== null ?  ['father' => $this->father->jsonSerialize()] : []);
    }
}

#[Draft04ObjectSchema]
class Person04 implements JsonSerializable
{
    public function __construct(
        #[PropertyDescription('unique id of the person')]
        public string $id,

        #[Draft04ArraySchema(
            items: new Draft04StringSchema(),
        )]
        public array $names,

        #[Draft04IntegerSchema(minimum: 0)]
        public int $age,

        public Sex $sex,

        #[ExcludedFromSchema]
        public string $ignoreMe,

        // Inferred oneOf type
        public string|int $height = 180,

        // Inferred $ref to self
        #[NotRequired]
        public ?Person04 $father = null,
    ) {}

    #[Draft04IntegerSchema]
    public function getInheritedComputedProperty(): int
    {
        return 12;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'names' => $this->names,
            'age' => $this->age,
            'sex' => $this->sex->value,
            'height' => $this->height,
            'inheritedComputedProperty' => $this->getInheritedComputedProperty(),
        ] + ($this->father !== null ?  ['father' => $this->father->jsonSerialize()] : []);
    }
}

enum Power: string
{
    case Fly = 'weeeee!';
    case Strong = 'smash!';
    case Psychic = 'hummmm!';
}

// Infer $allOf Person
#[ObjectSchema]
class Hero extends Person implements JsonSerializable
{
    public function __construct(
        string $id,
        array $names,
        int $age,
        Sex $sex,
        string|int $height,

        // Inferred string property
        public string $superName,

        #[StringSchema(enumClass: Power::class)]
        public string $power,

        ?Person $father = null,
    ) {
        parent::__construct($id, $names, $age, $sex, 'ignore me', $height, $father);
    }

    #[IntegerSchema]
    public function getComputed(): int
    {
        return 12;
    }

    #[BooleanSchema]
    public function isOk(): bool
    {
        return true;
    }

    // Overriden getter should be ignored
    public function getInheritedComputedProperty(): int
    {
        return 13;
    }

    public function getNotAProperty(): void {}

    public function jsonSerialize(): mixed
    {
        return parent::jsonSerialize()
            + [
                'superName' => $this->superName,
                'power' => $this->power,
                'computed' => $this->getComputed(),
                'ok' => $this->isOk(),
            ];
    }
}

#[Draft04ObjectSchema]
class Hero04 extends Person04 implements JsonSerializable
{
    public function __construct(
        string $id,
        array $names,
        int $age,
        Sex $sex,
        string|int $height,

        // Inferred string property
        public string $superName,

        #[Draft04StringSchema(enumClass: Power::class)]
        public string $power,

        ?Person04 $father = null,
    ) {
        parent::__construct($id, $names, $age, $sex, 'ignore me', $height, $father);
    }

    #[Draft04IntegerSchema]
    public function getComputed(): int
    {
        return 12;
    }

    #[Draft04BooleanSchema]
    public function isOk(): bool
    {
        return true;
    }

    // Overriden getter should be ignored
    public function getInheritedComputedProperty(): int
    {
        return 13;
    }

    public function getNotAProperty(): void {}

    public function jsonSerialize(): mixed
    {
        return parent::jsonSerialize()
            + [
                'superName' => $this->superName,
                'power' => $this->power,
                'computed' => $this->getComputed(),
                'ok' => $this->isOk(),
            ];
    }
}

final class Vehicle extends Draft04ConstSchema
{
    public function __construct()
    {
        parent::__construct(
            new Draft04ObjectSchema(
                properties: [
                    'speed' => new Draft04IntegerSchema(),
                    'weight' => new Draft04NumberSchema(),
                ]
            )
        );
    }
}

final class GenerateJsonSchemaTest extends TestCase
{
    public function testBasicSchema(): void
    {
        $rawSchema = [
            '$schema' => Draft::December2020->value,
            'type' => 'object',
            'properties' => [
                'superName' => [
                    'type' => 'string'
                ],
                'power' => [
                    'type' => 'string',
                    'enum' => [
                        'weeeee!',
                        'smash!',
                        'hummmm!',
                    ]
                ],
                'computed' => [
                    'type' => 'integer'
                ],
                'ok' => [
                    'type' => 'boolean'
                ]
            ],
            'allOf' => [
                [
                    '$ref' => '#/$defs/Person'
                ]
            ],
            '$defs' => [
                'Person' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => [
                            'type' => 'string',
                            'format' => 'uuid',
                            'description' => 'unique id of the person'
                        ],
                        'names' => [
                            'type' => 'array',
                            'minContains' => 1,
                            'items' => [
                                'type' => 'string'
                            ]
                        ],
                        'age' => [
                            'type' => 'integer',
                            'minimum' => 0
                        ],
                        'father' => [
                            'oneOf' => [
                                [
                                    'type' => 'null'
                                ],
                                [
                                    '$ref' => '#/$defs/Person'
                                ]
                            ]
                        ],
                        'sex' => [
                            'type' => 'string',
                            'enum' => [
                                'male',
                                'female',
                                'other'
                            ]
                        ],
                        'height' => [
                            'default' => 180,
                            'oneOf' => [
                                [
                                    'type' => 'string'
                                ],
                                [
                                    'type' => 'integer'
                                ]
                            ]
                        ],
                        'inheritedComputedProperty' => [
                            'type' => 'integer'
                        ],
                    ],
                    'required' => [
                        'id',
                        'names',
                        'age',
                        'sex',
                        'height',
                        'inheritedComputedProperty',
                    ]
                ]
            ],
            'required' => [
                'superName',
                'power',
                'computed',
                'ok',
            ],
        ];

        $this->assertEquals(
            $rawSchema,
            Schema::classSchema(Hero::class)->jsonSerialize()
        );

        // Generate annotation expression AST from json schema
        $ast = (new Generator)->generateSchema(Schema::classSchema(Hero::class)->jsonSerialize());
        $reconstructed = eval('return ' . (new PrettyPrinter\Standard())->prettyPrintExpr($ast) . ';');

        // Compare generated annotations schema to schema built from classes
        $this->assertInstanceOf(Schema::class, $reconstructed);
        $this->assertEquals(Schema::classSchema(Hero::class)->jsonSerialize(), $reconstructed->jsonSerialize());

        // Generate classes from schema
        $entities = (new EntityGenerator(new Trunk($rawSchema), namespace: 'Test'))->generateEntities(Hero::class);

        // Run them
        eval((new PrettyPrinter\Standard())->prettyPrint($entities));

        // Get shema from them and compare the schema with original one
        $this->assertEquals(
            $rawSchema,
            // Use json encode to remove namespace from class names
            json_decode(
                str_replace(
                    ['Test\\Person', 'Test\\\\Person'],
                    ['Person', 'Person'],
                    json_encode(
                        Schema::classSchema(Test\Hero::class)->jsonSerialize()
                    ) ?: '',
                ),
                true
            ),
        );
    }

    public function testInvalidInheritanceTypeSchema(): void
    {
        $this->expectException(InvalidSchemaTypeException::class);
        $this->expectExceptionMessage('Property oneOf/0 has an inconsistent type from its parent, expecting type [ object ] but got type [ array ]');

        (new Generator)->generateSchema(
            [
                '$schema' => Draft::December2020->value,
                'type' => 'object',
                'oneOf' => [
                    [
                        'type' => 'array',
                        'maxItems' => 0,
                    ],
                    [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'interest' => ['type' => 'number'],
                        ],
                        'additionalProperties' => false,
                        'required' => ['interest'],
                    ]
                ]
            ]
        );
    }

    public function testValidInheritanceTypeSchema(): void
    {
        $ast = (new Generator)->generateSchema(
            [
                '$schema' => Draft::December2020->value,
                'type' => 'number',
                'oneOf' => [
                    [
                        'multipleOf' => 5,
                    ],
                    [
                        'multipleOf' => 3,
                    ]
                ]
            ]
        );

        $reconstructed = eval('return ' . (new PrettyPrinter\Standard())->prettyPrintExpr($ast) . ';');
        $this->assertInstanceOf(NumberSchema::class, $reconstructed);
        $this->assertNotNull($reconstructed->oneOf);
        foreach ($reconstructed->oneOf as $definition) {
            $this->assertInstanceOf(NumberSchema::class, $definition);
        }

        $ast = (new Generator)->generateSchema(
            [
                '$schema' => Draft::December2020->value,
                'type' => 'object',
                'oneOf' => [
                    [
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'benefit' => ['type' => 'number'],
                        ],
                        'additionalProperties' => false,
                        'required' => ['interest'],
                    ],
                    [
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'interest' => ['type' => 'number'],
                        ],
                        'additionalProperties' => false,
                        'required' => ['interest'],
                    ]
                ]
            ]
        );

        $reconstructed = eval('return ' . (new PrettyPrinter\Standard())->prettyPrintExpr($ast) . ';');
        $this->assertInstanceOf(ObjectSchema::class, $reconstructed);
        $this->assertNotNull($reconstructed->oneOf);
        foreach ($reconstructed->oneOf as $definition) {
            $this->assertInstanceOf(ObjectSchema::class, $definition);
        }
    }

    public function testInvalidSchema(): void
    {
        $this->expectException(InvalidSchemaException::class);
        $this->expectExceptionMessage('Invalid or misplaced keywords at #/properties/list: properties');

        (new Generator)->generateSchema(
            [
                'type' => 'object',
                'properties' => [
                    'list' => [
                        // Misplaced `items` keyword
                        'items' => [
                            'type' => 'string'
                        ],
                        'properties' => [
                            'nope' => [
                                'type' => 'string'
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    public function testDraft04Schema(): void
    {
        $rawSchema = [
            '$schema' => Draft::Draft04->value,
            'type' => 'object',
            'properties' => [
                'superName' => [
                    'type' => 'string'
                ],
                'power' => [
                    'type' => 'string',
                    'enum' => [
                        'weeeee!',
                        'smash!',
                        'hummmm!',
                    ]
                ],
                'computed' => [
                    'type' => 'integer'
                ],
                'ok' => [
                    'type' => 'boolean'
                ]
            ],
            'allOf' => [
                [
                    '$ref' => '#/definitions/Person04'
                ]
            ],
            'definitions' => [
                'Person04' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => [
                            'type' => 'string',
                            'description' => 'unique id of the person'
                        ],
                        'names' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string'
                            ]
                        ],
                        'age' => [
                            'type' => 'integer',
                            'minimum' => 0
                        ],
                        'father' => [
                            'oneOf' => [
                                [
                                    'type' => 'null'
                                ],
                                [
                                    '$ref' => '#/definitions/Person04'
                                ]
                            ]
                        ],
                        'sex' => [
                            'type' => 'string',
                            'enum' => [
                                'male',
                                'female',
                                'other'
                            ]
                        ],
                        'height' => [
                            'default' => 180,
                            'oneOf' => [
                                [
                                    'type' => 'string'
                                ],
                                [
                                    'type' => 'integer'
                                ]
                            ]
                        ],
                        'inheritedComputedProperty' => [
                            'type' => 'integer'
                        ],
                    ],
                    'required' => [
                        'id',
                        'names',
                        'age',
                        'sex',
                        'height',
                        'inheritedComputedProperty',
                    ]
                ]
            ],
            'required' => [
                'superName',
                'power',
                'computed',
                'ok',
            ],
        ];

        $this->assertEquals(
            $rawSchema,
            Draft04Schema::classSchema(Hero04::class)->jsonSerialize()
        );

        // Generate annotation expression AST from json schema
        $ast = (new Generator)->generateSchema(Draft04Schema::classSchema(Hero04::class)->jsonSerialize());
        $reconstructed = eval('return ' . (new PrettyPrinter\Standard())->prettyPrintExpr($ast) . ';');

        // Compare generated annotations schema to schema built from classes
        $this->assertInstanceOf(Draft04Schema::class, $reconstructed);
        $this->assertEquals(Draft04Schema::classSchema(Hero04::class)->jsonSerialize(), $reconstructed->jsonSerialize());

        // Generate classes from schema
        $entities = (new Draft04EntityGenerator(new Trunk($rawSchema), namespace: 'Test'))->generateEntities(Hero04::class);

        // Run them
        eval((new PrettyPrinter\Standard())->prettyPrint($entities));

        // Get shema from them and compare the schema with original one
        $this->assertEquals(
            $rawSchema,
            // Use json encode to remove namespace from class names
            json_decode(
                str_replace(
                    ['Test\\Person04', 'Test\\\\Person04'],
                    ['Person04', 'Person04'],
                    json_encode(
                        Draft04Schema::classSchema(Test\Hero04::class)->jsonSerialize()
                    ) ?: '',
                ),
                true
            ),
        );
    }

    public function testDraft04InvalidInheritanceTypeSchema(): void
    {
        $this->expectException(InvalidSchemaTypeException::class);
        $this->expectExceptionMessage('Property oneOf/0 has an inconsistent type from its parent, expecting type [ object ] but got type [ array ]');

        (new Generator)->generateSchema(
            [
                '$schema' => Draft::Draft04->value,
                'type' => 'object',
                'oneOf' => [
                    [
                        'type' => 'array',
                        'maxItems' => 0,
                    ],
                    [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'interest' => ['type' => 'number'],
                        ],
                        'additionalProperties' => false,
                        'required' => ['interest'],
                    ]
                ]
            ]
        );
    }

    public function testDraft04ValidInheritanceTypeSchema(): void
    {
        $ast = (new Generator)->generateSchema(
            [
                '$schema' => Draft::Draft04->value,
                'type' => 'number',
                'oneOf' => [
                    [
                        'multipleOf' => 5,
                    ],
                    [
                        'multipleOf' => 3,
                    ]
                ]
            ]
        );

        $reconstructed = eval('return ' . (new PrettyPrinter\Standard())->prettyPrintExpr($ast) . ';');
        $this->assertInstanceOf(Draft04NumberSchema::class, $reconstructed);
        $this->assertNotNull($reconstructed->oneOf);
        foreach ($reconstructed->oneOf as $definition) {
            $this->assertInstanceOf(Draft04NumberSchema::class, $definition);
        }

        $ast = (new Generator)->generateSchema(
            [
                '$schema' => Draft::Draft04->value,
                'type' => 'object',
                'oneOf' => [
                    [
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'benefit' => ['type' => 'number'],
                        ],
                        'additionalProperties' => false,
                        'required' => ['interest'],
                    ],
                    [
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'interest' => ['type' => 'number'],
                        ],
                        'additionalProperties' => false,
                        'required' => ['interest'],
                    ]
                ]
            ]
        );

        $reconstructed = eval('return ' . (new PrettyPrinter\Standard())->prettyPrintExpr($ast) . ';');
        $this->assertInstanceOf(Draft04ObjectSchema::class, $reconstructed);
        $this->assertNotNull($reconstructed->oneOf);
        foreach ($reconstructed->oneOf as $definition) {
            $this->assertInstanceOf(Draft04ObjectSchema::class, $definition);
        }
    }

    public function testConstSchema(): void
    {
        $this->assertEquals(
            [
                '$schema' => Draft::Draft04->value,
                'type' => 'object',
                'properties' => [
                    'speed' => [
                        'type' => 'integer'
                    ],
                    'weight' => [
                        'type' => 'number'
                    ]
                ]
            ],
            (new Vehicle)->jsonSerialize()
        );
    }
}
