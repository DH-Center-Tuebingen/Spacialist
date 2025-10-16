<?php

namespace App\Exceptions\Status;

/**
 * This Exception is thrown when the provided content is syntactically correct but semantically erroneous.
 * For example, when trying to set an attribute value that does not conform to the attribute's type.
 * It corresponds to HTTP status code 422 Unprocessable Entity.
 */
class UnprocessableContentException extends StatusCodeException
{
    public function getStatusCode(): int
    {
        return 422;
    }
}