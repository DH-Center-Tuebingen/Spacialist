<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;

class AttributeValue extends Model
{
    use PostgisTrait;

    protected $table = 'attribute_values';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'context_id',
        'attribute_id',
        'context_val',
        'str_val',
        'int_val',
        'dbl_val',
        'dt_val',
        'possibility',
        'possibility_description',
        'lasteditor',
        'thesaurus_val',
        'json_val'
    ];

    protected $postgisFields = [
        'geography_val',
    ];

    public function context() {
        return $this->belongsTo('App\Context');
    }

    public function attribute() {
        return $this->belongsTo('App\Attribute');
    }

    public function context_val() {
        return $this->belongsTo('App\Context', 'context_val');
    }

    public function thesaurus_val() {
        return $this->belongsTo('App\ThConcept', 'thesaurus_val', 'concept_url');
    }
}
