<?php

namespace App\Observers;

use App\AttributeValue;
use App\Events\AttributeValueCreated;
use App\Events\AttributeValueDeleted;
use App\Events\AttributeValueUpdated;

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
        $user = auth()->user();
        if($attributeValue->wasRecentlyCreated) {
            broadcast(new AttributeValueCreated($attributeValue, $user))->toOthers();
        } else {
            broadcast(new AttributeValueUpdated($attributeValue, $user))->toOthers();
        }
    }

    /**
     * Handle the AttributeValue "deleting" event.
     *
     * @param  \App\AttributeValue  $attributeValue
     * @return void
     */
    public function deleting(AttributeValue $attributeValue) : void {
        broadcast(new AttributeValueDeleted($attributeValue, $user = auth()->user()))->toOthers();
    }
}
