<?php

namespace App\Support;

use Illuminate\Support\Str;

class Method {

    public function __construct(public ?string $class, public ?string $method) {
    }

    public function isValid(): bool {
        return !empty($this->class) && !empty($this->method);
    }

    /**
     * Expands the given string by a base namespace.
     * 
     * @return string
     */
    public function expandNamespace($baseNamespace): string {
        if(empty($this->class)) {
            return Str::chopEnd($baseNamespace, '\\');
        }

        $baseNamespace = Str::finish($baseNamespace, '\\');
        $class = Str::chopStart($this->class, '\\');
        return $baseNamespace . $class;
    }

    /**
     * Checks if the current method does exist. 
     * 
     * @param {string} $baseNamespace - Optional base bath to the class namespace.
     */
    public function exists(string $baseNamespace = null): bool {
        if(!$this->isValid()) {
            return false;
        }

        $fullClass = $this->expandNamespace($baseNamespace);
        return class_exists($fullClass) && method_exists($fullClass, $this->method);
    }

    public function __toString(): string {
        return $this->class . "@" . $this->method;
    }

    /**
     * Parses a method string in the format "class@method" and returns an instance of the Method class.
     */
    public static function parseFromString(string $methodString): static {
        $methodString = trim($methodString);

        $class = null;
        $method = null;

        if(!empty($methodString)) {
            $parts = explode('@', $methodString);
            if(count($parts) == 2) {
                $class = $parts[0];
                $method = $parts[1];
            }
        }

        return new static($class, $method);
    }
}