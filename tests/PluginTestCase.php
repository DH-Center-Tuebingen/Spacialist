<?php

namespace Tests;

use Illuminate\Support\Facades\Storage;


abstract class PluginTestCase extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('private');
    }

}