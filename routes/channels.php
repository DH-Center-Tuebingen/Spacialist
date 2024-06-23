<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

use App\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.User.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('entity_updates', function (User $user) {
    info("is user allowed to broadcast?");
    info($user);
    info($user->can('entity_read') ? "Yes, allowed" : "No, not allowed");
    return $user->can('entity_read');
});
