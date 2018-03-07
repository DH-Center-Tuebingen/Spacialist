<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Preference extends Model
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    public static function getPreferences() {
        $prefs = self::orderBy('id')->get();
        $prefObj = self::decodePreferences($prefs);
        return $prefObj;
    }

    public static function getUserPreferences($id) {
        $prefs = self::leftJoin('user_preferences as up', 'preferences.id', '=', 'up.pref_id')
            ->select('preferences.*', 'up.pref_id', 'up.user_id')
            ->selectRaw(\DB::raw('COALESCE(up.value, default_value) AS default_value'))
            ->where('up.user_id', $id)
            ->orWhereNull('up.user_id')
            ->orderBy('id')
            ->get();
        $prefObj = self::decodePreferences($prefs);
        return $prefObj;
    }

    public static function hasPublicAccess() {
        $value = self::where('label', 'prefs.project-maintainer')->value('default_value');
        $decodedValue = json_decode($value);
        return Helpers::parseBoolean($decodedValue->public);
    }

    public static function decodePreference($label, $value) {
        switch($label) {
            case 'prefs.gui-language':
                return $value->language_key;
            case 'prefs.columns':
                return $value;
            case 'prefs.show-tooltips':
                return $value->show;
            case 'prefs.tag-root':
                return $value->uri;
            case 'prefs.load-extensions':
                return $value;
            case 'prefs.link-to-thesaurex':
                return $value->url;
            case 'prefs.project-name':
                return $value->name;
            case 'prefs.project-maintainer':
                return $value;
        }
        return $value;
    }

    private static function decodePreferences($prefs) {
        $prefObj = [];
        foreach($prefs as $p) {
            $decoded = json_decode($p->default_value);
            unset($p->default_value);
            $p->value = self::decodePreference($p->label, $decoded);
            $prefObj[$p->label] = $p;
        }
        return $prefObj;
    }
}
