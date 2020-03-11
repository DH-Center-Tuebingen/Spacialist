<?php

namespace App\Http\Controllers;

use App\User;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ActivityController extends Controller
{

    // GET
    public function getAll(Request $request) {
        $user = auth()->user();
        if(!$user->can('view_users')) {
            return response()->json([
                'error' => __('You do not have the permission to view activity logs')
            ], 403);
        }

        return response()->json(Activity::with(['causer', 'subject'])->get());
    }

    public function getByUser(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('view_users') && $user->id !== $id) {
            return response()->json([
                'error' => __('You do not have the permission to view activity logs')
            ], 403);
        }

        try {
            $actUser = $user->id == $id ? $user : User::firstOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This user does not exist')
            ], 400);
        }

        $actUser->actions->load('causer');
        $actUser->actions->load('subject');

        return response()->json($actUser->actions);
    }

    public function getFiltered(Request $request, $page = 1) {
        $user = auth()->user();
        if(!$user->can('view_users')) {
            return response()->json([
                'error' => __('You do not have the permission to view activity logs')
            ], 403);
        }

        return response()->json(Activity::with(['causer', 'subject'])->paginate());
    }
}
