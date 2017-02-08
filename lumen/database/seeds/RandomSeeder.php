<?php

use Illuminate\Database\Seeder;

class RandomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $numberSeedEntries = 10;


        factory(App\ThConcept::class, $numberSeedEntries)->create();
        factory(App\ThBroader::class, $numberSeedEntries)->create();
        factory(App\ThConceptLabel::class, $numberSeedEntries)->create();
        factory(App\ContextType::class, $numberSeedEntries)->create();
        factory(App\Attribute::class, $numberSeedEntries)->create();
        factory(App\ContextAttribute::class, $numberSeedEntries)->create();
        factory(App\Literature::class, $numberSeedEntries)->create();
        factory(App\Geodata::class, $numberSeedEntries)->create();
        for($i = 0; $i < $numberSeedEntries; $i++){
            // creation needs to be done in a loop in order to have root-context relationships
            factory(App\Context::class)->create();
        }
    }
}
