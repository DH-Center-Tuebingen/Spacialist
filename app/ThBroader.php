<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ThBroader extends Model
{
    use LogsActivity;

    protected $table = 'th_broaders';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'broader_id',
        'narrower_id',
    ];

    protected static $logOnlyDirty = true;
    protected static $logFillable = true;
    protected static $logAttributes = ['id'];

}
