<?php

namespace App\Models\Plugin;

use Illuminate\Database\Eloquent\Model;

class Scopes extends Model
{
    public const AVAILABLE_HOOKS = [
        'api/v1/pre',
    ];        

    protected $table = 'plugin_scopes';

    protected $fillable = [
        'plugin_id',
        'namespace',
        'on',
    ];

}