<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileTag extends Model
{
    protected $table = 'photo_tags';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $primaryKay = '';
    public $incrementing = false;
}
