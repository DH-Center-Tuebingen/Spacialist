<?php

namespace App;

use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{
    const rules = [
        'name'          => 'required|string|max:255|unique:roles',
        'display_name'  => 'string|max:255',
        'description'   => 'string|max:255',
    ];
    const patchRules = [
        'display_name'  => 'string|max:255',
        'description'   => 'string|max:255',
    ];
}
