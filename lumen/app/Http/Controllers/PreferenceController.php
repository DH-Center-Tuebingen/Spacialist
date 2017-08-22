<?php

namespace App\Http\Controllers;
use App\User;
use App\Preference;
use App\UserPreference;
use App\Helpers;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PreferenceController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    // GET
    public function getPreferences() {
        $user = \Auth::user();
        if(!$user->can('edit_preferences')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $prefs = Preference::orderBy('id')->get();
        $prefObj = $this->decodePreferences($prefs, false);
        return $prefObj;
    }

    public function getUserPreferences($id) {
        $user = \Auth::user();
        if($user['id'] != $id && !$user->can('edit_preferences')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $prefs = Preference::where('allow_override', true)
            ->leftJoin('user_preferences as up', 'preferences.id', '=', 'up.pref_id')
            ->select('preferences.*', 'up.pref_id', 'up.user_id', 'up.value')
            ->orderBy('id')
            ->get();
        $prefObj = $this->decodePreferences($prefs, true);
        return $prefObj;
    }

    // POST

    // PATCH
    public function patchPreference(Request $request, $id) {
        $this->validate($request, [
            'label' => 'required|string|exists:preferences,label',
            'value' => 'string',
            'user_id' => 'nullable|integer|exists:users,id',
            'allow_override' => 'nullable|boolean_string'
        ]);

        $label = $request->get('label');
        $value = $request->get('value');
        $uid = $request->get('user_id');
        $allowOverride = $request->get('allow_override');

        // Permission is required, if preference is not a user preference
        $user = \Auth::user();
        if((!isset($uid) && !$user->can('edit_preferences')) || (isset($uid) && !$user->can('edit_preferences') && $uid != $user['id'])) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        try {
            $pref = Preference::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This preference does not exist'
            ]);
        }

        $encodedValue = $this->encodePreference($label, $value);

        if(isset($uid)) {
            $userPref = UserPreference::where('pref_id', $id)->where('user_id', $uid)->first();
            // if this preference doesn't exist for the desired user: create it
            if($userPref == null) {
                $userPref = new UserPreference();
                $userPref->pref_id = $id;
                $userPref->user_id = $uid;
            }
            $userPref->value = $encodedValue;
            $userPref->save();
        } else {
            $pref->default_value = $encodedValue;
            if(isset($allowOverride)) {
                $allowOverride = Helpers::parseBoolean($allowOverride);
                $removeUserPrefs = $pref->allow_override && !$allowOverride;
                $pref->allow_override = $allowOverride;
                // remove stored user prefs, if pref is no longer overridable
                if($removeUserPrefs) {
                    UserPreference::where('pref_id', $id)->delete();
                }
            }
            $pref->save();
        }
    }

    // PUT

    // DELETE

    // OTHER FUNCTIONS
    private function decodePreferences($prefs, $isUserPref) {
        $prefObj = [];
        foreach($prefs as $p) {
            if(!isset($p->value)) {
                $decoded = json_decode($p->default_value);
            } else {
                $decoded = json_decode($p->value);
            }
            unset($p->default_value);
            if($isUserPref) unset($p->allow_override);
            switch($p->label) {
                case 'prefs.gui-language':
                    $p->value = $decoded->language_key;
                    break;
                case 'prefs.columns':
                    break;
                case 'prefs.show-tooltips':
                    $p->value = $decoded->show;
                    break;
                case 'prefs.tag-root':
                    $p->value = $decoded->uri;
                    break;
                case 'prefs.load-extensions':
                    break;
                case 'prefs.link-to-thesaurex':
                    $p->value = $decoded->show;
                    break;
                case 'prefs.project-name':
                    $p->value = $decoded->name;
                    break;
            }
            $prefObj[$p->label] = $p;
        }
        return $prefObj;
    }

    private function encodePreference($label, $decodedValue) {
        $value;
        switch($label) {
            case 'prefs.gui-language':
                $value = json_encode(['language_key' => $decodedValue]);
                break;
            case 'prefs.columns':
                break;
            case 'prefs.show-tooltips':
                $value = json_encode(['show' => $decodedValue]);
                break;
            case 'prefs.tag-root':
                $value = json_encode(['uri' => $decodedValue]);
                break;
            case 'prefs.load-extensions':
                break;
            case 'prefs.link-to-thesaurex':
                $value = json_encode(['show' => $decodedValue]);
                break;
            case 'prefs.project-name':
                $value = json_encode(['name' => $decodedValue]);
                break;
        }
        return $value;
    }
}
