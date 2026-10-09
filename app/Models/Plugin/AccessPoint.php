<?php

namespace App\Models\Plugin;

use App\Plugin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessPoint extends Model {
    protected $table = 'plugin_service_access_points';

    protected $fillable = [
        'plugin_id',
        'path',
        'label',
        'identifier',
    ];

    /**
     * The plugin this hook belongs to.
     */
    public function plugin(): BelongsTo  {
        return $this->belongsTo(Plugin::class, 'plugin_id');
    }
}
