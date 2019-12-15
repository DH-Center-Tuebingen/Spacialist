<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
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

    public function users() {
        return $this->belongsToMany('App\User', 'user_groups', 'group_id', 'user_id')->orderBy('user_groups.user_id');
    }
}
