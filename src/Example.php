<?php

declare(strict_types=1);

#[Schema(type: SchemaType::Object, description: 'A person')]
class Person
{
    #[StringSchema(description: 'Person id', format: StringFormat::Uuid)]
    public string $id;

    #[ArraySchema(description: 'Array of names', items: new StringSchema())]
    public array $names;

    // Here [integer] could be infered
    #[NumberSchema(description: 'How old is that person', integer: true)]
    public int $age;

    // Here we could infer $ref
    #[Schema(ref: Person::class)]
    public Person $father;
}
