<?php

namespace App\Exceptions\Status;

use Exception;


/**
 * This Exception is thrown when the provided content is syntactically erroneous.
 * For example, when the JSON is not well-formed.
 * It corresponds to HTTP status code 400 Bad Request.
 */
class MalformedContentException extends StatusCodeException
{
    public function getStatusCode(): int
    {
        return 400;
    }
}