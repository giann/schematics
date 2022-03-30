<?php

declare(strict_types=1);

namespace Giann\Schematics;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "PROPERTY"})
 */
abstract class SchemaAttribute
{
    public string $key;

    public $value;

    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
}
