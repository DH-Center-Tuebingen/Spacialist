<?php

namespace App\Providers;

use App\Exceptions\MalformedAccessRuleException;
use App\Group;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    use HandlesAuthorization;

    protected $denyMessage = "You are not allowed to edit this resource. Please ask your administrator to grant access.";

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        //
        // ds = dataset
        // handles read access of objects through groups
        Gate::define('ds-read', function($user, $model, $options = []) {
            return $this->handleDatasetGate($user, $model, $options, 'read');
        });
        // handles write access to **existing** objects through groups
        Gate::define('ds-write', function($user, $model, $options = []) {
            return $this->handleDatasetGate($user, $model, $options, 'write');
        });
        // handles create access to **newly** created objects through groups
        Gate::define('ds-create', function($user, $model, $options = []) {
            return $this->handleDatasetGate($user, $model, $options, 'create');
        });
        // handles delete access of objects through groups
        Gate::define('ds-delete', function($user, $model, $options = []) {
            return $this->handleDatasetGate($user, $model, $options, 'delete');
        });
        // handles export/share access of objects through groups
        Gate::define('ds-export', function($user, $model, $options = []) {
            return $this->handleDatasetGate($user, $model, $options, 'export');
        });
    }

    private function handleDatasetGate($user, $model, array $options, string $gateType) {
        if(!isset($options['permission_fallback'])) {
            throw new MalformedAccessRuleException("No 'permission_fallback' provided in options!");
        }

        if(!isset($model->access_type)) {
            if($user->can($options['permission_fallback'])) {
                return true;
            } else {
                return $this->deny($this->denyMessage);
            }
        }
        $accType = $model->access_type->type;
        // First check for open access (everyone can read)
        if($accType == 'open') {
            if($gateType == 'read') {
                return true;
            } else {
                return $this->deny($this->denyMessage);
            }
        } else if($accType == 'users') {
            if($user->can($options['permission_fallback'])) {
                return true;
            } else {
                return $this->deny($this->denyMessage);
            }
        } else if($accType == 'restricted') {
            if(count($model->access_rules) == 0) {
                throw new MalformedAccessRuleException("No access rules set!");
            }

            $userRule = $model->access_rules()->where('guardable_id', $user->id)
                ->where('guardable_type', get_class($user))
                ->first();
            if(isset($userRule)) {
                $rType = $userRule->rule_type;
                if($rType == 'read') {
                    if($gateType == 'read') {
                        return true;
                    } else {
                        return $this->deny($this->denyMessage);
                    }
                } else if($rType == 'role') {
                    if($user->can($options['permission_fallback'])) {
                        return true;
                    } else {
                        return $this->deny($this->denyMessage);
                    }
                } else if($rType == 'matrix') {
                    // read access is always included in matrix
                    if($gateType == 'read' || $userRule->rule_values[$gateType] === true) {
                        return true;
                    } else {
                        return $this->deny($this->denyMessage);
                    }
                } else {
                    throw new MalformedAccessRuleException("Undefined rule type! $rType");
                }
            } else {
                $userGrps = $user->groups()->pluck('id');
                $userGrpRules = $model
                    ->access_rules()
                    ->whereIn('guardable_id', $userGrps)
                    ->where('guardable_type', Group::class)
                    ->get();

                // do not give access if user groups are not defined in rules
                if(!isset($userGrpRules) || count($userGrpRules) == 0) {
                    return $this->deny($this->denyMessage);
                }
                $readonlyGrps = [];
                $roleGrps = [];
                $matrixGrps = [];
                foreach($userGrpRules as $grpRule) {
                    if($grpRule->rule_type == 'read') {
                        $readonlyGrps[] = $grpRule;
                    } else if($grpRule->rule_type == 'role') {
                        $roleGrps[] = $grpRule;
                    } else if($grpRule->rule_type == 'matrix') {
                        $matrixGrps[] = $grpRule;
                    }
                }
                // if all user groups have only read access, return true for read access
                $hasReadonly = count($readonlyGrps) > 0;
                $hasRoles = count($roleGrps) > 0;
                $hasMatrix = count($matrixGrps) > 0;
                if($hasReadonly && !$hasRoles && !$hasMatrix) {
                    if($gateType == 'read') {
                        return true;
                    } else {
                        return $this->deny($this->denyMessage);
                    }
                }

                if($hasMatrix) {
                    if($gateType == 'read') {
                        return true;
                    }
                    foreach($matrixGrps as $mGrp) {
                        if($mGrp->rule_values[$gateType] === true) {
                            return true;
                        }
                    }
                }
                if($hasRoles) {
                    if($user->can($options['permission_fallback'])) {
                        return true;
                    } else {
                        return $this->deny($this->denyMessage);
                    }
                }

                return $this->deny($this->denyMessage);
            }
        } else {
            throw new MalformedAccessRuleException("Undefined access type! $accType");
        }
    }
}
