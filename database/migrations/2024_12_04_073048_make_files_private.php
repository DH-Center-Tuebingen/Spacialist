<?php

use App\Migration\FilesystemMigration;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToListContents;

return new class extends FilesystemMigration
{
    private array $dirs = ['avatars', 'bibliography', 'plugins'];

    /**
     * Run the migrations.
     */
    public function migrate(): void
    {
        foreach($this->dirs as $dir) {
            $this->safelyMoveDirectoryBetweenDisks('public', 'local', $dir);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function rollback(): void
    {
        foreach($this->dirs as $dir) {
            $this->safelyMoveDirectoryBetweenDisks('local', 'public', $dir);
        }
    }
};