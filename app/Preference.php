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

    private static function decodePreferences($prefs) {
        $prefObj = [];
        foreach($prefs as $p) {
            $decoded = json_decode($p->default_value);
            unset($p->default_value);
            switch($p->label) {
                case 'prefs.gui-language':
                    $p->value = $decoded->language_key;
                    break;
                case 'prefs.columns':
                    $p->value = $decoded;
                    break;
                case 'prefs.show-tooltips':
                    $p->value = $decoded->show;
                    break;
                case 'prefs.tag-root':
                    $p->value = $decoded->uri;
                    break;
                case 'prefs.load-extensions':
                    $p->value = $decoded;
                    break;
                case 'prefs.link-to-thesaurex':
                    $p->value = $decoded->url;
                    break;
                case 'prefs.project-name':
                    $p->value = $decoded->name;
                    break;
                case 'prefs.project-maintainer':
                    $p->value = $decoded;
                    break;
            }
            $prefObj[$p->label] = $p;
        }
        return $prefObj;
    }
}
