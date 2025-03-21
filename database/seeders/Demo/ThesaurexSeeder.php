<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;

class ThesaurexSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $this->call(ThLanguageTableSeeder::class);
        $this->call(ThConceptTableSeeder::class);
        $this->call(ThConceptMasterTableSeeder::class);
        $this->call(ThConceptLabelTableSeeder::class);
        $this->call(ThConceptLabelMasterTableSeeder::class);
        $this->call(ThBroadersTableSeeder::class);
        $this->call(ThBroadersMasterTableSeeder::class);
        $this->call(ThConceptNotesTableSeeder::class);
        $this->call(ThConceptNotesMasterTableSeeder::class);
    }
}
