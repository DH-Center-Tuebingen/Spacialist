<?php

namespace App\Observers;

use App\Bibliography;
use App\Events\BibliographyCreated;
use App\Events\BibliographyDeleted;
use App\Events\BibliographyUpdated;
use Illuminate\Broadcasting\BroadcastException;

class BibliographyObserver
{
    /**
     * Handle the Bibliography "saving" event.
     *
     * @param \App\Bibliography  $bibliography
     * @return void
     */
    public function saving(Bibliography $bibliography) : void {
    }

    /**
     * Handle the Bibliography "saved" event.
     *
     * @param \App\Bibliography  $bibliography
     * @return void
     *
     */
    public function saved(Bibliography $bibliography) : void {
        try {
            $user = auth()->user();
            if($bibliography->wasRecentlyCreated) {
                broadcast(new BibliographyCreated($bibliography, $user))->toOthers();
            } else {
                broadcast(new BibliographyUpdated($bibliography, $user))->toOthers();
            }
        } catch(BroadcastException $e) {
            if(env('APP_DEBUG')) {
                info("BroadcastException while handling saved() event in BibliographyObserver");
            }
        }
    }

    /**
     * Handle the Bibliography "deleting" event.
     *
     * @param \App\Bibliography  $bibliography
     * @return void
     */
    public function deleting(Bibliography $bibliography) : void {
        try {
            broadcast(new BibliographyDeleted($bibliography, $user = auth()->user()))->toOthers();
        } catch(BroadcastException $e) {
            if(env('APP_DEBUG')) {
                info("BroadcastException while handling deleting() event in BibliographyObserver");
            }
        }
    }
}
