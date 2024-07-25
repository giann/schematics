<?php

declare(strict_types=1);

namespace Giann\Schematics\Exception;

final class SchemaLoadingException extends \RuntimeException
{
    public static function create(string $path): self
    {
        return new static(sprintf('The schema "%s" could not be loaded.', $path));
    }

    public static function notFound(string $path): self
    {
        return new static(sprintf('The schema "%s" could not be found.', $path));
    }
}
