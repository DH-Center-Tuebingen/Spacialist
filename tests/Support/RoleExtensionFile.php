<?php

namespace Tests\Support;


class RoleExtensionFile {
    public function __construct(public readonly string $src, public readonly array $roleExtensions = []) {}

    public function toJson(): string {
        $arr = [];
        foreach($this->roleExtensions as $ext) {
            $arr[] = $ext->toArray();
        }

        return json_encode($arr, JSON_PRETTY_PRINT);
    }
}