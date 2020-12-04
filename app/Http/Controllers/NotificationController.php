<?php

namespace App\Http\Controllers;

use App\Notification;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // GET

    // POST

    // PATCH

    public function markNotificationAsRead($id)
    {
        $user = auth()->user();

        try {
            $notif = Notification::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This notification does not exist')
            ], 400);
        }

        if (count($user->notifications()->where('id', $id)->all()) === 0) {
            return response()->json([
                'error' => __('This notification does not exist for this user')
            ], 400);
        }

        $notif->markAsRead();
        return response()->json(null, 204);
    }

    public function markAllNotificationsAsRead()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();
        return response()->json(null, 204);
    }

    // PUT

    // DELETE

    public function deleteNotification($id)
    {
        $user = auth()->user();

        try {
            $notif = Notification::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This notification does not exist')
            ], 400);
        }

        if(count($user->notifications()->where('id', $id)->all()) === 0) {
            return response()->json([
                'error' => __('This notification does not exist for this user')
            ], 400);
        }

        $notif->delete();
        return response()->json(null, 204);
    }

    public function deleteNotifications()
    {
        $user = auth()->user();
        $user->notifications()->delete();
        return response()->json(null, 204);
    }

    // OTHER FUNCTIONS

}
