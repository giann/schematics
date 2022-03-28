<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Giann\Schematics\ArraySchema;
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

        // Infered $ref to self
        public Person $father,

        // Enum from constants
        #[StringSchema(enumPattern: 'Person::SEX_*')]
        public string $sex,
    ) {
    }
}

// Infer $allOf Person
#[ObjectSchema(additionalProperties: false)]
class Hero extends Person
{
    public function __construct(
        // Infers string property
        public string $superName,
        // Infers enum
        public Power $power,
    ) {
    }
}

final class GenerateJsonSchemaTest extends TestCase
{
    public function testBasicSchema(): void
    {

        echo json_encode(Schema::classSchema(Hero::class), JSON_PRETTY_PRINT);
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
                'additionalProperties' => false,
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
                                '$ref' => '#/definitions/Person'
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
}
