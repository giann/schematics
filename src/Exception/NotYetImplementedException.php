<?php

declare(strict_types=1);

namespace Giann\Schematics\Exception;

use BadMethodCallException;
use Throwable;

class NotYetImplementedException extends BadMethodCallException
{
    /**
     * @param string $message
     * @param string[] $path
     * @param integer $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message,
        array $path,
        int $code = 0,
        ?Throwable $previous = null
    )
    {
        $message = $message . ' at ' . implode("/", $path);

        parent::__construct($message, $code, $previous);
    }
}
