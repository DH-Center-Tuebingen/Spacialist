<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToListContents;

trait FilesystemMigration {
    /**
     * Check if this migration should run based on custom conditions.
     *
     * IMPORTANT: Laravel's shouldRun() is not being called when a rollback is done,
     * so in the scenario, that the migration is rolled back, and was in the migration database,
     * then it will ignore the ALLOW_FILESYSTEM_MIGRATIONS variable and run nevertheless.
     * This is only a problem in the very unlikely scenario, that the migration is run with the
     * ALLOW_FILESYSTEM_MIGRATIONS variable and then it was removed (to not allow filesystem migrations anymore on rollback).
     */
    public function shouldRun(): bool {
        // Skip if ALLOW_FILESYSTEM_MIGRATIONS not set or is set to false
        return env('ALLOW_FILESYSTEM_MIGRATIONS', false) === true;
    }

    /**
     * Safely move directories between storage disks with verification.
     * The files are moved one by one, and each file is verified after copying.
     *
     * The alternative would be copying the whole directory before deleting the source,
     * but that would require way greater storage space temporarily, which leads to
     * further problems.
     *
     * This is not a recursive operation, only the files in the given directory are moved.
     * If subdirectories exist, they are ignored.
     *
     * @param string $srcDisk
     * @param string $destDisk
     * @param string $srcDirectory
     * @param string|null $destDirectory
     * @return void
     */
    protected function safelyMoveDirectoryBetweenDisks(string $srcDisk, string $destDisk, string $srcDirectory, string $destDirectory = null): void {
        if($destDirectory === null) {
            $destDirectory = $srcDirectory;
        }

        $source = Storage::disk($srcDisk);
        $target = Storage::disk($destDisk);

        if(!$source->exists($srcDirectory)) {
            // The source directory may not exists in the first place, we just skip the migration.
            // E.g. the structure was not obsolete but the migration is run nevertheless.
            return;
        }
        if(!$target->exists($destDirectory)) {
            $target->makeDirectory($destDirectory);
        }
        try {
            $files = $source->files($srcDirectory);
            foreach($files as $file) {
                // 'throw' option is set in config/filesystems.php,
                // so a League\Flysystem\UnableToWriteFile exception is thrown
                // if something went wrong while copying.
                // We do not catch it, because the thrown exception will cancel
                // the migration anyway [VR]
                $content = $source->get($file);
                $originalHash = hash('sha256', $content);

                // When the file already exists we don't overwrite it
                // but only verify the hash. If the file is different
                // the migration will fail, as we seem to overwrite
                // an existing file with different content.
                if(!$target->exists($file)) {
                    $target->put($file, $content);
                }

                $copiedHash = hash('sha256', $target->get($file));
                if($originalHash !== $copiedHash) {
                    throw new \Exception("File verification failed for: {$file}");
                }

                $source->delete($file);
            }
        } catch(UnableToListContents $symLinkException) {
            // local plugin test env is the only case where we use sym links
            // thus we have to catch it, but there is no easy Laravel/Flysystem
            // approach to handle sym links
        }
        try {
            // Finally, we delete the source directory if it's empty.
            // Use files() and directories() separately to avoid symlink issues
            $remainingFiles = $source->files($srcDirectory);
            $remainingDirectories = $source->directories($srcDirectory);

            if(empty($remainingFiles) && empty($remainingDirectories)) {
                $source->deleteDirectory($srcDirectory);
            } else {
                throw new \Exception("Migration incomplete: some files could not be moved from $srcDisk/$srcDirectory to $destDisk/$destDirectory. Source directory was not empty and therefore not deleted!");
            }
        } catch(UnableToListContents $symLinkException) {
            // local plugin test env is the only case where we use sym links
            // thus we have to catch it, but there is no easy Laravel/Flysystem
            // approach to handle sym links
            throw new \Exception("Migration incomplete: completion could not be evaluated due to symlinked files in $srcDisk/$srcDirectory. Please verify manually if the migration was successful.");
        }
    }
}
