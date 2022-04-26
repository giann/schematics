<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Giann\Schematics\ArraySchema;
use Giann\Schematics\InvalidSchemaValueException;
use Giann\Schematics\Model;
use Giann\Schematics\NumberSchema;
use Giann\Schematics\ObjectSchema;
use Giann\Schematics\Schema;
use Giann\Schematics\StringSchema;
use Giann\Schematics\SchemaDescription;

/**
 * @ObjectSchema
 */
class Person implements Model
{
    const SEX_MALE = 'male';
    const SEX_FEMALE = 'female';
    const SEX_OTHER = 'other';

    /**
     * @StringSchema(format = StringSchema::FORMAT_UUID)
     * @SchemaDescription("unique id of the person")
     */
    public string $id;

    /**
     * @ArraySchema(items = @StringSchema, minContains = 1)
     */
    public array $names;

    /**
     * @NumberSchema(integer = true, minimum = 0)
     */
    public int $age;

    // Enum from constants
    /**
     * @StringSchema(enumPattern = "Person::SEX_*")
     */
    public string $sex;

    // Infered $ref to self
    public ?Person $father = null;

    public function __construct(
        string $id,
        array $names,
        int $age,
        string $sex,
        ?Person $father = null
    ) {
        $this->id = $id;
        $this->names = $names;
        $this->age = $age;
        $this->sex = $sex;
        $this->father = $father;
    }

    public static function fromJson(object $json): object
    {
        return new Person(
            $json['id'],
            $json['names'],
            $json['age'],
            $json['sex'],
            isset($json['father']) ? Person::fromJson($json) : null,
        );
    }
}

// Infer $allOf Person
/**
 * @ObjectSchema
 */
class Hero extends Person
{
    const POWER_FLY = 'weeeee!';
    const POWER_STRONG = 'smash!';
    const POWER_PSYCHIC = 'hummmm!';

    // Infers string property
    public string $superName;
    /**
     * @StringSchema(enumPattern = "Hero::POWER_*")
     */
    public string $power;

    public function __construct(
        string $id,
        array $names,
        int $age,
        string $sex,
        ?Person $father = null,
        string $superName,
        string $power
    ) {
        parent::__construct($id, $names, $age, $sex, $father);

        $this->superName = $superName;
        $this->power = $power;
    }

    public static function fromJson(object $json): object
    {
        return new Hero(
            $json['id'],
            $json['names'],
            $json['age'],
            $json['sex'],
            isset($json['father']) ? Person::fromJson($json) : null,
            $json['superName'],
            $json['power']
        );
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
                            ]
                        ],
                        'required' => [
                            'id', 'names', 'age', 'sex', 'father'
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
                'f554a7c7-5c33-415f-a0ca-db19be81f868',
                ['Bruce Banner'],
                30,
                Person::SEX_MALE,
                null,
                'Thor',
                Hero::POWER_STRONG
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
                'dumpid',
                ['Bruce Banner'],
                30,
                Person::SEX_MALE,
                null,
                'Thor',
                Hero::POWER_STRONG
            );

            Schema::validateInstance($thor);

            $this->assertTrue(false);
        } catch (InvalidSchemaValueException $e) {
            $this->assertEquals('Expected to be uuid got `dumpid` at #/id', $e->getMessage());
        }
    }
}
