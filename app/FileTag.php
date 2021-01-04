<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class FileTag extends Model
{
    use LogsActivity;

    protected $table = 'file_tags';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
     protected $fillable = [
         'file_id',
         'concept_id'
     ];

     protected static $logOnlyDirty = true;
     protected static $logFillable = true;
     protected static $logAttributes = ['id'];
}
