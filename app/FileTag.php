<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileTag extends Model
{
    protected $table = 'file_tags';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
     protected $fillable = ['file_id', 'concept_url'];
}
