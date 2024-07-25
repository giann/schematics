<?php

declare(strict_types=1);

namespace Giann\Schematics\Draft04;

use Giann\Schematics\Exception\SchemaCantBeEntityException;
use Giann\Schematics\Exception\SchemaLoadingException;
use Giann\Schematics\GeneratorHelper;
use Giann\Schematics\LoaderInterface;
use Giann\Schematics\NotRequired;
use Giann\Trunk\Trunk;
use PhpParser\Builder\Class_;
use PhpParser\Builder\Declaration;
use PhpParser\Builder\Namespace_;
use PhpParser\Builder\Property;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\UnionType;

final class EntityDeclaration
{
    public function __construct(
        public string $name,
        public Declaration $declaration,
        public string $ref,
    ) {
    }
}

class EntityGenerator
{
    private Generator $generator;
    private GeneratorHelper $helper;
    /** @var array<string,EntityDeclaration> */
    private array $registry = [];
    private string $currentEntityPath = '#';
    private string $currentEntityName = '';

    /**
     * @param LoaderInterface[] $loaders
     * @param string|null $namespace
     */
    public function __construct(
        private Trunk $rootSchema,
        private array $loaders = [],
        public ?string $namespace = null,
    ) {
        $this->generator = new Generator;
        $this->helper = new GeneratorHelper;
    }

    private function getRefEntity(string $ref): string
    {
        if (isset($this->registry[$ref])) {
            return $this->registry[$ref]->name;
        }

        // If ref in current schema, search in provided path
        if (str_starts_with($ref, '#/')) {
            // Is it a self reference ?
            if ($ref === $this->currentEntityPath) {
                return $this->currentEntityName;
            }

            $refSchema = $this->helper->getAt($this->rootSchema, $ref);
            if ($refSchema->map() !== null) {
                $explodedRef = explode('/', $ref);

                return $this->addEntityDeclaration(
                    $this->generateEntity(
                        $refSchema,
                        $this->helper->anyToCamelCase(end($explodedRef)),
                        $ref,
                    )
                )->name;
            }

            throw new SchemaLoadingException('Could not resolve ref ' . $ref);
        }

        // Use loader
        $name = $this->getRefName($ref);
        return $this->addEntityDeclaration(
            $this->generateEntity(
                $this->loadEntity($ref),
                $name,
                $ref,
            )
        )->name;
    }

    private function loadEntity(string $ref): Trunk
    {
        foreach ($this->loaders as $loader) {
            if (($loaded = $loader->load($ref))) {
                return new Trunk($loaded);
            }
        }

        throw new SchemaLoadingException('Could not load ref ' . $ref);
    }

    private function addEntityDeclaration(EntityDeclaration $declaration): EntityDeclaration
    {
        if (isset($this->registry[$declaration->ref])) {
            return $this->registry[$declaration->ref];
        }

        $this->registry[$declaration->ref] = $declaration;

        return  $declaration;
    }

    private function schemaIsSimpleRef(Trunk $schema): bool
    {
        return isset($schema['$ref'])
            && ($keys = array_keys($schema->mapValue()))
            && count(
                array_filter(
                    $keys,
                    fn ($key) => $key !== '$schema' && $key !== '$ref'
                )
            ) === 0;
    }

    /**
     * @param string $ref
     * @throws SchemaCantBeEntityException
     * @return string
     */
    private function getRefName(string $ref): string
    {
        $lastFragment = strrpos($ref, '\\');
        $name = $this->namespace . '\\' . substr(
            $ref,
            $lastFragment !== false
                ? $lastFragment + 1
                : 0
        );

        if (!isset($this->registry[$name])) {
            return $name;
        }

        throw new SchemaCantBeEntityException('An entity named ' . $name . ' already exists');
    }

    /**
     * Dismisses schema if can't be resolved to an php entity
     * @param Trunk $schema
     * @param boolean $throw
     * @return boolean
     */
    private function canBeEntity(Trunk $schema, bool $throw = true): bool
    {
        if (
            (!isset($schema['$ref']) && !isset($schema['type']))
            || $schema['type']->string() !== 'object'
        ) {
            if ($throw) {
                throw new SchemaCantBeEntityException('Schema is not of type object and is not a $ref');
            }

            return false;
        }

        foreach ([
            'patternProperties',
            'unevaluatedProperties',
            'propertyNames',
            'minProperties',
            'maxProperties',
            'dependentRequired',
            'anyOf',
            'oneOf',
            'not',
            'if',
            'then',
            'else',
        ] as $keyword) {
            if (isset($schema[$keyword])) {
                if ($throw) {
                    throw new SchemaCantBeEntityException('Schema has ' . $keyword);
                }

                return false;
            }
        }

        if ($schema['additionalProperties']->map() !== null) {
            if ($throw) {
                throw new SchemaCantBeEntityException('Schema has additionalProperties schema');
            }

            return false;
        }

        if (count($schema['properties']->arrayValue()) === 0 && !isset($schema['$ref'])) {
            if ($throw) {
                throw new SchemaCantBeEntityException('Schema does not define any properties');
            }

            return false;
        }

        return true;
    }

    /**
     * @param string $name
     * @return Node[]
     */
    public function generateEntities(string $name): array
    {
        $this->registry = [];

        $this->generateEntity($this->rootSchema, $name, '#');

        return array_merge(
            ($this->namespace !== null ? [(new Namespace_($this->namespace))->getNode()] : []),
            array_map(
                fn ($decl) => $decl->declaration->getNode(),
                array_values($this->registry)
            )
        );
    }

    /**
     * Generate entity from a json schema. Assumes the provided schema is valid.
     * @param Trunk $rawSchema
     * @return EntityDeclaration Class declaration
     * @throws SchemaCantBeEntityException
     */
    private function generateEntity(Trunk $rawSchema, string $name, string $path): EntityDeclaration
    {
        $this->canBeEntity($rawSchema);

        $previousEntityPath = $this->currentEntityPath;
        $previousEntityName = $this->currentEntityName;
        $this->currentEntityPath = $path;
        $this->currentEntityName = $name;

        $class = (new Class_($name));

        // $ref: can be inheritance or simple ref
        if (($ref = $rawSchema['$ref']->string())) {
            $entityName = $this->getRefEntity($ref);

            // Simple ref, generate that and return it
            // Any other keyword than $schema and $ref is considered reason enough to generate a new class
            // TODO: maybe set an option to ignore intermediate entities with no additional properties
            if (
                ($keys = array_keys($rawSchema->mapValue()))
                && count(
                    array_filter(
                        $keys,
                        fn ($key) => $key !== '$schema' && $key !== '$ref'
                    )
                ) > 0
            ) {
                return $this->registry[$ref];
            }

            // Otherwise extend it
            $class = $class->extend(new Name($entityName));
        }

        // Object properties
        foreach ($rawSchema['properties']->mapValue() as $property => $schema) {
            $class = $this->addProperty(
                $class,
                $property,
                $schema,
                in_array($property, $rawSchema['required']->listRawValue()),
                $path
            );
        }

        $attribute = $this->getAttribute($rawSchema, entity: true);
        assert($attribute !== null);

        // If php 8.2, we can mark the class as readonly
        if (PHP_VERSION_ID > 80200 && $rawSchema['readOnly']->boolValue()) {
            $class = $class->makeReadonly();
        }

        // We tolerate only $ref schemas
        // Any new properties defined here, should be part of the parent schema as they are no reason to not do otherwise
        foreach ($rawSchema['allOf']->listValue() as $schema) {
            if (
                ($ref = $schema['$ref']->string())
                && ($keys = array_keys($schema->mapValue()))
                && count(
                    array_filter(
                        $keys,
                        fn ($key) => $key !== '$schema' && $key !== '$ref'
                    )
                ) === 0
            ) {
                $name = $this->getRefName($ref);
                $entityDeclarationName = $this->getRefEntity($ref);

                $class = $class->extend(new Name($entityDeclarationName));
            } else {
                throw new SchemaCantBeEntityException(
                    '`allOf` contains more than simple $ref schemas: move those properties to the object schema itself.'
                );
            }
        }

        $this->currentEntityPath = $previousEntityPath;
        $this->currentEntityName = $previousEntityName;

        return $this->addEntityDeclaration(
            new EntityDeclaration(
                name: $name,
                declaration: $class->addAttribute($attribute),
                ref: $path,
            )
        );
    }

    private function addProperty(Class_ $class, string $name, Trunk $schema, bool $required, string $path): Class_
    {
        $property = (new Property($name))
            ->setType($this->getType($name, $schema, $path))
            ->makePublic();

        if ($schema['readOnly']->boolValue()) {
            $property = $property->makeReadonly();
        }

        // TODO: a array type could have objects in it
        // We don't generate default value for non-scalar type
        if (isset($schema['default']) && $schema['type']->string() !== 'object') {
            $property = $property->setDefault(
                $this->helper->phpValueToExpr($schema['default']->data)
            );
        }

        if (($attribute = $this->getAttribute($schema))) {
            $property = $property->addAttribute($attribute);
        }

        if (!$required) {
            $property->addAttribute(
                new Attribute(
                    new Name('\\' . NotRequired::class)
                )
            );
        }

        return $class->addStmt($property);
    }

    /**
     * @param string $name
     * @param Trunk $schema
     * @return Name|Identifier|ComplexType
     */
    private function getType(string $name, Trunk $schema, string $path): Name|Identifier|ComplexType
    {
        $types = $schema['type']->list() ?? (isset($schema['type'])
            ? [$schema['type']]
            : []);

        if (count($types) > 1) { // More than one type -> union type
            $union = [];
            foreach ($types as $type) {
                $resolvedType = $this->getScalarType($type->stringValue());

                if ($resolvedType instanceof UnionType) {
                    $union = array_merge($union, $resolvedType->types);
                } else {
                    assert(!($resolvedType instanceof ComplexType));
                    $union[] = $resolvedType;
                }
            }

            return new UnionType($union);
        } elseif (count($types) == 1) { // One type
            // If object do we need to generate a new class?
            if ($types[0]->stringValue() === 'object' && $this->canBeEntity($schema, throw: false)) {
                return new Name(
                    $this->generateEntity(
                        $schema,
                        $this->helper->anyToCamelCase($name),
                        $path . '/' . $name,
                    )->name,
                );
            }

            return $this->getScalarType($types[0]->stringValue());
        } elseif ($this->schemaIsSimpleRef($schema)) { // Simple ref
            return new Name(
                $this->getRefEntity($schema['$ref']->stringValue())
            );
        } elseif (isset($schema['$ref'])) { // Complex ref
            return new Name(
                $this->generateEntity(
                    $schema,
                    $this->helper->anyToCamelCase($name),
                    $path . '/' . $name,
                )->name
            );
        } elseif (($oneOf = $schema['oneOf']->list())) { // Union type
            $union = [];
            foreach ($oneOf as $subSchema) {
                $resolvedType = $this->getType($name, $subSchema, $path . '/' . $name . '/oneOf');

                if ($resolvedType instanceof UnionType) {
                    $union = array_merge($union, $resolvedType->types);
                } else {
                    assert(!($resolvedType instanceof ComplexType));
                    $union[] = $resolvedType;
                }
            }

            return new UnionType($union);
        }

        // None of the above, can be anything
        return new Identifier('mixed');
    }

    private function getScalarType(string $type): Identifier|ComplexType
    {
        switch ($type) {
            case 'string':
                return new Identifier('string');
            case 'boolean':
                return new Identifier("bool");
            case 'number':
                return new UnionType([
                    new Identifier("float"),
                    new Identifier("int"),
                ]);
            case 'array':
            case 'object': // At this point its most likely a map and not an object in the php sense
                return new Identifier('array');
            case 'null':
                return new Identifier('null');
            default:
                return new Identifier('mixed');
        }
    }

    private function getAttribute(Trunk $schema, bool $entity = false): ?Attribute
    {
        $attributeArgs = [];
        $name = Schema::class;
        switch ($schema['type']->string()) {
            case 'string':
                $name = StringSchema::class;

                $empty = [];
                $this->generator->buildStringKeywords(
                    rawSchema: $schema,
                    path: '#',
                    parameters: $attributeArgs,
                    keywords: $empty
                );

                break;
            case 'number':
            case 'integer':
                $name = NumberSchema::class;

                $empty = [];
                $this->generator->buildNumberKeywords(
                    rawSchema: $schema,
                    path: '#',
                    parameters: $attributeArgs,
                    keywords: $empty
                );

                break;
            case 'object':
                $name = ObjectSchema::class;

                if (!$entity && !$this->canBeEntity($schema)) {
                    $empty = [];
                    $this->generator->buildObjectKeywords(
                        rawSchema: $schema,
                        path: '#',
                        parameters: $attributeArgs,
                        keywords: $empty
                    );
                }

                break;
            case 'array':
                $name = ArraySchema::class;

                $empty = [];
                $this->generator->buildArrayKeywords(
                    rawSchema: $schema,
                    path: '#',
                    parameters: $attributeArgs,
                    keywords: $empty
                );

                break;
            case 'boolean':
                $name = BooleanSchema::class;
                break;
            case 'null':
                $name = NullSchema::class;
                break;
        }

        // Common keywords
        // Handled without calling buildCommonKeywords because we do some things differently when it comes to possible entities

        // If no other keyword are used, we don't need to annotate the property
        if (
            !$entity
            && ($keys = array_keys($schema->mapValue()))
            && count(
                array_filter(
                    $keys,
                    fn ($key) => $key !== '$schema' && $key !== 'type'
                )
            ) === 0
        ) {
            return null;
        }

        // If $ref with type object, it'll be handled with a class name
        if (
            !$entity
            && !$this->canBeEntity($schema, throw: false)
            && ($ref = $schema['$ref']->string())
        ) {
            $attributeArgs[] = new Arg(
                name: new Identifier('ref'),
                value: new String_($ref),
            );
        }

        foreach (['id', 'title', 'description'] as $keyword) {
            if (isset($schema[$keyword])) {
                $attributeArgs[] = new Arg(
                    name: new Identifier(preg_replace('/\\$/', '', $keyword) ?? $keyword),
                    value: new String_($schema[$keyword]->stringValue())
                );
            }
        }

        // TODO: enumPattern
        foreach (['examples', 'enum'] as $keyword) {
            if (($values = $schema[$keyword]->list())) {
                $attributeArgs[] = new Arg(
                    name: new Identifier($keyword),
                    value: new Array_(
                        array_map(
                            fn (Trunk $el) => new ArrayItem(
                                $this->helper->phpValueToExpr($el->data)
                            ),
                            $values
                        )
                    )
                );
            }
        }

        foreach (['default'] as $keyword) {
            if (($value = $schema[$keyword] ?? null)) {
                $attributeArgs[] = new Arg(
                    name: new Identifier($keyword),
                    value: $this->helper->phpValueToExpr($value->data)
                );
            }
        }

        foreach (['deprecated', 'readOnly', 'writeOnly'] as $keyword) {
            if (isset($schema[$keyword])) {
                $attributeArgs[] = new Arg(
                    name: new Identifier($keyword),
                    value: $this->helper->boolExpr($schema[$keyword]->boolValue())
                );
            }
        }

        // We only keep definitions of non-object or object that can't be entities (associative arrays)
        $defs = [];
        foreach ($schema['definitions']->mapValue() as $defName => $defSchema) {
            if (!$this->canBeEntity($defSchema, throw: false)) {
                $defs[] = new ArrayItem(
                    key: new String_($defName),
                    value: new ArrayItem(
                        // TODO: This does not work if a def is more deeply referencing an entity that should be generated
                        $this->generator->generateSchema($defSchema)
                    )
                );
            }
        }

        if (!empty($defs)) {
            $attributeArgs[] = new Arg(
                name: new Identifier('definitions'),
                value: new Array_($defs),
            );
        }

        if (!$entity && $name !== ObjectSchema::class) {
            foreach (['allOf', 'oneOf', 'anyOf'] as $keyword) {
                if (($subSchemas = $schema[$keyword]->list())) {
                    $attributeArgs[] = new Arg(
                        name: new Identifier($keyword),
                        value: new Array_(
                            array_map(
                                fn ($subSchema) => new ArrayItem(
                                    // TODO: This does not work if a def is more deeply referencing an entity that should be generated
                                    $this->generator->generateSchema($subSchema)
                                ),
                                $subSchemas
                            )
                        ),
                    );
                }
            }
        }

        return new Attribute(
            // Prefix with '\' so the name is not resolved in the current namespace
            name: new Name('\\' . $name),
            args: $attributeArgs
        );
    }
}
