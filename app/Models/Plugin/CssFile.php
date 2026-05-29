<?php
namespace App\Models\Plugin;

use App\Plugin;
use Illuminate\Database\Eloquent\Model;

class CssFile extends Model
{
    protected $table = 'plugin_service_css_files';

    protected $fillable = [
        'plugin_id',
        'src',
        'order',
    ];

    /**
     * The plugin this hook belongs to.
     */
    public function plugin()
    {
        return $this->belongsTo(Plugin::class, 'plugin_id');
    }
    
}
