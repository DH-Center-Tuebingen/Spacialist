<?php

namespace App\PluginResources;

use App\Patterns\Singleton;
use Illuminate\Database\Eloquent\Model;

class RelationManager extends Singleton {

    private array $relations = [];

    // Important: This must be redifined, otherwise PHP will use the 'last' child class that was loaded.
    protected static $instance = null;

    public function register(array $relationConfig): void {
        $key = $relationConfig['model'] . "::" . $relationConfig['name'];
        $this->relations[$key] = $relationConfig;
    }

    public function apply(): void {
        foreach($this->relations as $relation) {
            $relation['model']::resolveRelationUsing($relation['name'], function(Model $model) use($relation) {
                return $model->{$relation['type']}($relation['on'], $relation['fk'], $relation['lk']);
            });
        }
    }
}