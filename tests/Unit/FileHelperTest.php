<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\File;


test('create unique directory', function () {
    Carbon::setTestNow(Carbon::create(2025, 1, 1, 12, 30, 0));
    $tmpPath = File::getUniqueTemporaryDirectoryName();
    expect($tmpPath)->toEqual('temp_20250101123000');
});

test('create unique directory with existing name', function () {
    Storage::fake('private');
    Storage::disk('private')->makeDirectory('temp_20250101123000');
    Storage::disk('private')->assertExists('temp_20250101123000');
    Carbon::setTestNow(Carbon::create(2025, 1, 1, 12, 30, 0));
    $tmpPath = File::getUniqueTemporaryDirectoryName();
    expect($tmpPath)->toEqual('temp_20250101123000_1');
    Storage::disk('private')->assertMissing('temp_20250101123000_1');
});
