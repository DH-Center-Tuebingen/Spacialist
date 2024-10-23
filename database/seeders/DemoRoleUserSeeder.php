<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoRoleUserSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        DB::table('model_has_roles')->insert([
            [
                'role_id' => 1,
                'model_type' => 'App\User',
                'model_id' => 2,
            ],
            [
                'role_id' => 2,
                'model_type' => 'App\User',
                'model_id' => 1,
            ],[
                'role_id' => 2,
                'model_type' => 'App\User',
                'model_id' => 2,
            ],
        ]);
    }
}
