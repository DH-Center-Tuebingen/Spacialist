<?php

namespace Tests\Unit\Migration;

// We need to use the default TestCase to avoid the Database to be refreshed.
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;

class MakeFilesPrivateMigrationTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp();

        // Set up test environment
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=true');

        // Reconfigure the existing 'public' and 'local' disks to use test directories
        config([
            'filesystems.disks.public' => [
                'driver' => 'local',
                'root' => storage_path('testing/make_private_migration/public'),
                'url' => './storage',
                'visibility' => 'public',
                'throw' => true,
            ],
            'filesystems.disks.local' => [
                'driver' => 'local',
                'root' => storage_path('testing/make_private_migration/local'),
                'throw' => true,
            ],
        ]);

        // Clean up any existing test files
        $this->cleanupTestFiles();
    }

    protected function tearDown(): void {
        $this->cleanupTestFiles();
        putenv('ALLOW_FILESYSTEM_MIGRATIONS=false');
        parent::tearDown();
    }

    private function cleanupTestFiles(): void {
        $testPublicPath = storage_path('testing/make_private_migration/public');
        $testLocalPath = storage_path('testing/make_private_migration/local');

        if(is_dir($testPublicPath)) {
            $this->deleteDirectory($testPublicPath);
        }
        if(is_dir($testLocalPath)) {
            $this->deleteDirectory($testLocalPath);
        }
    }

    private function deleteDirectory(string $path): void {
        if(!is_dir($path)) {
            return;
        }

        $files = array_diff(scandir($path), ['.', '..']);
        foreach($files as $file) {
            $fullPath = $path . DIRECTORY_SEPARATOR . $file;
            if(is_dir($fullPath)) {
                $this->deleteDirectory($fullPath);
            } else {
                unlink($fullPath);
            }
        }
        rmdir($path);
    }

    #[Test]
    public function moves_files_from_public_to_private_storage() {
        // Get the actual migration
        $migration = require database_path('migrations/2024_12_04_073048_make_files_private.php');

        // Setup test data in public storage
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        $public->makeDirectory('avatars');
        $public->makeDirectory('bibliography');
        $public->put('avatars/user1.jpg', 'fake avatar content');
        $public->put('bibliography/paper1.pdf', 'fake pdf content');

        // Run the migration
        $migration->up();

        // Assert files moved to private storage
        $this->assertFalse($public->exists('avatars/user1.jpg'));
        $this->assertFalse($public->exists('bibliography/paper1.pdf'));
        $this->assertFalse($public->exists('avatars'));
        $this->assertFalse($public->exists('bibliography'));

        $this->assertTrue($private->exists('avatars/user1.jpg'));
        $this->assertTrue($private->exists('bibliography/paper1.pdf'));
        $this->assertEquals('fake avatar content', $private->get('avatars/user1.jpg'));
        $this->assertEquals('fake pdf content', $private->get('bibliography/paper1.pdf'));
    }

    #[Test]
    public function can_rollback_migration() {
        // Get the actual migration
        $migration = require database_path('migrations/2024_12_04_073048_make_files_private.php');

        // Setup files in private storage (simulating after migration)
        $private = Storage::disk('local');
        $public = Storage::disk('public');

        $private->makeDirectory('avatars');
        $private->put('avatars/user1.jpg', 'avatar content');

        // Run rollback
        $migration->down();

        // Assert files moved back to public storage
        $this->assertFalse($private->exists('avatars/user1.jpg'));
        $this->assertFalse($private->exists('avatars'));

        $this->assertTrue($public->exists('avatars/user1.jpg'));
        $this->assertEquals('avatar content', $public->get('avatars/user1.jpg'));
    }

    #[Test]
    public function skips_migration_when_no_directories_exist() {
        // Get the actual migration
        $migration = require database_path('migrations/2024_12_04_073048_make_files_private.php');

        // Ensure no directories exist
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        $this->assertFalse($public->exists('avatars'));
        $this->assertFalse($public->exists('bibliography'));
        $this->assertFalse($public->exists('plugins'));

        // Should not throw any exceptions
        $migration->up();

        // Nothing should be created
        $this->assertFalse($private->exists('avatars'));
        $this->assertFalse($private->exists('bibliography'));
        $this->assertFalse($private->exists('plugins'));
    }

    #[Test]
    public function fail_when_directory_has_subdirectories() {
        // Get the actual migration
        $migration = require database_path('migrations/2024_12_04_073048_make_files_private.php');

        // Ensure no directories exist
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        $public->makeDirectory('avatars');
        $public->makeDirectory('bibliography');
        $public->makeDirectory('plugins');

        $public->put('avatars/user1.jpg', 'fake avatar content');
        $public->put('bibliography/paper1.pdf', 'fake pdf content');
        $public->put('plugins/plugin1.php', 'fake plugin content');

        $public->makeDirectory('avatars/subdir');
        $public->makeDirectory('bibliography/subdir');
        $public->makeDirectory('plugins/subdir');
        $public->put('avatars/subdir/user1.jpg', 'fake avatar content');
        $public->put('bibliography/subdir/paper1.pdf', 'fake pdf content');
        $public->put('plugins/subdir/plugin1.php', 'fake plugin content');

        // Run the migration and assert it throws an exception with the expected message
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Migration incomplete: some files could not be moved from public\/avatars to local\/avatars\. Source directory was not empty and therefore not deleted!/');

        $migration->up();

        // Assert files moved to private storage
        $this->assertFalse($public->exists('avatars/user1.jpg'));
        $this->assertFalse($public->exists('bibliography/paper1.pdf'));
        $this->assertFalse($public->exists('plugins/plugin1.php'));

        $this->assertTrue($private->exists('avatars/user1.jpg'));
        $this->assertTrue($private->exists('bibliography/paper1.pdf'));
        $this->assertTrue($private->exists('plugins/plugin1.php'));

        $this->assertFalse($public->exists('avatars/subdir/user1.jpg'));
        $this->assertFalse($public->exists('bibliography/subdir/paper1.pdf'));
        $this->assertFalse($public->exists('plugins/subdir/plugin1.php'));
        $this->assertTrue($public->exists('avatars/subdir'));
        $this->assertTrue($public->exists('bibliography/subdir'));
        $this->assertTrue($public->exists('plugins/subdir'));

        $this->assertTrue($private->exists('avatars/subdir/user1.jpg'));
        $this->assertTrue($private->exists('bibliography/subdir/paper1.pdf'));
        $this->assertTrue($private->exists('plugins/subdir/plugin1.php'));
        $this->assertEquals('fake avatar content', $private->get('avatars/subdir/user1.jpg'));
        $this->assertEquals('fake pdf content', $private->get('bibliography/subdir/paper1.pdf'));
        $this->assertEquals('fake plugin content', $private->get('plugins/subdir/plugin1.php'));
    }
}
