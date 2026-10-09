<?php

namespace App\Models\Plugin;

use App\Plugin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hook extends Model {
    protected $table = 'plugin_service_hooks';

    protected $fillable = [
        'plugin_id',
        'src',
        'order',
        'on',
        'method',
    ];

    /**
     * The plugin this hook belongs to.
     */
    public function plugin(): BelongsTo  {
        return $this->belongsTo(Plugin::class, 'plugin_id');
    }

    public function getApiIdentifier(): string {
        return $this->method . "::" . $this->on;
    }
}
