<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ThConceptLabel extends Model
{
    use LogsActivity;

    protected static $logOnlyDirty = true;
    protected static $logFillable = true;
    protected static $logAttributes = ['id'];
    protected static $ignoreChangedAttributes = ['user_id'];

    protected $table = 'th_concept_label';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'concept_id',
        'language_id',
        'user_id',
        'label',
        'concept_label_type',
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function concept() {
        return $this->belongsTo('App\ThConcept', 'concept_id');
    }

    public function language() {
        return $this->belongsTo('App\ThLanguage', 'language_id');
    }
}
