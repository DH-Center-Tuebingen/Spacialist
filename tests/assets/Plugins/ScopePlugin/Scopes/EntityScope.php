<?php

namespace App\Plugins\ScopePlugin\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class EntityScope implements Scope {
    public function apply(Builder $builder, Model $model) {
        $builder->where('entity_type_id', 3);
    }
}