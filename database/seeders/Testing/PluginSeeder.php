<?php

namespace Database\Seeders\Testing;

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PluginSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run() {
        \DB::table('plugins')->insert(array(
           0 => array(
                'id' => 1,
                'name' => 'FooPlugin',
                'version' => '1.0.0',
                'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                'update_available' => '2.2.0',
                'installed_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2020-02-20 22:44:12', 'UTC'),
                'created_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2020-01-10 11:22:34', 'UTC'),
                'updated_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2020-03-30 03:33:45', 'UTC')
            ),
            1 => array(
                'id' => 2,
                'name' => 'BarPlugin',
                'version' => '2.1.0',
                'uuid' => '123e4567-e89b-12d3-a456-426614174002',
                'update_available' => null,
                'installed_at' => null,
                'created_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2020-04-14 04:40:04', 'UTC'),
                'updated_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2020-06-16 06:36:27', 'UTC'),
            ),
            2 => array(
                'id' => 3,
                'name' => 'ScopePlugin',
                'version' => '3.2.0',
                'uuid' => '123e4567-e89b-12d3-a456-426614174004',
                'update_available' => null,
                'installed_at' => null,
                'created_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-01 08:00:00', 'UTC'),
                'updated_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-01 08:00:00', 'UTC'),
            ),
        ));

        // Reset PostgreSQL sequence to continue from the highest ID
        \DB::statement("SELECT setval(pg_get_serial_sequence('plugins', 'id'), (SELECT MAX(id) FROM plugins))");
    }
}