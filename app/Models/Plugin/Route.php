<?php
namespace App\Models\Plugin;

use App\Plugin;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $table = 'plugin_service_routes';

    protected $fillable = [
        'plugin_id',
        'src',
        'plugin_name',
        'plugin_slug',
        'middleware',
    ];

    /**
     * The plugin this model belongs to.
     */
    public function plugin()
    {
        return $this->belongsTo(Plugin::class, 'plugin_id');
    }
    
}
