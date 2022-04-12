<?php

declare(strict_types=1);

namespace Giann\Schematics;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "PROPERTY"})
 */
final class SchemaDescription extends SchemaAttribute
{
    public function __construct(string $description)
    {
        parent::__construct("description", $description);
    }
}
