<?php

namespace App\Utils;

use Exception;

class StringUtils {

    static function useGuard(string $exceptionClass, string $message = "Provided data is not a string.") : callable {
        return function(mixed $data) use ($exceptionClass, $message) {
            if(!is_string($data)) {
                throw new $exceptionClass($message);
            }

            return trim($data);
        };
    }

    static function guard(mixed $data) : string {
        return self::useGuard(Exception::class)($data);
    }
}