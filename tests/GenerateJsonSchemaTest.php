<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhpParser\PrettyPrinter;
use Giann\Schematics\December2020\ArraySchema;
use Giann\Schematics\December2020\BooleanSchema;
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
    ) {
    }

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

    public function getNotAProperty(): void
    {
    }

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

final class GenerateJsonSchemaTest extends TestCase
{
    public function testBasicSchema(): void
    {
        $this->assertEquals(
            [
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
                            'id', 'names', 'age', 'sex', 'height', 'inheritedComputedProperty',
                        ]
                    ]
                ],
                'required' => [
                    'superName', 'power', 'computed', 'ok',
                ],
            ],
            Schema::classSchema(Hero::class)->jsonSerialize()
        );

        // Generate annotation expression AST from json schema
        $ast = (new Generator)->generateSchema(Schema::classSchema(Hero::class)->jsonSerialize());
        $reconstructed = eval('return ' . (new PrettyPrinter\Standard())->prettyPrintExpr($ast) . ';');

        $this->assertInstanceOf(Schema::class, $reconstructed);
        $this->assertEquals(Schema::classSchema(Hero::class)->jsonSerialize(), $reconstructed->jsonSerialize());
    }

    public function testInvalidSchema(): void
    {
        try {
            (new Generator)->generateSchema(
                [
                    'type' => 'object',
                    'properties' => [
                        'list' => [
                            // Misplaced `items` keyword
                            'items' => [
                                'type' => 'string'
                            ]
                        ]
                    ]
                ]
            );

            $this->assertTrue(false);
        } catch (InvalidSchemaException $e) {
            $this->assertEquals('Invalid or misplaced keywords at #/properties/list: items', $e->getMessage());
        }
    }
}
