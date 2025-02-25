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

Broadcast::channel('channel.system', function (User $user) {
    return auth()->user()->id == $user->id;
});

Broadcast::channel('channel.entity', function (User $user) {
    return auth()->user()->id == $user->id;
});

Broadcast::channel('room.entity.{entityId}', function (User $user, int $entityId) {
    // TODO also check for $entityId
    if($user->can('entity_read')) {
        return [
            'id' => $user->id,
        ];
    }
});

Broadcast::channel('private_testchannel', function (User $user) {
    return true;
});
