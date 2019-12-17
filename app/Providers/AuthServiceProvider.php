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
            return AccessRule::where('objectable_id', $id)
                ->where('objectable_type', $type)
                ->whereIn(
                    'group_id', $user->groups()->pluck('id')->toArray()
                )
                ->whereNotNull('rules')
                ->exists()
                ||
            !AccessRule::where('objectable_id', $id)
                    ->where('objectable_type', $type)
                    ->exists();
        });

        Gate::define('modify-object', function($user, $objectModel) {
            $id = $objectModel->id;
            $type = $objectModel->getMorphClass();
            return AccessRule::where('objectable_id', $id)
                ->where('objectable_type', $type)
                ->whereIn(
                    'group_id', $user->groups()->pluck('id')->toArray()
                )
                ->where('rules', 'rw')
                ->exists()
                ||
            !AccessRule::where('objectable_id', $id)
                    ->where('objectable_type', $type)
                    ->exists();
        });
    }
}
