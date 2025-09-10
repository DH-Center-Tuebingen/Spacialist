<?php

namespace App\Migration;

use Illuminate\Database\Migrations\Migration as LaravelMigration;

/**
 * Base Migration class for the application.
 * This class extends Laravel's Migration class and provides a structured way to define
 * migrations with `migrate` and `rollback` methods.
 * 
 * Currently it only disables activity logging during migrations to avoid cluttering logs.
 */
abstract class Migration extends LaravelMigration
{
    public final function up(): void {
        activity()->disableLogging();
        try {
            $this->migrate();
            activity()->enableLogging();
        }catch(\Exception $e) {
            activity()->enableLogging();
            throw $e;
        }
    }

    public final function down(): void {
        activity()->disableLogging();
        try {
            $this->rollback();
            activity()->enableLogging();
        }catch(\Exception $e) {
            activity()->enableLogging();
            throw $e;
        }
    }

    /**
     * Runs this application specific migration's `up` function.
     */
    abstract protected function migrate(): void;

    /**
     * Runs this application specific migration's `down` function.
     */
    abstract protected function rollback(): void;
}
