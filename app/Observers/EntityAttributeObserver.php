<?php

namespace App\Observers;

use App\AttributeValue;
use App\Events\AttributeValueCreated;
use App\Events\AttributeValueDeleted;
use App\Events\AttributeValueUpdated;
use Illuminate\Broadcasting\BroadcastException;

class EntityAttributeObserver {
    /**
     * Handle the AttributeValue "saved" event.
     * dispatching update events require persisted new AttributeValue data
     *
     * @param \App\AttributeValue $attributeValue
     * @return void
     *
     */
    public function saved(AttributeValue $attributeValue) : void {
        try {
            $user = auth()->user();
            if($attributeValue->wasRecentlyCreated) {
                broadcast(new AttributeValueCreated($attributeValue, $user))->toOthers();
            } else {
                broadcast(new AttributeValueUpdated($attributeValue, $user))->toOthers();
            }
        } catch(BroadcastException $e) {
            info("BroadcastException while handling saved() event in EntityAttributeObserver");
        }
    }

    /**
     * Handle the AttributeValue "deleting" event.
     *
     * @param  \App\AttributeValue  $attributeValue
     * @return void
     */
    public function deleting(AttributeValue $attributeValue) : void {
        try {
            broadcast(new AttributeValueDeleted($attributeValue, auth()->user()))->toOthers();
        } catch(BroadcastException $e) {
            info("BroadcastException while handling deleting() event in EntityAttributeObserver");
        }
    }
}
