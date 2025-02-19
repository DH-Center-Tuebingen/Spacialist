<?php

namespace App\Observers;

use App\Reference;
use App\Events\ReferenceAdded;
use App\Events\ReferenceDeleted;
use App\Events\ReferenceUpdated;
use Illuminate\Broadcasting\BroadcastException;

class ReferenceObserver
{
    /**
     * Handle the Reference "saving" event.
     *
     * @param \App\Reference  $reference
     * @return void
     */
    public function saving(Reference $reference) : void {
    }

    /**
     * Handle the Reference "saved" event.
     *
     * @param \App\Reference  $reference
     * @return void
     *
     */
    public function saved(Reference $reference) : void {
        try {
            $user = auth()->user();
            if($reference->wasRecentlyCreated) {
                broadcast(new ReferenceAdded($reference, $user))->toOthers();
            } else {
                broadcast(new ReferenceUpdated($reference, $user))->toOthers();
            }
        } catch(BroadcastException $e) {
            info("BroadcastException while handling saved() event in ReferenceObserver");
        }
    }

    /**
     * Handle the Reference "deleting" event.
     *
     * @param \App\Reference  $reference
     * @return void
     */
    public function deleting(Reference $reference) : void {
        try {
            broadcast(new ReferenceDeleted($reference, $user = auth()->user()))->toOthers();
        } catch(BroadcastException $e) {
            info("BroadcastException while handling deleting() event in ReferenceObserver");
        }
    }
}
