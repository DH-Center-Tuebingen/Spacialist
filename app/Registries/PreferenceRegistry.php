<?php

namespace App\Registries;

use App\Preferences\PreferenceBase;

class PreferenceRegistry {
    private static array $preferences = [];

    private static function reset(): void {
        self::$preferences = [];
    }

    private static function register(PreferenceBase $type): void {
        $key = $type::getId();
        if(array_key_exists($key, self::$preferences)) {
            throw new \Exception("Attribute type '{$key}' is already registered.");
        }
        self::$preferences[$key] = [
            'class' => $type::class,
        ];
    }

    private static function registerCoreTypes(): void {
        $files = glob(base_path('/app/Preferences') . '/*.php');
        foreach($files as $file) {
            $className = basename($file, '.php');
            $class = "App\\Preferences\\{$className}";

            // Skip all abstract classes and Enums
            $reflection = new \ReflectionClass($class);
            if($reflection->isAbstract()) continue;
            if($reflection->isEnum()) continue;

            self::register(new $class());
        }
    }

    public static function getTypes(bool $serialized = false, bool $userOnly = false): array {
        self::reset();
        self::registerCoreTypes();
        $preferences = self::$preferences;

        if($serialized) {
            return array_values(array_map(function(array $typeDef) {
                return $typeDef['class']::serialized();
            }, $preferences));
        } else {
            return $preferences;
        }
    }
}