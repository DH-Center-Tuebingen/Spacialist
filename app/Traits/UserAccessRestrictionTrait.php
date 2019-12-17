<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

trait UserAccessRestrictionTrait
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('restricted-access', function(Builder $builder) {
            if(auth()->check()) {
                $user = auth()->user();
                $builder->whereHas('access_rules', function($subQ) use($user) {
                    $subQ->whereIn('group_id', $user->groups()->pluck('id')->toArray());
                    $subQ->whereNotNull('rules');
                })->orDoesntHave('access_rules');
            } else {
                $builder->whereDoesntHave('access_rules');
            }
        });
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
