<?php

namespace App\Exceptions\Status;

use Exception;


/**
* Base class for exceptions that contain an HTTP status code.
* and can be used to determine the appropriate HTTP response code.
*/
abstract class StatusCodeException extends Exception
{
    abstract public function getStatusCode(): int;
}