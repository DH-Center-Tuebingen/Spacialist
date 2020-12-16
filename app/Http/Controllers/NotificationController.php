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

        $notif = $user->notifications->where('id', $id)->first();
        if(!isset($notif)) {
            return response()->json([
                'error' => __('This notification does not exist')
            ], 400);
        }

        $notif->markAsRead();
        return response()->json(null, 204);
    }

    public function markAllNotificationsAsRead(Request $request)
    {
        $user = auth()->user();
        $this->validate($request, [
            'ids' => 'required|array',
            'ids.*' => 'exists:notifications,id',
        ]);
        $user->
        unreadNotifications->whereIn('id', $request->input('ids'))->markAsRead();
        return response()->json(null, 204);
    }

    // PUT

    // DELETE

    public function deleteNotification($id)
    {
        $user = auth()->user();

        $notif = $user->notifications->where('id', $id)->first();
        if(!isset($notif)) {
            return response()->json([
                'error' => __('This notification does not exist for this user')
            ], 400);
        }

        $notif->delete();
        return response()->json(null, 204);
    }

    public function deleteNotifications(Request $request)
    {
        $user = auth()->user();
        $this->validate($request, [
            'ids' => 'required|array',
            'ids.*' => 'exists:notifications,id',
        ]);
        $notifications = $user->notifications->whereIn('id', $request->input('ids'));
        foreach($notifications as $n) {
            $n->delete();
        }
        return response()->json(null, 204);
    }

    // OTHER FUNCTIONS

}
