<?php

namespace App\Models\Plugin;

use Illuminate\Database\Eloquent\Model;

class Scope extends Model {
    protected $table = 'plugin_service_scopes';

    protected $fillable = [
        'plugin_id',
        'namespace',
        'on',
    ];
}