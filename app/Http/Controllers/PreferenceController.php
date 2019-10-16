<?php

namespace App\Http\Controllers;
use App\Preference;
use App\User;
use App\UserPreference;
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
        $preferences = Preference::getPreferences();
        return response()->json($preferences);
    }

    public function getUserPreferences($id) {
        $user = auth()->user();

        try {
            User::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This user does not exist')
            ], 400);
        }

        if(!isset($user) || $user->id != $id) {
            return response()->json([
                'error' => __('You are not allowed to access preferences of another user')
            ], 403);
        }
        $preferences = Preference::getUserPreferences($id);
        return response()->json($preferences);
    }

    // POST

    // PATCH
    public function patchPreference(Request $request, $id) {
        $user = auth()->user();
        $uid = $request->get('user_id');
        if(!$user->can('edit_preferences') && !isset($uid)) {
            return response()->json([
                'error' => __('You do not have the permission to edit preferences')
            ], 403);
        }

        $this->validate($request, [
            'label' => 'required|string|exists:preferences,label',
            'value' => 'nullable',
            'user_id' => 'nullable|integer|exists:users,id',
            'allow_override' => 'nullable|boolean_string'
        ]);

        $label = $request->get('label');
        $value = $request->get('value');
        $allowOverride = $request->get('allow_override');

        try {
            $pref = Preference::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This preference does not exist')
            ], 400);
        }

        $encodedValue = Preference::encodePreference($label, $value);

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
                $allowOverride = sp_parse_boolean($allowOverride);
                $removeUserPrefs = $pref->allow_override && !$allowOverride;
                $pref->allow_override = $allowOverride;
                // remove stored user prefs, if pref is no longer overridable
                if($removeUserPrefs) {
                    UserPreference::where('pref_id', $id)->delete();
                }
            }
            $pref->save();
            return response()->json(null, 204);
        }
    }

    // PUT

    // DELETE
}
