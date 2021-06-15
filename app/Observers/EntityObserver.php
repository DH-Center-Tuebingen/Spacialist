<?php

namespace App\Observers;

use App\Entity;
use App\Notifications\EntityUpdated;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Activitylog\Models\Activity;

class EntityObserver
{
    /**
     * Handle the Entity "saving" event.
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
     * Handle the Entity "deleted" event.
     *
     * @param  \App\Entity  $entity
     * @return void
     */
    public function deleted(Entity $entity)
    {
        Entity::where('root_entity_id', $entity->root_entity_id)->where('rank', '>', $entity->rank)->decrement('rank');
    }
}
