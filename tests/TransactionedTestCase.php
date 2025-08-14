<?php

namespace Tests;

use Illuminate\Support\Facades\DB;

/**
 * The commands did fail on Windows and left the database in an inconsistent state.
 * This class is used to run the commands in a transaction and rollback after each test. 
 */
abstract class TransactionedTestCase extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        DB::beginTransaction();
    }

    protected function tearDown(): void {
        DB::rollback();
        parent::tearDown();
    }
}