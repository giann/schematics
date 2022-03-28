# schematics

📏 Models that can be translated to JSON Schemas using attributes or docblock annotations

## Example

```php
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
```

Results in the following JSON Schema:

```json
{
  "type": "object",
  "definitions": {
    "Person": {
      "type": "object",
      "properties": {
        "id": {
          "type": "string",
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
        "father": {
          "$ref": "#/definitions/Person"
        },
        "sex": {
          "type": "string",
          "enum": ["male", "female", "other"]
        }
      }
    }
  },
  "properties": {
    "superName": {
      "type": "string"
    },
    "power": {
      "type": "string",
      "enum": ["weeeee!", "smash!", "hummmm!"]
    }
  },
  "additionalProperties": false,
  "allOf": [
    {
      "$ref": "#/definitions/Person"
    }
  ]
}
```
