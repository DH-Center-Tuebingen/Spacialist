<?php

namespace App\Traits;

use App\AccessRule;

trait RestrictableTrait
{
    public function access_type() {
        return $this->morphOne(AccessType::class, 'accessible');
    }

    public function access_rules() {
        return $this->morphMany(AccessRule::class, 'restrictable')->orderBy('id');
    }
}
