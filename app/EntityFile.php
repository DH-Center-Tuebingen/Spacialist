<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class EntityFile extends Model
{
    use LogsActivity;

    protected $table = 'entity_files';

    public $timestamps = false; // disable updated_at and created_at in ->save()

    // disable primary key
    protected $primaryKey = null;
    public $incrementing = false;

    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_id',
        'entity_id',
        'lasteditor',
    ];

    protected static $logOnlyDirty = true;
    protected static $logFillable = true;
    protected static $ignoreChangedAttributes = ['lasteditor'];
}
