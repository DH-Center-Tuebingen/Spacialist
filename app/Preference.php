<?php

namespace App;

use App\UserPreference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Preference extends Model
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label',
        'default_value',
    ];


    public static function getPreferences($simple = false) {
        $prefs = self::orderBy('id')->get();
        $prefObj = self::decodePreferences($prefs, $simple);
        return $prefObj;
    }

    public static function getUserPreferences($id, $simple = false) {
        $userPrefs = UserPreference::select('user_preferences.*', 'preferences.*', 'value as default_value')
            ->join('preferences', 'user_preferences.pref_id', 'preferences.id')
            ->where('user_id', $id)
            ->get();
        $systemPrefs = self::whereNotIn('id', $userPrefs->pluck('pref_id')->toArray())
            ->get();
        $prefs = $systemPrefs->merge($userPrefs);
        $prefObj = self::decodePreferences($prefs, $simple);
        return $prefObj;
    }

    public static function getUserPreference($uid, $label) {
        $pref = self::where('label', $label)
            ->join('user_preferences', 'pref_id', 'preferences.id')
            ->where('user_id', $uid)
            ->first();
        if(!isset($pref)) {
            $pref = self::where('label', $label)->first();
            $pref->value = $pref->default_value;
            unset($pref->default_value);
        }
        $pref->value = self::decodePreference($pref->label, json_decode($pref->value));
        return $pref;
    }

    public static function hasPublicAccess() {
        $value = self::where('label', 'prefs.project-maintainer')->value('default_value');
        $decodedValue = json_decode($value);
        return sp_parse_boolean($decodedValue->public);
    }

    public static function decodePreference($label, $value) {
        switch($label) {
            case 'prefs.gui-language':
                return $value->language_key;
            case 'prefs.color':
                return $value->color_key;
            case 'prefs.enable-password-reset-link':
                return $value->use;
            case 'prefs.columns':
                return $value;
            case 'prefs.show-tooltips':
                return $value->show;
            case 'prefs.tag-root':
                return $value->uri;
            case 'prefs.link-to-thesaurex':
                return $value->url;
            case 'prefs.project-name':
                return $value->name;
            case 'prefs.project-maintainer':
                return $value;
            case 'prefs.map-projection':
                $proj4 = DB::table('spatial_ref_sys')
                    ->where('auth_srid', $value->epsg)
                    ->value('proj4text');
                return [
                    'epsg' => $value->epsg,
                    'proj4' => $proj4
                ];
        }
        return $value;
    }

    public static function encodePreference($label, $decodedValue) {
        $value = $decodedValue;
        switch($label) {
            case 'prefs.gui-language':
                $value = json_encode(['language_key' => $decodedValue]);
                break;
            case 'prefs.color':
                $value = json_encode(['color_key' => $decodedValue]);
                break;
            case 'prefs.enable-password-reset-link':
                $value = json_encode(['use' => $decodedValue]);
                break;
            case 'prefs.columns':
                $value = json_encode($decodedValue);
                break;
            case 'prefs.show-tooltips':
                $value = json_encode(['show' => $decodedValue]);
                break;
            case 'prefs.tag-root':
                $value = json_encode(['uri' => $decodedValue]);
                break;
            case 'prefs.link-to-thesaurex':
                $value = json_encode(['url' => $decodedValue]);
                break;
            case 'prefs.project-name':
                $value = json_encode(['name' => $decodedValue]);
                break;
            case 'prefs.project-maintainer':
                $value = $decodedValue;
                break;
            case 'prefs.map-projection':
                $value = $decodedValue;
                break;
        }
        return $value;
    }

    private static function decodePreferences($prefs, $simple = false) {
        $prefObj = [];
        foreach($prefs as $p) {
            $decoded = json_decode($p->default_value);
            unset($p->default_value);
            $p->value = self::decodePreference($p->label, $decoded);
            if($simple) {
                $data = $p->value;
            } else {
                $data = $p;
            }
            $prefObj[$p->label] = $data;
        }
        return $prefObj;
    }
}
