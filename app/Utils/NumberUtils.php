<?php
namespace App\Utils;

class NumberUtils {
    /**
     * Returns a guard function that checks if the provided data is an integer or a string that can be parsed to an integer.
     */
    public static function useStringIntegerGuard(string $exceptionClass, string $message = "Provided data is not an integer.") : callable {
        return function($data) use ($exceptionClass, $message): int {
            $not_an_integer_error = "Data is not an integer: " . $data;

            if(is_int($data)) {
                return $data;
            } else if(is_string($data)) {
                if(!self::is_integer_string($data)) {
                    throw new $exceptionClass($not_an_integer_error);
                }

                if(self::is_integer_too_big($data)) {
                    throw new $exceptionClass("Integer is too big: " . $data);
                }

                return intval($data);
            } else {
                throw new $exceptionClass($not_an_integer_error);
            }
        };
    }

    /**
     * Checks if the provided text is a string of numbers. Can be negative.
     *
     * @param string $text
     * @return bool
     */
    public static function is_integer_string(string $text) : bool {
        return preg_match('/^-?\d+$/', $text);
    }

    /**
     * Checks if the provided text is a string of numbers. Cannot be negative.
     *
     * @param string $text
     * @return bool
     */
    public static function is_unsigned_integer_string(string $text) : bool {
        return preg_match('/^\d+$/', $text);
    }

    /**
     * Checks if the provided integer is too big to be stored in a PHP integer.
     *
     * @param string $intString - The integer as a string
     * @return bool
     */
    public static function is_integer_too_big(string $intString) : bool {
        // Compare with PHP_INT_MAX and PHP_INT_MIN using bccomp for arbitrary precision
        return
            bccomp($intString, (string)PHP_INT_MAX) === 1
            ||
            bccomp($intString, (string)PHP_INT_MIN) === -1;
    }
}