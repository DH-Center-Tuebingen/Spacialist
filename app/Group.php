<?php

namespace App;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use LogsActivity;

    const rules = [
        'name'          => 'required|alpha_dash|max:255|unique:groups',
        'display_name'  => 'string|max:255',
        'description'   => 'string|max:255',
    ];
    const patchRules = [
        'display_name'  => 'string|max:255',
        'description'   => 'string|max:255',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'display_name', 'description',
    ];

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->logOnlyDirty();
    }

    public function users() {
        return $this->belongsToMany('App\User', 'user_groups', 'group_id', 'user_id')->orderBy('user_groups.user_id');
    }

    public function access_rules() {
        // TODO
        return $this->hasMany('App\AccessRule');
    }
}