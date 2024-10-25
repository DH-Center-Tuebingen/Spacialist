<?php

namespace App\Traits;

use App\PluginResources\RelationManager;

trait AllowPluginRelationTrait {
    protected static array $registeredPluginRelations = [];

    public static function registerPluginRelation(string $name, string $class, string $relationType, string $foreignKey, ?string $localKey) : void {
        if(array_key_exists($name, self::$registeredPluginRelations)) {
            throw new \Exception("Relation $name already registered for class " . self::class);
        }

        self::$registeredPluginRelations[$name] = [
            'name' => $name,
            'model' => self::class,
            'type' => $relationType,
            'on' => $class,
            'fk' => $foreignKey,
            'lk' => $localKey,
        ];

        RelationManager::get()->register(self::$registeredPluginRelations[$name]);
    }

    public static function getRegisteredPluginRelations() {
        return self::$registeredPluginRelations;
    }
}
