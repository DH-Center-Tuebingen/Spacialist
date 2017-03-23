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
        'lasteditor',
        'thesaurus_val',
        'json_val'
    ];

    protected $postgisFields = [
        'geography_val',
    ];
}
