<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Giann\Schematics\ArraySchema;
use Giann\Schematics\InvalidSchemaValueException;
use Giann\Schematics\NumberSchema;
use Giann\Schematics\ObjectSchema;
use Giann\Schematics\Schema;
use Giann\Schematics\StringFormat;
use Giann\Schematics\StringSchema;

enum Power: string
{
    case fly = 'weeeee!';
    case strong = 'smash!';
    case psychic = 'hummmm!';
}

#[ObjectSchema]
class Person
{
    const SEX_MALE = 'male';
    const SEX_FEMALE = 'female';
    const SEX_OTHER = 'other';

    public function __construct(
        #[StringSchema(format: StringFormat::Uuid)]
        public string $id,

        #[ArraySchema(items: new StringSchema(), minContains: 1)]
        public array $names,

        #[NumberSchema(integer: true, minimum: 0)]
        public int $age,

        // Enum from constants
        #[StringSchema(enumPattern: 'Person::SEX_*')]
        public string $sex,

        // Infered $ref to self
        public ?Person $father = null,
    ) {
    }
}

// Infer $allOf Person
#[ObjectSchema] //additionalProperties: false)]
class Hero extends Person
{
    public function __construct(
        string $id,
        array $names,
        int $age,
        string $sex,
        // Infers string property
        public string $superName,
        // Infers enum
        public Power $power,
        ?Person $father = null,
    ) {
        parent::__construct($id, $names, $age, $sex, $father);
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
                    ]
                ],
                'allOf' => [
                    [
                        '$ref' => '#/definitions/Person'
                    ]
                ],
                // 'additionalProperties' => false,
                'definitions' => [
                    'Person' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => [
                                'type' => 'string',
                                'format' => 'uuid'
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
                                        '$ref' => '#/definitions/Person'
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
                            ]
                        ]
                    ]
                ]
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
                sex: Person::SEX_MALE,
                superName: 'Thor',
                power: Power::strong,
            );

            Schema::validateInstance($thor);

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
                sex: Person::SEX_MALE,
                superName: 'Thor',
                power: Power::strong,
            );

            Schema::validateInstance($thor);

            $this->assertTrue(false);
        } catch (InvalidSchemaValueException $e) {
            $this->assertEquals('Expected to be uuid', $e->getMessage());
        }
    }
}
