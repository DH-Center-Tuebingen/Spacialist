<?php

namespace App\Models\Plugin;

use App\Plugin;
use Illuminate\Database\Eloquent\Model;

class Hook extends Model
{
    public const AVAILABLE_HOOKS = [
        'api/v1/pre',
    ];        

    protected $table = 'plugin_hooks';

    protected $fillable = [
        'plugin_id',
        'src',
        'order',
        'on',
    ];

    /**
     * The plugin this hook belongs to.
     */
    public function plugin()
    {
        return $this->belongsTo(\App\Plugin::class, 'plugin_id');
    }
}
