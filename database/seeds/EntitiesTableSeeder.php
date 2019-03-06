<?php

use Illuminate\Database\Seeder;

class EntitiesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('entities')->insert(array (
            0 =>
            array (
                'id' => 3,
                'name' => 'Inv. 1234',
                'entity_type_id' => 5,
                'root_entity_id' => 2,
                'created_at' => '2017-12-27 20:31:38',
                'updated_at' => '2017-12-27 20:31:38',
                'lasteditor' => 'Admin',
                'geodata_id' => NULL,
                'rank' => 1,
            ),
            1 =>
            array (
                'id' => 4,
                'name' => 'Inv. 124',
                'entity_type_id' => 5,
                'root_entity_id' => 2,
                'created_at' => '2017-12-27 20:31:54',
                'updated_at' => '2017-12-27 20:31:54',
                'lasteditor' => 'Admin',
                'geodata_id' => NULL,
                'rank' => 2,
            ),
            2 =>
            array (
                'id' => 5,
                'name' => 'Inv. 31',
                'entity_type_id' => 6,
                'root_entity_id' => 2,
                'created_at' => '2017-12-27 20:32:07',
                'updated_at' => '2017-12-27 20:32:07',
                'lasteditor' => 'Admin',
                'geodata_id' => NULL,
                'rank' => 3,
            ),
            3 =>
            array (
                'id' => 6,
                'name' => 'Aufschluss',
                'entity_type_id' => 7,
                'root_entity_id' => NULL,
                'created_at' => '2017-12-27 20:32:25',
                'updated_at' => '2017-12-31 16:02:57',
                'lasteditor' => 'Admin',
                'geodata_id' => NULL,
                'rank' => 3,
            ),
            4 =>
            array (
                'id' => 8,
                'name' => 'Fund 12',
                'entity_type_id' => 5,
                'root_entity_id' => 7,
                'created_at' => '2017-12-31 16:03:12',
                'updated_at' => '2017-12-31 16:03:12',
                'lasteditor' => 'Admin',
                'geodata_id' => NULL,
                'rank' => 1,
            ),
            5 =>
            array (
                'id' => 7,
                'name' => 'Site B',
                'entity_type_id' => 3,
                'root_entity_id' => NULL,
                'created_at' => '2017-12-31 16:02:55',
                'updated_at' => '2017-12-31 16:10:50',
                'lasteditor' => 'Admin',
                'geodata_id' => 3,
                'rank' => 2,
            ),
            6 =>
            array (
                'id' => 1,
                'name' => 'Site A',
                'entity_type_id' => 3,
                'root_entity_id' => NULL,
                'created_at' => '2017-12-20 17:10:34',
                'updated_at' => '2017-12-31 16:10:56',
                'lasteditor' => 'Admin',
                'geodata_id' => 2,
                'rank' => 1,
            ),
            7 =>
            array (
                'id' => 2,
                'name' => 'Befund 1',
                'entity_type_id' => 4,
                'root_entity_id' => 1,
                'created_at' => '2017-12-20 17:10:41',
                'updated_at' => '2017-12-31 16:13:09',
                'lasteditor' => 'Admin',
                'geodata_id' => 5,
                'rank' => 1,
            ),
        ));
    }
}
