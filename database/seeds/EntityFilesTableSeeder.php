<?php

use Illuminate\Database\Seeder;

class EntityFilesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('entity_files')->insert(array (
            0 =>
            array (
                'file_id' => 3,
                'entity_id' => 2,
                'lasteditor' => 'Admin',
            ),
            1 =>
            array (
                'file_id' => 5,
                'entity_id' => 1,
                'lasteditor' => 'Admin',
            ),
            2 =>
            array (
                'file_id' => 6,
                'entity_id' => 3,
                'lasteditor' => 'Admin',
            ),
            3 =>
            array (
                'file_id' => 4,
                'entity_id' => 3,
                'lasteditor' => 'Admin',
            ),
            4 =>
            array (
                'file_id' => 2,
                'entity_id' => 4,
                'lasteditor' => 'Admin',
            ),
        ));
    }
}
