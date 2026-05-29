<?php
namespace App\Models\Plugin;

use App\Plugin;
use Illuminate\Database\Eloquent\Model;

class Hook extends Model
{
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
    public function plugin()
    {
        return $this->belongsTo(\App\Plugin::class, 'plugin_id');
    }
    
    public function getApiIdentifier(): string {
        return $this->method . "::" . $this->on;
    }    
}
