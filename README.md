# schematics

📏 Models that can be translated to JSON Schemas using attributes or docblock annotations.

## Validation

**schematics** can validate data according to the annotated schemas. It currently covers 84% of the [official JSON Schema test suite for the 2020-12 draft](https://github.com/json-schema-org/JSON-Schema-Test-Suite).

## Example

```php
/**
 * @ObjectSchema
 */
class Person
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
}
```

Results in the following JSON Schema:

```json
{
  "type": "object",
  "$defs": {
    "Person": {
      "type": "object",
      "properties": {
        "id": {
          "type": "string",
          "description": "unique id of the person",
          "format": "uuid"
        },
        "names": {
          "type": "array",
          "items": {
            "type": "string"
          },
          "minContains": 1
        },
        "age": {
          "type": "integer",
          "minimum": 0
        },
        "sex": {
          "type": "string",
          "enum": ["male", "female", "other"]
        },
        "father": {
          "oneOf": [
            {
              "type": "null"
            },
            {
              "$ref": "#/$defs/Person"
            }
          ]
        }
      }
    }
  },
  "allOf": [
    {
      "$ref": "#/$defs/Person"
    }
  ],
  "properties": {
    "superName": {
      "type": "string"
    },
    "power": {
      "type": "string",
      "enum": ["weeeee!", "smash!", "hummmm!"]
    }
  }
}
```

## Not yet implemented

- string format `7bit`
- string format `8bit`
- string format `binary`
- string format `base16`
- string format `base32`
- [if/then/else](https://json-schema.org/understanding-json-schema/reference/conditionals.html#if-then-else)
- [`unevaluatedProperties`](https://json-schema.org/understanding-json-schema/reference/object.html#unevaluated-properties)
- `$id`
- anchors
- `$ref` other than `#/$defs/<name>`
