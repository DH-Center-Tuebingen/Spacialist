<?php

namespace Tests\Unit\Migration;

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

function cleanuPrivateMigrationFiles(): void
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
