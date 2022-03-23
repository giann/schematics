# schematics

📏 Models that can be translated to JSON Schemas using attributes or docblock annotations

## Example

```php
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
        }
      }
    }
  },
  "properties": {
    "superName": {
      "type": "string"
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
