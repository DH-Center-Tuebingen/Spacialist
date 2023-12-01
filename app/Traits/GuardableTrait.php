<?php

namespace App\Traits;

use App\AccessRule;

trait GuardableTrait
{
    public function access_rules() {
        return $this->morphMany(AccessRule::class, 'guardable')->orderBy('id');
    }
}
