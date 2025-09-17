<?php

namespace App\Listeners;

use App\Traits\EnableLogging;
use Illuminate\Database\Events\MigrationStarted;
use Illuminate\Database\Events\MigrationEnded;

class DisableMigrationLogging {
    /**
     * Create the event listener.
     */
    public function __construct() {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MigrationStarted | MigrationEnded $event): void {
        // Do not disbale logging if EnableLogging trait is used
        if(in_array(EnableLogging::class, class_uses($event->migration))) {
            return;
        }
        if($event instanceof MigrationStarted) {
            activity()->disableLogging();
        } else if($event instanceof MigrationEnded) {
            activity()->enableLogging();
        }
    }
}
