# schematics

Translates php classes to JSON Schema by annotating them with attributes. Provides also validation.

## Validation

**schematics** can validate data according to the annotated schemas. It currently covers 84% of the [official JSON Schema test suite for the 2020-12 draft](https://github.com/json-schema-org/JSON-Schema-Test-Suite).

## Example

```php
enum Sex: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';
}

#[ObjectSchema]
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

        // Inferred $ref to self
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
#[ObjectSchema]
class Hero extends Person
{
    public function __construct(
        string $id,
        array $names,
        int $age,
        string $sex,
        ?Person $father = null,

        // Infers string property
        public string $superName,

        #[StringSchema(enumClass: Power::class)]
        public string $power
    ) {
        parent::__construct($id, $names, $age, $sex, $father);
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
      },
      "required": ["id", "names", "age", "sex", "father"]
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
  },
  "required": ["superName", "power"]
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
