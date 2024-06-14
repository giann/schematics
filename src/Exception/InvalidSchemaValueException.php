<?php

declare(strict_types=1);

namespace Giann\Schematics\Exception;

use Exception;
use Throwable;

class InvalidSchemaValueException extends Exception
{
    /**
     * @param string $message
     * @param string[] $dataPath
     * @param integer $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message,
        array $dataPath,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = $message . ' at ' . implode("/", $dataPath);

        parent::__construct($message, $code, $previous);
    }
}
