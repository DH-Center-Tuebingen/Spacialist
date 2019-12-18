<?php

namespace App\Providers;

use App\AccessRule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
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

        Gate::define('read-object', function($user, $objectModel) {
            $id = $objectModel->id;
            $type = $objectModel->getMorphClass();
            $ug = $user->groups()->pluck('id')->toArray();

            if($type == 'entities') {
                $parents = $objectModel->getParentIdsAttribute();

                // get all rules for the user's groups
                $rulesUserGroups = AccessRule::whereIn('objectable_id', $parents)
                    ->where('objectable_type', $type)
                    ->whereHas('group', function($q) use($ug) {
                        $q->whereIn('id', $ug);
                    })
                    ->whereNotNull('rules')
                    ->pluck('objectable_id');

                // get all rules for the groups the user is not part of
                $rulesOtherGroups = AccessRule::whereIn('objectable_id', $parents)
                    ->where('objectable_type', $type)
                    ->whereDoesntHave('group', function($q) use($ug) {
                        $q->whereIn('id', $ug);
                    })
                    ->whereNotNull('rules')
                    ->pluck('objectable_id');

                // diff those rules to remove rules that allow access to both groups
                // if all rules match both user groups and other groups,
                // access is granted
                return $rulesOtherGroups->diff($rulesUserGroups)->isEmpty();
            } else {
                return AccessRule::where('objectable_id', $id)
                    ->where('objectable_type', $type)
                    ->whereIn('group_id', $ug)
                    ->whereNotNull('rules')
                    ->exists()
                    ||
                    !AccessRule::where('objectable_id', $id)
                    ->where('objectable_type', $type)
                    ->exists();
            }

        });

        Gate::define('modify-object', function($user, $objectModel) {
            $id = $objectModel->id;
            $type = $objectModel->getMorphClass();
            $ug = $user->groups()->pluck('id')->toArray();

            if($type == 'entities') {
                $parents = $objectModel->getParentIdsAttribute();

                // get all rules for the user's groups
                $rulesUserGroups = AccessRule::whereIn('objectable_id', $parents)
                    ->where('objectable_type', $type)
                    ->whereHas('group', function($q) use($ug) {
                        $q->whereIn('id', $ug);
                    })
                    ->where('rules', 'rw')
                    ->pluck('objectable_id');

                // get all rules for the groups the user is not part of
                $rulesOtherGroups = AccessRule::whereIn('objectable_id', $parents)
                    ->where('objectable_type', $type)
                    ->whereDoesntHave('group', function($q) use($ug) {
                        $q->whereIn('id', $ug);
                    })
                    ->where('rules', 'rw')
                    ->pluck('objectable_id');

                // diff those rules to remove rules that allow access to both groups
                // if all rules match both user groups and other groups,
                // access is granted
                return $rulesOtherGroups->diff($rulesUserGroups)->isEmpty();
            } else {
                return AccessRule::where('objectable_id', $id)
                    ->where('objectable_type', $type)
                    ->whereIn('group_id', $ug)
                    ->where('rules', 'rw')
                    ->exists()
                    ||
                !AccessRule::where('objectable_id', $id)
                    ->where('objectable_type', $type)
                    ->exists();
            }
        });
    }
}
