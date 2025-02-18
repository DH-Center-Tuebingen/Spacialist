<?php

namespace App;

use App\Traits\SoftDeletesWithTrashed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Notifications\Notifiable;
use App\File\Directory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

    public function uploadAvatar($file): string {
        $avatarDirectory = self::getDirectory();
        $avatarDirectory->delete($this->avatar);
        $filename = $this->id . "." . $file->getClientOriginalExtension();
        $storedFilename = $avatarDirectory->store($filename, $file);
        $this->avatar = $storedFilename;
        $this->save();
        return$storedFilename;
    }

    public function deleteAvatar() : void{
        $success = self::getDirectory()->delete($this->avatar);
        if($success) {
            $this->avatar = null;
            $this->save();
        }
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

    public static function getDirectory(): Directory {
        return new Directory('avatars');
    }
    
    public static function create($name, $nickname, $email, $password) {
        $password = Hash::make($password);

        $user = new User();
        $user->name = $name;
        $user->nickname = Str::lower($nickname);
        $user->email = Str::lower($email);
        $user->password = $password;
        $user->save();
        $user = User::find($user->id);
    }

    // public function roles() {
    //     return $this->belongsToMany('App\Role', 'role_user', 'user_id', 'role_id');
    // }
}
