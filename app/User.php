<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Notifications\Notifiable;
use App\File\Directory;
use App\Events\UserLogin;
use App\Events\UserLogout;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    use SoftDeletes;
    use HasFactory;
    use HasApiTokens;
    // use Authenticatable;

    protected $guard_name = 'web';
    
    // Disables the remember_web token, as we don't need it using Sanctum authentication
    // and it would disrupt the session_cookies, as the token is managed in the User table, 
    // which conflicts when accessed from multiple websites.
    protected $rememberTokenName = null;

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
        'password',
    ];
    
    
    /**
     * Handles the login attempts logic and broadcasts the login event.
     * @return void
     */
    public function login(){
        if($this->login_attempts > 0) {
            $this->login_attempts--;
            $this->save();
        }
        try {
            UserLogin::dispatch($this);
        } catch(BroadcastException $e) {
            if(env('APP_DEBUG')) {
                info("Error dispatching UserLogin event: " . $e->getMessage());
            }
        }
    }

    /**
     * Handles the logout logic and broadcasts the logout event.
     * @return void
     */
    public function logout(){
        try {
            UserLogout::dispatch($this);
        } catch(BroadcastException $e) {
            if(env('APP_DEBUG')) {
                info("Error dispatching UserLogout event: " . $e->getMessage());
            }
        }
    }
    
   /**
     * Checks if the user has login attempts left.
     * @return bool
     */
    public function hasLoginAttemptsLeft(): bool {
        return $this->login_attempts == null || $this->login_attempts > 0;
    }
    
    /**
     * When password is resetted externally (e.g. by an admin)
     * This will reset the login attempts counter as well (to 3).
     * @param string $newPassword The new password to set.
     * @return void
     */
    public function externalPasswordReset($newPassword){        
        $this->login_attempts = 3;
        $this->resetPassword($newPassword);
    }
    
    /**
     * Resets the user's password.
     * @param string $newPassword The new password to set.
     * @return void
     */
    public function resetPassword($newPassword) {
        $password = Hash::make($newPassword);
        $this->password = $password;
        $this->save();
    }
    
    /**
     * Confirms the user's password and resets login attempts.
     * @param string $newPassword The new password to set.
     * @return void
     */
    public function confirmPassword($newPassword){
        $this->login_attempts = null;
        $this->resetPassword($newPassword);
    }   
    

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
        self::getDirectory()->delete($this->avatar);
        $this->avatar = null;
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

    public static function getDirectory(): Directory {
        return new Directory('avatars');
    }

}
