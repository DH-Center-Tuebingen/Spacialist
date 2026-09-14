<?php

namespace Tests\Support;

use Exception;


class RoleExtension {

    const ALLOWED_PERMISSIONS = [
        'c' => "create", 
        'r' => "read", 
        'w' => "write", 
        'd' => "delete", 
        's' => "share"
    ];

    public function __construct(public string $extends, public string $name, public string $permissions) {}

    public function toArray(): array {
        return [
            'extends' => $this->extends,
            'rule_set' => $this->mapPermissions(),
        ];
    }

    public function mapPermissions(): array {
        $permChars = str_split($this->permissions);
        $permissionArray = [];
        foreach($permChars as $char) {
            if(!in_array($char, array_keys(self::ALLOWED_PERMISSIONS))) {
                throw new Exception("Invalid permission character: $char. Allowed characters are: " . implode(', ', array_keys(self::ALLOWED_PERMISSIONS)));
            }
            $permissionArray[] = $this->name . "_" . self::ALLOWED_PERMISSIONS[$char];
        }
        return $permissionArray;
    }
}