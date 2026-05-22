<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach([
            'plugin_read', 'plugin_write', 'plugin_create', 'plugin_delete', 'plugin_share',
        ] as $perm) {
            DB::table('permissions')->insert([
                'name' => $perm,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach([
            'plugin_read', 'plugin_write', 'plugin_create', 'plugin_delete', 'plugin_share',
        ] as $perm) {
            DB::table('permissions')->where('name', $perm)->delete();
        }
    }
};
