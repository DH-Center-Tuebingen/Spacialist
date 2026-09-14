<?php

namespace App\Models\Plugin;

use Illuminate\Database\Eloquent\Model;

class Scopes extends Model
{    

    protected $table = 'plugin_service_scopes';

    protected $fillable = [
        'plugin_id',
        'namespace',
        'on',
    ];

}