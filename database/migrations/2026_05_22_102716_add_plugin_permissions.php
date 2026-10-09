<?php

use App\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        foreach([
            'plugin_read', 'plugin_write', 'plugin_create', 'plugin_delete', 'plugin_share',
        ] as $perm) {
            $permission = new Permission();
            $permission->name = $perm;
            $permission->guard_name = 'web';
            $permission->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        foreach([
            'plugin_read', 'plugin_write', 'plugin_create', 'plugin_delete', 'plugin_share',
        ] as $perm) {
            Permission::where('name', $perm)->delete();
        }
    }
};
