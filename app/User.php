<?php

namespace App;

use App\Traits\SoftDeletesWithTrashed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Notifications\Notifiable;
use App\File\FileDirectory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;
    use CausesActivity;
    use LogsActivity;
    use SoftDeletesWithTrashed;
    use HasFactory;
    use HasApiTokens;
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

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->dontLogIfAttributesChangedOnly(['password'])
            ->logOnlyDirty();
    }

    public function getLanguage() {
        $langObj = Preference::getUserPreference($this->id, 'prefs.gui-language');
        if(isset($langObj)) return $langObj->value;
        return 'en';
    }

    public function uploadAvatar($file) {
        $avatarDirectory = self::getAvatarDirectory();
        $avatarDirectory->deleteRaw($this->avatar);
        $filename = $this->id . "." . $file->getClientOriginalExtension();
        $storedFilename = $avatarDirectory->store($filename, $file);
        $this->avatar = $storedFilename;
        $this->save();
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

    public function setMetadata(array $data, bool $save = false) {
        if(!isset($this->metadata)) {
            $this->metadata = $data;
        } else {
            $this->metadata = array_replace($this->metadata, $data);
        }

        if($save) {
            $this->save();
        }
    }

    public function isModerated() : bool {
         $moderated = false;

        foreach($this->roles as $r) {
            if($r->isModerated()) {
                $moderated = true;
                break;
            }
        }

        return $moderated;
    }

    public function preferences() {
        return $this->hasMany('App\UserPreference');
    }

    public static function getAvatarDirectory(){
        return new FileDirectory('local', 'avatars');
    }

    // public function roles() {
    //     return $this->belongsToMany('App\Role', 'role_user', 'user_id', 'role_id');
    // }
}
