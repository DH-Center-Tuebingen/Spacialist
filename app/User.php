<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasRoles;
    // use Authenticatable;

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'nickname', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getLanguage() {
        $langObj = Preference::getUserPreference($this->id, 'prefs.gui-language');
        if(isset($langObj)) return $langObj->value;
        return 'en';
    }

    public function setPermissions() {
        $permissions = [];
        foreach($this->roles as $role) {
            $rolePermissions = $role->permissions;
            foreach($rolePermissions as $p) {
                if(!isset($permissions[$p->name])) {
                    $permissions[$p->name] = 1;
                }

            }
        }
        $this->permissions = $permissions;
    }

    public function isModerated() {
        $moderated = true;

        foreach($this->roles as $r) {
            if(!$r->moderated) {
                $moderated = false;
                break;
            }
        }

        return $moderated;
    }

    public function preferences() {
        return $this->hasMany('App\UserPreference');
    }

    // public function roles() {
    //     return $this->belongsToMany('App\Role', 'role_user', 'user_id', 'role_id');
    // }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }
}
