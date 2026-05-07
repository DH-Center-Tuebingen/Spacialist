<?php
namespace App\Models\Plugin;

use App\Plugin;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $table = 'plugin_service_attributes';

    protected $fillable = [
        'plugin_id',
        'src',
    ];

    /**
     * The plugin this hook belongs to.
     */
    public function plugin()
    {
        return $this->belongsTo(Plugin::class, 'plugin_id');
    }
}