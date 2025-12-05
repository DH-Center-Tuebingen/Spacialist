<?php

uses(\Illuminate\Foundation\Testing\TestCase::class);
use Illuminate\Support\Facades\Storage;
use \Tests\Unit\Migration\TestMigration;
use \Illuminate\Foundation\Testing\TestCase;
use \Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;


function run($migration)
{
    $shouldRunMigration = $migration instanceof Migration
        ? $migration->shouldRun()
        : true;

    $skipped = true;
    if($shouldRunMigration) {
        $skipped = false;
        up();
    }

    return $skipped;
}

// Currently Laravel does not call shouldRun() on rollback (12.x)
function rollback($migration)
{
    down();
    return false;
}

// We just need empty migrations as the Migrator is handling the check
// whether the migration was run or not.
class TestMigration extends Migration {
    uses(\App\Traits\FilesystemMigration::class);
    
    function up(): void
    {
    }

    function down(): void
    {
    }
}

class FilesystemMigrationTest extends TestCase {
    beforeEach(function () {
        // Create test storage disks for testing
        config([
            'filesystems.disks.test_source' => [
                'driver' => 'local',
                'root' => storage_path('testing/source'),
            ],
            'filesystems.disks.test_target' => [
                'driver' => 'local',
                'root' => storage_path('testing/target'),
            ],
        ]);

        // Clean up any previous test files
        cleanupTestDirectories();
    });

    afterEach(function () {
        cleanupTestDirectories();
    });

    function cleanupTestDirectories(): void
    {
        $sourcePath = storage_path('testing/source');
        $targetPath = storage_path('testing/target');

        if(is_dir($sourcePath)) {
            deleteDirectory($sourcePath);
        }
        if(is_dir($targetPath)) {
            deleteDirectory($targetPath);
        }
    }

    function deleteDirectory(string $path): void
    {
        if(!is_dir($path)) return;

        $files = glob($path . '/*');
        foreach($files as $file) {
            is_dir($file) ? deleteDirectory($file) : unlink($file);
        }
        rmdir($path);
    }

    test('should not migrate when environment variable is false', function () {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=false');
        $migration = new TestMigration();
        expect($migration->shouldRun())->toBeFalse();
        $skipped = run($migration);
        expect($skipped)->toBeTrue('migrate() should be skipped when shouldRun() returns false');
    });

    test('should migrate when environment variable is true', function () {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');
        $migration = new TestMigration();
        expect($migration->shouldRun())->toBeTrue();
        $skipped = run($migration);
        expect($skipped)->toBeFalse('migrate() should be run when shouldRun() returns true');
    });

    test('should rollback when environment variable is false', function () {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=false');
        $migration = new TestMigration();
        expect($migration->shouldRun())->toBeFalse();
        $skipped = rollback($migration);
        expect($skipped)->toBeFalse('rollback() should be run when shouldRun() returns false');
    });

    test('should rollback when environment variable is true', function () {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');
        $migration = new TestMigration();
        expect($migration->shouldRun())->toBeTrue();
        $skipped = rollback($migration);
        expect($skipped)->toBeFalse('rollback() should not be skipped when shouldRun() returns true');
    });

    test('moves files between disks successfully', function () {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');

        // Create test migration instance
        $migration = new class extends Migration {
            uses(\App\Traits\FilesystemMigration::class);
            
            function up(): void
            {
                $this->safelyMoveDirectoryBetweenDisks('test_source', 'test_target', 'test_dir');
            }
        };
        // Setup test files
        $source = Storage::disk('test_source');
        $target = Storage::disk('test_target');

        $source->makeDirectory('test_dir');
        $source->put('test_dir/file1.txt', 'content1');
        $source->put('test_dir/file2.txt', 'content2');

        // Execute migration
        up();

        // Assert files moved correctly
        expect($source->exists('test_dir/file1.txt'))->toBeFalse();
        expect($source->exists('test_dir/file2.txt'))->toBeFalse();
        expect($source->exists('test_dir'))->toBeFalse();

        expect($target->exists('test_dir/file1.txt'))->toBeTrue();
        expect($target->exists('test_dir/file2.txt'))->toBeTrue();
        expect($target->get('test_dir/file1.txt'))->toEqual('content1');
        expect($target->get('test_dir/file2.txt'))->toEqual('content2');
    });

    test('handles non existent source directory gracefully', function () {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');

        $migration = new class extends Migration {
            uses(\App\Traits\FilesystemMigration::class);
            
            function up(): void
            {
                $this->safelyMoveDirectoryBetweenDisks('test_source', 'test_target', 'non_existent_dir');
            }
        };
        // Should not throw exception
        up();

        $source = Storage::disk('test_source');
        expect($source->exists('non_existent_dir'))->toBeFalse();
        $target = Storage::disk('test_target');
        expect($target->exists('non_existent_dir'))->toBeFalse();
    });

    test('does not fail when files have same content', function () {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');

        $migration = new class extends Migration {
            uses(\App\Traits\FilesystemMigration::class);
            
            function up(): void
            {
                $this->safelyMoveDirectoryBetweenDisks('test_source', 'test_target', 'test_dir');
            }
        };
        // Setup files in both locations with same content
        $source = Storage::disk('test_source');
        $target = Storage::disk('test_target');

        $source->makeDirectory('test_dir');
        $target->makeDirectory('test_dir');

        $source->put('test_dir/same_file.txt', 'same content');
        $target->put('test_dir/same_file.txt', 'same content');

        // Should not throw exception
        up();

        expect($target->get('test_dir/same_file.txt'))->toEqual('same content');
    });

    test('fails when existing file has different content', function () {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');

        $migration = new class extends Migration {
            uses(\App\Traits\FilesystemMigration::class);
            
            function up(): void
            {
                $this->safelyMoveDirectoryBetweenDisks('test_source', 'test_target', 'test_dir');
            }
        };
        // Setup files with different content
        $source = Storage::disk('test_source');
        $target = Storage::disk('test_target');

        $source->makeDirectory('test_dir');
        $target->makeDirectory('test_dir');

        $source->put('test_dir/conflict_file.txt', 'source content');
        $target->put('test_dir/conflict_file.txt', 'different target content');

        // Should throw exception due to hash mismatch
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File verification failed for: test_dir/conflict_file.txt');

        up();
    });

    test('creates target directory if not exists', function () {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');

        $migration = new class extends Migration {
            uses(\App\Traits\FilesystemMigration::class);
            
            function up(): void
            {
                $this->safelyMoveDirectoryBetweenDisks('test_source', 'test_target', 'test_dir');
            }
        };
        // Setup source only
        $source = Storage::disk('test_source');
        $target = Storage::disk('test_target');

        $source->makeDirectory('test_dir');
        $source->put('test_dir/file.txt', 'content');

        up();

        expect($target->exists('test_dir'))->toBeTrue();
        expect($target->exists('test_dir/file.txt'))->toBeTrue();
    });

    test('fails when encountering symlinks', function () {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');

        $migration = new class extends Migration {
            uses(\App\Traits\FilesystemMigration::class);
            
            function up(): void
            {
                $this->safelyMoveDirectoryBetweenDisks('test_source', 'test_target', 'test_dir');
            }
        };
        // Setup source with a symlink
        $source = Storage::disk('test_source');
        $source->makeDirectory('test_dir');
        file_put_contents(storage_path('testing/source/test_dir/file.txt'), 'content');
        symlink(storage_path('testing/source/test_dir/file.txt'), storage_path('testing/source/test_dir/symlink.txt'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Migration incomplete: completion could not be evaluated due to symlinked files in test_source/test_dir. Please verify manually if the migration was successful.');

        // Should not throw exception, as symlinks are caught and ignored
        up();

        // Verify the regular file was moved, but the symlink was ignored
        expect($source->exists('test_dir/file.txt'))->toBeFalse();
        expect($source->exists('test_dir/symlink.txt'))->toBeFalse();

        $target = Storage::disk('test_target');
        expect($target->exists('test_dir/file.txt'))->toBeTrue();
        expect($target->exists('test_dir/symlink.txt'))->toBeFalse();
    });

    test('does not delete source directory if not empty', function () {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');

        $migration = new class extends Migration {
            uses(\App\Traits\FilesystemMigration::class);
            
            function up(): void
            {
                $this->safelyMoveDirectoryBetweenDisks('test_source', 'test_target', 'test_dir');
            }
        };
        // Setup source with one file and one subdirectory
        $source = Storage::disk('test_source');
        $source->makeDirectory('test_dir');
        $source->put('test_dir/file.txt', 'content');
        $source->makeDirectory('test_dir/subdir');
        $source->put('test_dir/subdir/nested_file.txt', 'nested content');

        // Should throw exception because source directory won't be empty after moving files
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Migration incomplete: some files could not be moved from test_source/test_dir to test_target/test_dir. Source directory was not empty and therefore not deleted!');

        up();

        // Verify source directory still exists with the subdirectory
        expect($source->exists('test_dir'))->toBeTrue();
        expect($source->exists('test_dir/subdir'))->toBeTrue();
        expect($source->exists('test_dir/subdir/nested_file.txt'))->toBeTrue();
    });
}
