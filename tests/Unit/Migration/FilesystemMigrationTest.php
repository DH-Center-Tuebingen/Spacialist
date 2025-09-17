<?php

namespace Tests\Unit\Migration;

use App\Traits\FilesystemMigration;

use Illuminate\Database\Migrations\Migration;
// We need to use the default TestCase to avoid the Database to be refreshed.
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

/**
 *  The migrator is a very complex and file based system
 *  so we just emulate it's core functionality for these few tests.
 * (Only to test the proper calling of shouldRun() in up() and down())
 */
class Migrator {
    static function run($migration){
        $shouldRunMigration = $migration instanceof Migration
            ? $migration->shouldRun()
            : true;

        $skipped = true;
        if($shouldRunMigration) {
            $skipped = false;
            $migration->up();
        }

        return $skipped;
    }

    // Currently Laravel does not call shouldRun() on rollback (12.x)
    static function rollback($migration) {
        $migration->down();
        return false;
    }
}

// We just need empty migrations as the Migrator is handling the check
// whether the migration was run or not.
class TestMigration extends Migration {
    use FilesystemMigration;

    public function up(): void {}

    public function down(): void {}
}

class FilesystemMigrationTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();

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
        $this->cleanupTestDirectories();
    }

    protected function tearDown(): void {
        $this->cleanupTestDirectories();
        parent::tearDown();
    }

    private function cleanupTestDirectories(): void {
        $sourcePath = storage_path('testing/source');
        $targetPath = storage_path('testing/target');

        if(is_dir($sourcePath)) {
            $this->deleteDirectory($sourcePath);
        }
        if(is_dir($targetPath)) {
            $this->deleteDirectory($targetPath);
        }
    }

    private function deleteDirectory(string $path): void {
        if(!is_dir($path)) return;

        $files = glob($path . '/*');
        foreach($files as $file) {
            is_dir($file) ? $this->deleteDirectory($file) : unlink($file);
        }
        rmdir($path);
    }

    #[Test]
    public function should_not_migrate_when_environment_variable_is_false() {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=false');
        $migration = new TestMigration();
        $this->assertFalse($migration->shouldRun());
        $skipped = Migrator::run($migration);
        $this->assertTrue($skipped, 'migrate() should be skipped when shouldRun() returns false');
    }

    #[Test]
    public function should_migrate_when_environment_variable_is_true() {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');
        $migration = new TestMigration();
        $this->assertTrue($migration->shouldRun());
        $skipped = Migrator::run($migration);
        $this->assertFalse($skipped, 'migrate() should be run when shouldRun() returns true');
    }

    #[Test]
    /**
     * This is somewhat controversial, as it's only called when the migration is registered
     * but when the variable is set for rollback, it would still happen. This is due to the current
     * implementation in Laravel 12.x where shouldRun() is not called on rollback at all.
     *
    */
    public function should_rollback_when_environment_variable_is_false() {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=false');
        $migration = new TestMigration();
        $this->assertFalse($migration->shouldRun());
        $skipped = Migrator::rollback($migration);
        $this->assertFalse($skipped, 'rollback() should be run when shouldRun() returns false');
    }

    #[Test]
    public function should_rollback_when_environment_variable_is_true() {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');
        $migration = new TestMigration();
        $this->assertTrue($migration->shouldRun());
        $skipped = Migrator::rollback($migration);
        $this->assertFalse($skipped, 'rollback() should not be skipped when shouldRun() returns true');
    }

    #[Test]
    public function moves_files_between_disks_successfully() {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');

        // Create test migration instance
        $migration = new class extends Migration {
            use FilesystemMigration;

            public function up(): void {
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
        $migration->up();

        // Assert files moved correctly
        $this->assertFalse($source->exists('test_dir/file1.txt'));
        $this->assertFalse($source->exists('test_dir/file2.txt'));
        $this->assertFalse($source->exists('test_dir'));

        $this->assertTrue($target->exists('test_dir/file1.txt'));
        $this->assertTrue($target->exists('test_dir/file2.txt'));
        $this->assertEquals('content1', $target->get('test_dir/file1.txt'));
        $this->assertEquals('content2', $target->get('test_dir/file2.txt'));
    }

    #[Test]
    public function handles_non_existent_source_directory_gracefully() {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');

        $migration = new class extends Migration {
            use FilesystemMigration;

            public function up(): void {
                $this->safelyMoveDirectoryBetweenDisks('test_source', 'test_target', 'non_existent_dir');
            }
        };

        // Should not throw exception
        $migration->up();

        $source = Storage::disk('test_source');
        $this->assertFalse($source->exists('non_existent_dir'));
        $target = Storage::disk('test_target');
        $this->assertFalse($target->exists('non_existent_dir'));
    }

    /**
     * This test is not strictly necessary, but it ensures that
     * if a file with the same content already exists in the target,
     * the migration does not fail.
    */
    #[Test]
    public function does_not_fail_when_files_have_same_content() {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');

        $migration = new class extends Migration {
            use FilesystemMigration;

            public function up(): void {
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
        $migration->up();

        $this->assertEquals('same content', $target->get('test_dir/same_file.txt'));
    }

    #[Test]
    public function fails_when_existing_file_has_different_content() {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');

        $migration = new class extends Migration {
            use FilesystemMigration;

            public function up(): void {
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

        $migration->up();
    }

    #[Test]
    public function creates_target_directory_if_not_exists() {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');

        $migration = new class extends Migration {
            use FilesystemMigration;

            public function up(): void {
                $this->safelyMoveDirectoryBetweenDisks('test_source', 'test_target', 'test_dir');
            }
        };

        // Setup source only
        $source = Storage::disk('test_source');
        $target = Storage::disk('test_target');

        $source->makeDirectory('test_dir');
        $source->put('test_dir/file.txt', 'content');

        $migration->up();

        $this->assertTrue($target->exists('test_dir'));
        $this->assertTrue($target->exists('test_dir/file.txt'));
    }

    #[Test]
    public function fails_when_encountering_symlinks() {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');

        $migration = new class extends Migration {
            use FilesystemMigration;

            public function up(): void {
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
        $migration->up();

        // Verify the regular file was moved, but the symlink was ignored
        $this->assertFalse($source->exists('test_dir/file.txt'));
        $this->assertFalse($source->exists('test_dir/symlink.txt'));

        $target = Storage::disk('test_target');
        $this->assertTrue($target->exists('test_dir/file.txt'));
        $this->assertFalse($target->exists('test_dir/symlink.txt'));
    }

    #[Test]
    public function does_not_delete_source_directory_if_not_empty() {
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');

        $migration = new class extends Migration {
            use FilesystemMigration;

            public function up(): void {
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

        $migration->up();

        // Verify source directory still exists with the subdirectory
        $this->assertTrue($source->exists('test_dir'));
        $this->assertTrue($source->exists('test_dir/subdir'));
        $this->assertTrue($source->exists('test_dir/subdir/nested_file.txt'));
    }
}
