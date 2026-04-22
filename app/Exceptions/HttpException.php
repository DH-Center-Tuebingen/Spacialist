<?php

namespace App\Exceptions;

use Exception;

/**
 * Throws an exception that contains the HTTP status code as reason for the exception.
 */
class HttpException extends Exception {
    protected int $statusCode;

    public function __construct(string $message = "", int $statusCode = 500, Exception $previous = null) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }
}