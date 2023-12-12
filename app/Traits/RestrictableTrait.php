<?php

namespace App\Traits;

use App\AccessRule;
use App\AccessType;
use App\Scopes\RestrictableScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

trait RestrictableTrait
{
    protected static function boot() {
        parent::boot();

        static::addGlobalScope(new RestrictableScope);
    }

    public function initializeRestrictableTrait() {
        $this->with[] = 'access_type';
        $this->with[] = 'access_rules';
        $this->appends[] = 'user_access';
    }

    private function getUserAccessMatrix() {
        $rkey = $this->restrictable_recursive_key;
        $tbl = $this->getTable();
        $type = get_class($this);
        $auth = auth()->check();
        $user = $auth ? auth()->user() : null;
        $accessTypes = DB::select("
            SELECT at.*
            FROM $tbl y
            JOIN
            (
                SELECT access_types.*, path[1] as ref_type_id, array_position(path, accessible_id) as prio
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
                WHERE access_types.accessible_type = '$type' AND access_types.accessible_id = ANY(path) AND path[1] = $this->id
                ORDER BY prio ASC
                LIMIT 1
            ) as at ON y.id = at.ref_type_id
        ");
        $result = [
            'read' => false,
            'write' => false,
            'create' => false,
            'delete' => false,
            'share' => false,
        ];
        if(
            (!isset($accessTypes) || count($accessTypes) == 0)
            ||
            $accessTypes[0]->type == 'open'
        ) {
            foreach($this->restrictable_permissions as $pType => $perms) {
                $result[$pType] = $auth && $user->can($perms);
            }
            return $result;
        }

        // access type is 'restricted' and user authenticated
        $userGroups = $user->groups()->pluck('id')->toArray();
        $grpStr = implode(",", $userGroups);
        $grpQry = count($userGroups) > 0 ? "OR (ar.guardable_id IN ($grpStr) AND ar.guardable_type = 'App\Group')" : "";

        $rules = DB::select("
            SELECT ar.*
            FROM $tbl y
            JOIN
            (
                SELECT access_types.*, path[1] as ref_type_id, array_position(path, accessible_id) as prio
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
                WHERE access_types.accessible_type = '$type' AND access_types.accessible_id = ANY(path) AND path[1] = $this->id
                ORDER BY prio ASC
                LIMIT 1
            ) as at ON y.id = at.ref_type_id
            JOIN
            (
                SELECT DISTINCT ON (guardable_id, guardable_type, ref_rule_id)
                    access_rules.*, path[1] as ref_rule_id, array_position(path, restrictable_id) as prio
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
                WHERE access_rules.restrictable_type = '$type' AND access_rules.restrictable_id = ANY(path) AND path[1] = $this->id
                ORDER BY guardable_id, guardable_type, ref_rule_id, prio ASC
            ) as ar ON y.id = ar.ref_rule_id
            WHERE y.id = $this->id AND (
                at.type = 'open' OR at.id IS NULL OR (
                    at.type = 'restricted' AND ar.id IS NOT NULL AND (
                        (
                            ar.guardable_id = $user->id AND ar.guardable_type = 'App\User'
                        ) $grpQry
                    )
                )
            )
        ");

        // sort rules for simpler checks in foreach
        $sortedRules = Arr::sort($rules, function(object $value) {
            $weight = 0;
            if($value->guardable_type == 'App\\User') {
                $weight = -5;
            }
            if($value->rule_type == 'read') {
                $weight += -1;
            } else if($value->rule_type == 'matrix') {
                $weight += 0;
            } else if($value->rule_type == 'role') {
                $weight += 1;
            }

            return $weight;
        });

        foreach($sortedRules as $rule) {
            if($rule->guardable_type == 'App\\User') {
                if($rule->rule_type == 'read') {
                    $result['read'] = true;
                    $result['write'] = false;
                    $result['create'] = false;
                    $result['delete'] = false;
                    $result['share'] = false;
                } else if($rule->rule_type == 'matrix') {
                    // read is always allowed in matrix
                    $result['read'] = true;
                    // for all other values we check if it is allowed in any of the matrix rules
                    $values = json_decode($rule->rule_values, true);
                    foreach($values as $key => $ruleValue) {
                        $result[$key] = $ruleValue;
                    }
                } else {
                    foreach($this->restrictable_permissions as $pType => $perms) {
                        $result[$pType] = $user->can($perms);
                    }
                }
                // if user rule exists, instantly return because user rules overwrite group rules
                return $result;
                // otherwise loop over all rules to get an OR combination of all group rules
            } else {
                if($rule->rule_type == 'read') {
                    $result['read'] = true;
                    // other access types are not part of read rule
                } else if($rule->rule_type == 'matrix') {
                    // read is always allowed in matrix
                    $result['read'] = true;
                    // for all other values we check if it is allowed in any of the matrix rules
                    $values = json_decode($rule->rule_values, true);
                    foreach($values as $key => $ruleValue) {
                        $result[$key] = $result[$key] || $ruleValue;
                    }
                } else {
                    foreach($this->restrictable_permissions as $pType => $perms) {
                        $result[$pType] = $result[$pType] || $user->can($perms);
                    }
                }
            }
        }
        return $result;
    }

    private function userHasAccess(string $type, bool $check_only) : mixed {
        $options = [
            'permission_fallback' => $this->restrictable_permissions[$type],
        ];
        if($check_only) {
            return Gate::allows("ds-$type", [$this, $options]);
        } else {
            return Gate::authorize("ds-$type", [$this, $options]);
        }
    }

    public function userHasReadAccess(bool $check_only = false) {
        return $this->userHasAccess('read', $check_only);
    }

    public function userHasWriteAccess(bool $check_only = false) {
        return $this->userHasAccess('write', $check_only);
    }

    public function userHasCreateAccess(bool $check_only = false) {
        return $this->userHasAccess('create', $check_only);
    }

    public function userHasDeleteAccess(bool $check_only = false) {
        return $this->userHasAccess('delete', $check_only);
    }

    public function userHasShareAccess(bool $check_only = false) {
        return $this->userHasAccess('share', $check_only);
    }

    public function access_type() {
        return $this->morphOne(AccessType::class, 'accessible');
    }

    public function access_rules() {
        return $this->morphMany(AccessRule::class, 'restrictable')->orderBy('id');
    }

    public function getUserAccessAttribute() {
        if(isset($this->restrictable_recursive_key)) {
            return $this->getUserAccessMatrix();
        } else {
            return [
                'read' => $this->userHasReadAccess(true),
                'write' => $this->userHasWriteAccess(true),
                'create' => $this->userHasCreateAccess(true),
                'delete' => $this->userHasDeleteAccess(true),
                'share' => $this->userHasShareAccess(true),
            ];
        }
    }
}
