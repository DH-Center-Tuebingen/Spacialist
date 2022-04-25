<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

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

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->logOnlyDirty();
    }
}
