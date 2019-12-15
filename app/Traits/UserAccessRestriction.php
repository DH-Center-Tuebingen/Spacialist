<?php

namespace App\Traits;

trait UserAccessRestriction
{
    /**
     * Scope a query to only include files allowed by the current user.
     */
    public function scopeHasAccessTo($query, $user)
    {
        return $query->whereHas('access_rules', function($subQ) use($user) {
            $subQ->whereIn('group_id', $user->groups()->pluck('id')->toArray());
            $subQ->whereNotNull('rules');
        })->orDoesntHave('access_rules');
    }
}
