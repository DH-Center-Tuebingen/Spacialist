<?php

use App\Traits\FilesystemMigration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    use FilesystemMigration;

    private array $dirs = ['avatars', 'bibliography', 'plugins'];

    /**
     * Run the migrations.
     */
    public function up(): void {
        foreach($this->dirs as $dir) {
            $this->safelyMoveDirectoryBetweenDisks('public', 'local', $dir);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        foreach($this->dirs as $dir) {
            $this->safelyMoveDirectoryBetweenDisks('local', 'public', $dir);
        }
    }
};