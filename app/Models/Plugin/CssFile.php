<?php
namespace App\Models\Plugin;

use App\Plugin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class, 'plugin_id');
    }
    
}
