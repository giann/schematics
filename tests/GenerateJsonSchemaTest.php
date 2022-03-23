<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Giann\Schematics\ArraySchema;
use Giann\Schematics\NumberSchema;
use Giann\Schematics\ObjectSchema;
use Giann\Schematics\Schema;
use Giann\Schematics\StringFormat;
use Giann\Schematics\StringSchema;

#[ObjectSchema]
class Person
{
    public function __construct(
        #[StringSchema(format: StringFormat::Uuid)]
        public string $id,

        #[ArraySchema(items: new StringSchema(), minContains: 1)]
        public array $names,

        #[NumberSchema(integer: true, minimum: 0)]
        public int $age,

        // Infered $ref to self
        public Person $father,
    ) {
    }
}

// Infer $allOf Person
#[ObjectSchema(additionalProperties: false)]
class Hero extends Person
{
    public function __construct(
        // Infers string property
        public string $superName
    ) {
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
                            ]
                        ]
                    ]
                ]
            ],
            Schema::classSchema(Hero::class)->jsonSerialize()
        );
    }
}
