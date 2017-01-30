<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;

class Geodata extends Model
{
    use PostgisTrait;

    protected $table = 'geodata';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];
    
    protected $postgisFields = [
        'geom',
    ];
}
