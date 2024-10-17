<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $insertData = [];
        $idCntr = 1;
        $permissionSets = sp_get_permission_groups();
        foreach($permissionSets as $group => $permSet) {
            foreach($permSet as $perm) {
                $name = $group . "_" . $perm['name'];
                $insertData[] = [
                    'id' => $idCntr++,
                    'name' => $name,
                    'display_name' => $perm['display_name'],
                    'description' => $perm['description'],
                    'guard_name' => 'web',
                    'created_at' => '2022-03-24 09:06:58',
                    'updated_at' => '2022-03-24 09:06:58',
                ];
            }
        }
        DB::table('permissions')->insert($insertData);
    }
}
