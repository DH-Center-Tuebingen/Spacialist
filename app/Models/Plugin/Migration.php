<?php

namespace App\Models\Plugin;

use Illuminate\Database\Eloquent\Model;


/**
 * For normal migrations Laravel is using the migrations table to track which migrations have been run.
 * However, since plugins can be installed and uninstalled at any time, we need a separate table to track
 * the migrations for each plugin.
 * 
 * This is necesarry to ensure when a plugin is updated, what migrations have already been run and what
 * still need to be run.
 */
class Migration extends Model
{

    protected $table = 'plugin_service_migrations';

    protected $fillable = [
        'plugin_id',
        'migration',
        'batch'
    ];

    public static function getNextBatchNumber(): int {
        return static::max('batch') + 1;
    }
    
}
