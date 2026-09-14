<?php

namespace App\Interfaces;

interface ValidateThrow {
    
/**
 * Validate input data and throws an exception returning the 
 * error message if the validation fails.
 * @param array $data - The data to validate.
 * @throws \Exception if validation fails with a message describing the error.
 * @return any - The validated data, potentially transformed (e.g. trimmed) by the validation process. 
*/
    public function validate(array $data);
}