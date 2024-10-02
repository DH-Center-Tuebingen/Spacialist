<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        info("Running TestSeeder");
        activity()->disableLogging();
        $this->call(DatabaseSeeder::class);
        $this->call(DemoSeeder::class);
        activity()->enableLogging();
    }
}