<?php
namespace App\Models\Plugin;

use App\Plugin;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $table = 'plugin_service_route_files';

    protected $fillable = [
        'plugin_id',
        'src',
    ];

    /**
     * The plugin this model belongs to.
     */
    public function plugin()
    {
        return $this->belongsTo(Plugin::class, 'plugin_id');
    }
    
}
