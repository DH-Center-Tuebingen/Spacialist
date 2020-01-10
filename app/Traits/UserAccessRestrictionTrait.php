<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Scope;
use App\AccessRule;

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
            $builder->whereHas('access_rules', function($subQ) use($userGroups, $model) {
                $subQ->whereIn('group_id', $userGroups);
                $subQ->whereNotNull('rules');
            })->orDoesntHave('access_rules');

            /*
              SELECT *
              FROM public.access_rules
              WHERE (objectable_id IN (7,8)
              AND objectable_type = 'entities'
              AND NOT (
            	group_id in (2)
            	  AND not exists (
            		select *
            		from access_rules
            		WHERE objectable_id IN (7,8)
            		AND objectable_type = 'entities'
            		AND group_id in (1)
            	  )
              ));
            */
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

    public function userHasReadAccess($objectModel = null, $asBool = false) {
        if(!isset($objectModel)) {
            $objectModel = $this;
        }
        if($asBool) {
            return Gate::allows('read-object', $objectModel);
        } else {
            return Gate::authorize('read-object', $objectModel);
        }
    }

    public function userHasWriteAccess($objectModel = null, $asBool = false) {
        if(!isset($objectModel)) {
            $objectModel = $this;
        }
        if($asBool) {
            return Gate::allows('modify-object', $objectModel);
        } else {
            return Gate::authorize('modify-object', $objectModel);
        }
    }

    public function getHasWriteAccessAttribute() {
        return $this->userHasWriteAccess($this, true);
    }
}
