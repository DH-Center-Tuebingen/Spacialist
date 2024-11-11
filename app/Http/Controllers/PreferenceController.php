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
        $user = auth()->user();
        if(!$user->can('preferences_read')) {
            return response()->json([
                'error' => __('You are not allowed to read preferences')
            ], 403);
        }

        $preferences = Preference::getPreferences();
        return response()->json($preferences);
    }

    // POST

    // PATCH
    public function patchPreferences(Request $request) {
        $user = auth()->user();

        $this->validate($request, [
            'changes' => 'required|array',
        ]);

        $changes = $request->get('changes');

        foreach($changes as $c) {
            $label = $c['label'];
            $value = $c['value'] ?? '';
            $isUser = isset($c['user']) && $c['user'];

            if(!$isUser && !$user->can('preferences_write')) {
                return response()->json([
                    'error' => __('You do not have the permission to edit system preferences')
                ], 403);
            }

            try {
                $pref = Preference::where('label', $label)->firstOrFail();
            } catch(ModelNotFoundException $e) {
                return response()->json([
                    'error' => __('This preference does not exist')
                ], 400);
            }
            $encodedValue = Preference::encodePreference($label, $value);

            if($isUser) {
                UserPreference::updateOrCreate(
                    ['pref_id' => $pref->id, 'user_id' => $user->id],
                    ['value' => $encodedValue]
                );
            } else {
                $pref->default_value = $encodedValue;
                $pref->save();
            }
        }

        return response()->json(null, 204);
    }

    // PUT

    // DELETE
}
