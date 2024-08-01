<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model {
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pref_id',
        'user_id',
        'value',
    ];

    public static function setOwn($label, $value) {
        $user = auth()->user();
        if (!isset($user)) {
            throw new \Exception('User not found');
        }
        self::set($user->id, $label, $value);
    }

    public static function set($user_id, $label, $value) {
        $pref = Preference::where('label', $label)->firstOrFail();
        $userPref = UserPreference::where('user_id', $user_id)
            ->where('pref_id', $pref->id)
            ->first();

        if (isset($userPref)) {
            self::updatePreference($userPref->id, $label, $value);
        } else {
            self::createPreference($pref->id, $user_id, $label, $value);
        }
    }

    private static function createPreference($pref_id, $user_id, $label, $value) {
        $pref = new UserPreference();
        $pref->pref_id = $pref_id;
        $pref->user_id = $user_id;
        $pref->value = Preference::encodePreference($label, $value);
        $pref->save();
    }

    private static function updatePreference($pref_id, $label, $value) {
        $pref = UserPreference::findOrFail($pref_id);
        $pref->value = Preference::encodePreference($label, $value);
        $pref->save();
    }
}
