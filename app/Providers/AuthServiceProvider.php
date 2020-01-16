<?php

namespace App\Providers;

use App\AccessRule;
use App\Entity;
use App\Group;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuthServiceProvider extends ServiceProvider
{
    use HandlesAuthorization;
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('read-object', function($user, $objectModel, $options = []) {
            $custom = isset($objectModel->custom);
            $id = $objectModel->id;
            $type = $custom ? $objectModel->type : $objectModel->getMorphClass();
            $grps = isset($options['groups']) ? $options['groups'] : $user->groups()->pluck('id')->toArray();


            if($type == 'entities') {
                $otherGroups = [];
                foreach(Group::whereNotIn('id', $grps)->pluck('id')->toArray() as $g) {
                    $otherGroups[] = "$g:rw";
                    $otherGroups[] = "$g:r";
                }
                $otherGroups = "'" . implode("','", $otherGroups) . "'";

                $access = Entity::selectRaw("EXISTS (
                	WITH RECURSIVE parent_rules AS (
                		SELECT e.id, e.name, e.root_entity_id, ar.objectable_id, ar.objectable_type, ar.group_id, ar.rules, ARRAY[ar.group_id || ':' || ar.rules]::text[] AS path
                		FROM entities e
                		LEFT JOIN access_rules ar ON e.id = ar.objectable_id AND ar.objectable_type = 'entities'
                		WHERE e.id = ?
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
                ) as has_access", [$objectModel->id])->first();
                $granted = $access->has_access;
            } else {
                $granted = AccessRule::where('objectable_id', $id)
                    ->where('objectable_type', $type)
                    ->whereIn('group_id', $grps)
                    ->whereNotNull('rules')
                    ->exists()
                    ||
                    !AccessRule::where('objectable_id', $id)
                    ->where('objectable_type', $type)
                    ->exists();
            }

            if($granted) {
                return true;
            } else {
                return $this->deny('You are not allowed to view this resource. Please ask your administrator to grant access.');
            }

        });

        Gate::define('modify-object', function($user, $objectModel, $options = []) {
            $custom = isset($objectModel->custom);
            $id = $objectModel->id;
            $type = $custom ? $objectModel->type : $objectModel->getMorphClass();
            $grps = isset($options['groups']) ? $options['groups'] : $user->groups()->pluck('id')->toArray();

            if($type == 'entities') {
                $otherGroups = [];
                foreach(Group::whereNotIn('id', $grps)->pluck('id')->toArray() as $g) {
                    $otherGroups[] = "$g:rw";
                    $otherGroups[] = "$g:r";
                }
                $otherGroups = "'" . implode("','", $otherGroups) . "'";

                $access = Entity::selectRaw("EXISTS (
                	WITH RECURSIVE parent_rules AS (
                		SELECT e.id, e.name, e.root_entity_id, ar.objectable_id, ar.objectable_type, ar.group_id, ar.rules, ARRAY[ar.group_id || ':' || ar.rules]::text[] AS path
                		FROM entities e
                		LEFT JOIN access_rules ar ON e.id = ar.objectable_id AND ar.objectable_type = 'entities'
                		WHERE e.id = ?
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
                	AND (array_remove(path, null) = ARRAY[]::text[] OR (array_remove(path, null))[1] LIKE '_:rw')
                ) as has_access", [$objectModel->id])->first();
                $granted = $access->has_access;
            } else {
                $granted = AccessRule::where('objectable_id', $id)
                    ->where('objectable_type', $type)
                    ->whereIn('group_id', $grps)
                    ->where('rules', 'rw')
                    ->exists()
                    ||
                !AccessRule::where('objectable_id', $id)
                    ->where('objectable_type', $type)
                    ->exists();
            }

            if($granted) {
                return true;
            } else {
                return $this->deny('You are not allowed to edit this resource. Please ask your administrator to grant access.');
            }
        });
    }
}
