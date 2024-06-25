<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Giann\Schematics\ArraySchema;
use Giann\Schematics\Exception\InvalidSchemaValueException;
use Giann\Schematics\Format;
use Giann\Schematics\IntegerSchema;
use Giann\Schematics\NotRequired;
use Giann\Schematics\ObjectSchema;
use Giann\Schematics\Property\Description;
use Giann\Schematics\Schema;
use Giann\Schematics\StringSchema;
use Giann\Schematics\Validator;

enum Sex: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';
}

#[ObjectSchema()]
class Person
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

        #[StringSchema(enumClass: Sex::class)]
        public string $sex,

        // Inferred oneOf type
        public string|int $height,

        // Inferred $ref to self
        #[NotRequired]
        public ?Person $father = null
    ) {
    }
}

enum Power: string
{
    case Fly = 'weeeee!';
    case Strong = 'smash!';
    case Psychic = 'hummmm!';
}

// Infer $allOf Person
#[ObjectSchema()]
class Hero extends Person
{
    public function __construct(
        string $id,
        array $names,
        int $age,
        string $sex,
        string|int $height,

        // Inferred string property
        public string $superName,

        #[StringSchema(enumClass: Power::class)]
        public string $power,

        ?Person $father = null,
    ) {
        parent::__construct($id, $names, $age, $sex, $height, $father);
    }
}

final class GenerateJsonSchemaTest extends TestCase
{
    public function testBasicSchema(): void
    {
        $this->assertEquals(
            [
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
                                'oneOf' => [
                                    [
                                        'type' => 'string'
                                    ],
                                    [
                                        'type' => 'integer'
                                    ]
                                ]
                            ]
                        ],
                        'required' => [
                            'id', 'names', 'age', 'sex', 'height'
                        ]
                    ]
                ],
                'required' => [
                    'superName', 'power',
                ],
            ],
            Schema::classSchema(Hero::class)->jsonSerialize()
        );
    }

    public function testBasicValidation(): void
    {
        try {
            $thor = new Hero(
                id: 'f554a7c7-5c33-415f-a0ca-db19be81f868',
                names: ['Bruce Banner'],
                age: 30,
                sex: Sex::Male->value,
                height: 174,
                superName: 'Thor',
                power: Power::Strong->value,
            );

            (new Validator())->validateInstance($thor);

            $this->assertTrue(true);
        } catch (InvalidSchemaValueException $e) {
            $this->assertTrue(false);
        }
    }

    public function testBasicValidationError(): void
    {
        try {
            $thor = new Hero(
                id: 'dumpid',
                names: ['Bruce Banner'],
                age: 30,
                sex: Sex::Male->value,
                height: '174',
                superName: 'Thor',
                power: Power::Strong->value,
            );

            (new Validator())->validateInstance($thor);

            $this->assertTrue(false);
        } catch (InvalidSchemaValueException $e) {
            $this->assertEquals('Expected to be uuid got `dumpid` at #/allOf/0/#/$defs/Person/id', $e->getMessage());
        }
    }
}
