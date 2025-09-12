<?php

namespace App\Listeners;

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
        if($event instanceof MigrationStarted) {
            activity()->disableLogging();
        } else if($event instanceof MigrationEnded) {
            activity()->enableLogging();
        }
    }
}
