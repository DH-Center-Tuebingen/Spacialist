<?php

namespace App\Observers;

use App\Entity;
use App\Events\SomethingChanged;
use App\Events\EntityUpdated as EntityUpdatedEvent;
use App\Notification;
use App\Notifications\EntityUpdated;
use App\User;
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
    public function saving(Entity $entity)
    {
        $user = auth()->user();
        $userIds = $entity->activities()->groupBy('causer_id')->where('causer_id', '<>', $user->id)->pluck('causer_id')->toArray();
        foreach($userIds as $uid) {
            try {
                User::find($uid)->notify(new EntityUpdated($entity));
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
    public function saved(Entity $entity) {
        $user = auth()->user();
        $action = '';
        if($entity->wasRecentlyCreated) {
            $action = "added";
        } else {
            $action = "updated";
        }
        broadcast(new EntityUpdatedEvent($entity, $user, $action))->toOthers();
        broadcast(new SomethingChanged($entity, $user))->toOthers();
    }

    /**
     * Handle the Entity "deleted" event.
     *
     * @param  \App\Entity  $entity
     * @return void
     */
    public function deleted(Entity $entity)
    {
        Entity::where('root_entity_id', $entity->root_entity_id)->where('rank', '>', $entity->rank)->decrement('rank');
        // Delete notifications where the deleted entity is referenced
        Notification::whereRaw("(data::json->>'resource')::json->>'id' = ?", [$entity->id])->delete();
    }
}
