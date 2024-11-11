<?php

namespace Database\Seeders;

use Database\Seeders\General\AdminUserSeeder;
use Database\Seeders\General\LanguageTableSeeder;
use Database\Seeders\General\MapSeeder;
use Database\Seeders\General\RolesPermissionsSeeder;
use Database\Seeders\General\RolesTableSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        activity()->disableLogging();

        $this->call(RolesTableSeeder::class);
        $this->call(RolesPermissionsSeeder::class);
        $this->call(AdminUserSeeder::class);
        $this->call(LanguageTableSeeder::class);
        $this->call(MapSeeder::class);

        activity()->enableLogging();
    }
}
