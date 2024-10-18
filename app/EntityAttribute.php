<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

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

    protected $casts = [
        'depends_on' => 'array',
    ];

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->logOnlyDirty();
    }

    public function removeFromEntityType() {
        $pos = $this->position;
        $aid = $this->attribute_id;
        $etid = $this->entity_type_id;

        $successors = EntityAttribute::where([
                ['position', '>', $pos],
                ['entity_type_id', '=', $etid]
            ])->get();
        foreach($successors as $s) {
            $s->position--;
            $s->save();
        }

        $entityIds = Entity::where('entity_type_id', $etid)
            ->pluck('id')
            ->toArray();
        AttributeValue::where('attribute_id', $aid)
            ->whereIn('entity_id', $entityIds)
            ->delete();
    }

    public static function for($entityId, $attributeId) {
        $instance = new static;
        return $instance->where([
            ['entity_type_id', $entityId],
            ['attribute_id', $attributeId]
        ])->firstOrFail();
    }

    public function attribute() {
        return $this->belongsTo('App\Attribute');
    }

    public function entity_type() {
        return $this->belongsTo('App\EntityType');
    }
}
