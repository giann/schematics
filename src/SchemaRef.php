<?php

declare(strict_types=1);

namespace Giann\Schematics;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "PROPERTY"})
 */
final class SchemaRef extends SchemaAttribute
{
    public string $ref;

    public function __construct(string $ref)
    {
        parent::__construct("$ref", $ref);
    }
}
