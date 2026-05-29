<?php

namespace App\Models\Plugin;

use App\Plugin;
use Illuminate\Database\Eloquent\Model;

class Dependencies extends Model {
    protected $table = 'plugin_service_dependencies';

    protected $fillable = [
        'plugin_id',
        'depends_on',
    ];

    /**
     * The plugin this hook belongs to.
     */
    public function plugin() {
        return $this->belongsTo(Plugin::class, 'plugin_id');
    }
}