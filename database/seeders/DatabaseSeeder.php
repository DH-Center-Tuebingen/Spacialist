<?php

namespace Database\Seeders;

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
        $this->call(RolesPermissionsSeeder::class);
        $this->call(AdminUserSeeder::class);
        $this->call(LanguageTableSeeder::class);
        $this->call(MapSeeder::class);
    }
}
