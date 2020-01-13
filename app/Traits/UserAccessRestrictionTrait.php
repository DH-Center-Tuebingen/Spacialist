<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Scope;
use App\AccessRule;
use App\Group;

class AccessRestrictionScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if(auth()->check()) {
            $user = auth()->user();
            $userGroups = $user->groups()->pluck('id')->toArray();
            if($model->getMorphClass() == 'entities') {
                $otherGroups = [];
                foreach(Group::whereNotIn('id', $userGroups)->pluck('id')->toArray() as $g) {
                    $otherGroups[] = "$g:rw";
                    $otherGroups[] = "$g:r";
                }
                $otherGroups = "'".implode("','", $otherGroups)."'";

                $builder->whereRaw("EXISTS (
                	WITH RECURSIVE parent_rules AS (
                		SELECT e.id, e.name, e.root_entity_id, ar.objectable_id, ar.objectable_type, ar.group_id, ar.rules, ARRAY[ar.group_id || ':' || ar.rules]::text[] AS path
                		FROM entities e
                		LEFT JOIN access_rules ar ON e.id = ar.objectable_id AND ar.objectable_type = 'entities'
                		WHERE e.id = entities.id
                		UNION ALL
                		SELECT e2.id, e2.name, e2.root_entity_id, ar2.objectable_id, ar2.objectable_type, ar2.group_id, ar2.rules, path || (ar2.group_id || ':' || ar2.rules)
                		FROM parent_rules pr
                		JOIN entities e2 ON e2.id = pr.root_entity_id
                		LEFT JOIN access_rules ar2 ON e2.id = ar2.objectable_id AND ar2.objectable_type = 'entities'
                	)
                	SELECT *
                	FROM parent_rules
                	WHERE root_entity_id IS NULL
                	AND NOT path && ARRAY[$otherGroups]
                )");
            } else {
                $builder->whereHas('access_rules', function($subQ) use($userGroups) {
                    $subQ->whereIn('group_id', $userGroups);
                    $subQ->whereNotNull('rules');
                })->orDoesntHave('access_rules');
            }
        } else {
            $builder->whereDoesntHave('access_rules');
        }
    }
}

trait UserAccessRestrictionTrait
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AccessRestrictionScope);
    }

    public function initializeUserAccessRestrictionTrait() {
        $this->withCount[] = 'access_rules';
        $this->appends[] = 'hasWriteAccess';
    }

    public function access_rules() {
        return $this->morphMany('App\AccessRule', 'objectable');
    }

    public function userHasReadAccess($objectModel = null, $options = []) {
        if(!isset($objectModel)) {
            $objectModel = $this;
        }
        if(isset($options['as_bool']) && $options['as_bool']) {
            return Gate::allows('read-object', $objectModel, $options);
        } else {
            return Gate::authorize('read-object', $objectModel, $options);
        }
    }

    public function userHasWriteAccess($objectModel = null, $options = []) {
        if(!isset($objectModel)) {
            $objectModel = $this;
        }
        if(isset($options['as_bool']) && $options['as_bool']) {
            return Gate::allows('modify-object', $objectModel, $options);
        } else {
            return Gate::authorize('modify-object', $objectModel, $options);
        }
    }

    public function getHasWriteAccessAttribute() {
        return $this->userHasWriteAccess($this, ['as_bool' => true]);
    }
}
