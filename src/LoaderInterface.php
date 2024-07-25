<?php

declare(strict_types=1);

namespace Giann\Schematics;

interface LoaderInterface
{
    /**
     * Load the json schema from the given path.
     *
     * @param string $path The path to load, without the protocol.
     * @return ?array<string,mixed> The loaded schema as associative array
     */
    public function load($path): ?array;
}
