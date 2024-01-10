<?php

namespace App\Scopes;

use App\Group;
use App\User;
use DragonCode\Support\Facades\Helpers\Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class RestrictableScope implements Scope
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

            // compute role read access for given user
            if(isset($model->restrictable_permissions)) {
                $readPerms = $model->restrictable_permissions['read'];
                // set role access using can helper
                if(gettype($readPerms) == 'string') {
                    $roleAccess = $user->can($readPerms);
                } else if(gettype($readPerms) == 'array') {
                    $roleAccess = true;
                    foreach($readPerms as $rp) {
                        $roleAccess = $roleAccess && $user->can($rp);
                        if(!$roleAccess) break;
                    }
                } else if(gettype($readPerms) == 'boolean') {
                    $roleAccess = $readPerms;
                } else {
                    $roleAccess = false;
                }
            }
            if(isset($model->restrictable_recursive_key)) {
                $rkey = $model->restrictable_recursive_key;
                $type = get_class($model);
                $tbl = (new $type())->getTable();
                $grpStr = implode(",", $userGroups);
                $grpQry = count($userGroups) > 0 ? "OR (ar.guardable_id IN ($grpStr) AND ar.guardable_type = 'App\Group')" : "";
                $accessibleIds = Arr::flatten(
                    DB::select("
                        SELECT DISTINCT y.id
                        FROM $tbl y
                        LEFT JOIN
                        (
                            SELECT access_types.*, path[1] as ref_type_id
                            FROM access_types, (
                                WITH RECURSIVE parent_rules AS (
                                    SELECT x.id, x.$rkey, ARRAY[x.id, x.$rkey]::integer[] AS path
                                    FROM $tbl x
                                    UNION ALL
                                    SELECT x2.id, x2.$rkey, path || (x2.$rkey)
                                    FROM parent_rules pr
                                    JOIN $tbl x2 ON x2.id = pr.$rkey
                                    WHERE x2.$rkey IS NOT NULL
                                )
                                SELECT *, ARRAY_LENGTH(path, 1) as len
                                FROM parent_rules
                            ) as path_rule
                            WHERE access_types.accessible_type = '$type' AND access_types.accessible_id = ANY(path)
                        ) as at ON y.id = at.ref_type_id
                        LEFT JOIN
                        (
                            SELECT DISTINCT ON (guardable_id, guardable_type, ref_rule_id)
                                access_rules.*, path[1] as ref_rule_id
                            FROM access_rules, (
                                WITH RECURSIVE parent_rules AS (
                                    SELECT x.id, x.$rkey, ARRAY[x.id, x.$rkey]::integer[] AS path
                                    FROM $tbl x
                                    UNION ALL
                                    SELECT x2.id, x2.$rkey, path || (x2.$rkey)
                                    FROM parent_rules pr
                                    JOIN $tbl x2 ON x2.id = pr.$rkey
                                    WHERE x2.$rkey IS NOT NULL
                                )
                                SELECT *, ARRAY_LENGTH(path, 1) as len
                                FROM parent_rules
                            ) as path_rule
                            WHERE access_rules.restrictable_type = '$type' AND access_rules.restrictable_id = ANY(path)
                            ORDER BY guardable_id, guardable_type, ref_rule_id ASC
                        ) as ar ON y.id = ar.ref_rule_id
                        WHERE at.type = 'open' OR at.id IS NULL OR (
                            at.type = 'restricted' AND ar.id IS NOT NULL AND (
                                (
                                    ar.guardable_id = $user->id AND ar.guardable_type = 'App\User'
                                ) $grpQry
                            )
                        )
                    ")
                );
                $builder->whereIn('id',$accessibleIds);
            } else {
                // access_type = 'users' is ignored here because access using roles (default) should be already handled by the system
                // also access rules with type 'role' are ignored, because access using roles should already be handled, for cases where working groups are not used
                $builder->whereRelation('access_type', 'type', 'open')
                    ->orWhere(function($mtrxQ) use($user, $userGroups) {
                        // user or user group access_rule is enough to check for read access
                        $mtrxQ->whereRelation('access_type', 'type', 'restricted')
                            ->whereHas('access_rules', function($ruleQ) use($user, $userGroups) {
                                $ruleQ->where(function($userRuleQ) use($user) {
                                    $userRuleQ->where('guardable_id', $user->id)
                                        ->where('guardable_type', User::class);
                                })->orWhere(function($userGrpRuleQ) use($userGroups) {
                                    $userGrpRuleQ->whereIn('guardable_id', $userGroups)
                                        ->where('guardable_type', Group::class);
                                });
                            });
                    })
                    ->orDoesntHave('access_rules');
            }
        } else {
            $builder->whereMorphRelation('access_type', 'type', 'open')
                ->orWhereDoesntHave('access_rules');
        }
    }
}