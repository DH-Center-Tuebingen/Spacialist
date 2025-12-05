<?php

uses(\Illuminate\Foundation\Testing\TestCase::class);
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;


beforeEach(function () {
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
    cleanupTestFiles();
});

afterEach(function () {
    cleanupTestFiles();
    putenv('ALLOW_FILESYSTEM_MIGRATIONS=false');
});

function cleanupTestFiles(): void
{
    $testPublicPath = storage_path('testing/make_private_migration/public');
    $testLocalPath = storage_path('testing/make_private_migration/local');

    if(is_dir($testPublicPath)) {
        deleteDirectory($testPublicPath);
    }
    if(is_dir($testLocalPath)) {
        deleteDirectory($testLocalPath);
    }
}

function deleteDirectory(string $path): void
{
    if(!is_dir($path)) {
        return;
    }

    $files = array_diff(scandir($path), ['.', '..']);
    foreach($files as $file) {
        $fullPath = $path . DIRECTORY_SEPARATOR . $file;
        if(is_dir($fullPath)) {
            deleteDirectory($fullPath);
        } else {
            unlink($fullPath);
        }
    }
    rmdir($path);
}

test('moves files from public to private storage', function () {
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
    expect($public->exists('avatars/user1.jpg'))->toBeFalse();
    expect($public->exists('bibliography/paper1.pdf'))->toBeFalse();
    expect($public->exists('avatars'))->toBeFalse();
    expect($public->exists('bibliography'))->toBeFalse();

    expect($private->exists('avatars/user1.jpg'))->toBeTrue();
    expect($private->exists('bibliography/paper1.pdf'))->toBeTrue();
    expect($private->get('avatars/user1.jpg'))->toEqual('fake avatar content');
    expect($private->get('bibliography/paper1.pdf'))->toEqual('fake pdf content');
});

test('can rollback migration', function () {
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
    expect($private->exists('avatars/user1.jpg'))->toBeFalse();
    expect($private->exists('avatars'))->toBeFalse();

    expect($public->exists('avatars/user1.jpg'))->toBeTrue();
    expect($public->get('avatars/user1.jpg'))->toEqual('avatar content');
});

test('skips migration when no directories exist', function () {
    // Get the actual migration
    $migration = require database_path('migrations/2024_12_04_073048_make_files_private.php');

    // Ensure no directories exist
    $public = Storage::disk('public');
    $private = Storage::disk('local');

    expect($public->exists('avatars'))->toBeFalse();
    expect($public->exists('bibliography'))->toBeFalse();
    expect($public->exists('plugins'))->toBeFalse();

    // Should not throw any exceptions
    $migration->up();

    // Nothing should be created
    expect($private->exists('avatars'))->toBeFalse();
    expect($private->exists('bibliography'))->toBeFalse();
    expect($private->exists('plugins'))->toBeFalse();
});

test('fail when directory has subdirectories', function () {
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
    expect($public->exists('avatars/user1.jpg'))->toBeFalse();
    expect($public->exists('bibliography/paper1.pdf'))->toBeFalse();
    expect($public->exists('plugins/plugin1.php'))->toBeFalse();

    expect($private->exists('avatars/user1.jpg'))->toBeTrue();
    expect($private->exists('bibliography/paper1.pdf'))->toBeTrue();
    expect($private->exists('plugins/plugin1.php'))->toBeTrue();

    expect($public->exists('avatars/subdir/user1.jpg'))->toBeFalse();
    expect($public->exists('bibliography/subdir/paper1.pdf'))->toBeFalse();
    expect($public->exists('plugins/subdir/plugin1.php'))->toBeFalse();
    expect($public->exists('avatars/subdir'))->toBeTrue();
    expect($public->exists('bibliography/subdir'))->toBeTrue();
    expect($public->exists('plugins/subdir'))->toBeTrue();

    expect($private->exists('avatars/subdir/user1.jpg'))->toBeTrue();
    expect($private->exists('bibliography/subdir/paper1.pdf'))->toBeTrue();
    expect($private->exists('plugins/subdir/plugin1.php'))->toBeTrue();
    expect($private->get('avatars/subdir/user1.jpg'))->toEqual('fake avatar content');
    expect($private->get('bibliography/subdir/paper1.pdf'))->toEqual('fake pdf content');
    expect($private->get('plugins/subdir/plugin1.php'))->toEqual('fake plugin content');
});
