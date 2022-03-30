<?php

declare(strict_types=1);

namespace Giann\Schematics;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "PROPERTY"})
 */
final class SchemaTitle extends SchemaAttribute
{
    public string $title;

    public function __construct(string $title)
    {
        parent::__construct("title", $title);
    }
}
