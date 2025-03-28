<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Reference extends Model
{
    use LogsActivity;

    protected $table = 'references';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'entity_id',
        'attribute_id',
        'bibliography_id',
        'description',
        'user_id',
    ];

    const rules = [
        'bibliography_id' => 'required|integer|exists:bibliography,id',
        'description' => 'required|string'
    ];

    const patchRules = [
        'description' => 'required|string'
    ];

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->dontLogIfAttributesChangedOnly(['user_id'])
            ->logOnlyDirty();
    }

    public static function add($values, $user) {
        $reference = new self();
        foreach($values as $k => $v) {
            $reference->{$k} = $v;
        }
        $reference->user_id = $user->id;
        $reference->save();

        return self::with('bibliography')->find($reference->id);
    }

    public function patch($values) {
        foreach($values as $k => $v) {
            $this->{$k} = $v;
        }
        $this->save();
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function entity() {
        return $this->belongsTo('App\Entity');
    }

    public function attribute() {
        return $this->belongsTo('App\Attribute');
    }

    public function bibliography() {
        return $this->belongsTo('App\Bibliography', 'bibliography_id');
    }
}
