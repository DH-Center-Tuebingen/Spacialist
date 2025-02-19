<?php

namespace App\Observers;

use App\Entity;
use App\Events\EntityCreated;
use App\Events\EntityDeleted;
use App\Events\EntityUpdated;
use App\Notification;
use App\Notifications\EntityUpdated as EntityUpdatedNotification;
use App\User;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EntityObserver
{
    /**
     * Handle the Entity "saving" event.
     * Notifications have to be send before entity is updated to get correct recipient list
     *
     * @param  \App\Entity  $entity
     * @return void
     */
    public function saving(Entity $entity) : void {
        $user = auth()->user();
        $userIds = $entity->activities()->groupBy('causer_id')->where('causer_id', '<>', $user->id)->pluck('causer_id')->toArray();
        foreach($userIds as $uid) {
            try {
                User::find($uid)->notify(new EntityUpdatedNotification($entity));
            } catch(ModelNotFoundException $e) {
            }
        }
    }

    /**
     * Handle the Entity "saved" event.
     * dispatching update events require persisted new entity data
     *
     * @param \App\Entity $entity
     * @return void
     *
     */
    public function saved(Entity $entity) : void {
        try {
            $user = auth()->user();
            if($entity->wasRecentlyCreated) {
                broadcast(new EntityCreated($entity, $user))->toOthers();
            } else {
                broadcast(new EntityUpdated($entity, $user))->toOthers();
            }
        } catch(BroadcastException $e) {
            info("BroadcastException while handling saved() event in EntityObserver");
        }
    }

    /**
     * Handle the Entity "deleting" event.
     *
     * @param  \App\Entity  $entity
     * @return void
     */
    public function deleting(Entity $entity) : void {
        Entity::where('root_entity_id', $entity->root_entity_id)->where('rank', '>', $entity->rank)->decrement('rank');
        // Delete notifications where the deleted entity is referenced
        Notification::whereRaw("(data::json->>'resource')::json->>'id' = ?", [$entity->id])->delete();
        try {
            broadcast(new EntityDeleted($entity, $user = auth()->user()))->toOthers();
        } catch(BroadcastException $e) {
            info("BroadcastException while handling deleting() event in EntityObserver");
        }
    }
}
