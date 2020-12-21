<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class EntityAttribute extends Model
{
    use LogsActivity;

    protected $table = 'entity_attributes';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'entity_type_id',
        'attribute_id',
        'position',
        'depends_on',
    ];

    protected static $logOnlyDirty = true;
    protected static $logFillable = true;
    protected static $logAttributes = ['id'];

    public function attribute() {
        return $this->belongsTo('App\Attribute');
    }
}
