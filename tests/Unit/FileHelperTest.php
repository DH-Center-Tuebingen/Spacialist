<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;


use App\File;

class FileHelperTest extends TestCase
{
    /**
     * Test method to get unique temporary directory name
     *
     * @return void
     */
    public function testCreateUniqueDirectory()
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 12, 30, 0));
        $tmpPath = File::getUniqueTemporaryDirectoryName();
        $this->assertEquals('temp_20250101123000', $tmpPath);
    }
    /**
     * Test method to get unique temporary directory name with name collision
     *
     * @return void
     */
    public function testCreateUniqueDirectoryWithExistingName()
    {
        Storage::fake('private');
        Storage::disk('private')->makeDirectory('temp_20250101123000');
        Storage::disk('private')->assertExists('temp_20250101123000');
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 12, 30, 0));
        $tmpPath = File::getUniqueTemporaryDirectoryName();
        $this->assertEquals('temp_20250101123000_1', $tmpPath);
        Storage::disk('private')->assertMissing('temp_20250101123000_1');
    }
}
