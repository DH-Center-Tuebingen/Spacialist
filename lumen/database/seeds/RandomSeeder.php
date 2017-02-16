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
        $numberSeedEntries = 20;


        $concepts = factory(App\ThConcept::class, $numberSeedEntries)->create()->each(function ($concept) {
            foreach(App\ThLanguage::get() as $language){
                factory(App\ThConceptLabel::class)->create([
                    'concept_id' => $concept->id,
                    'language_id' => $language->id,
                ]);
            }
        });

        factory(App\ThBroader::class, $numberSeedEntries)->create();
        factory(App\ContextType::class, $numberSeedEntries)->create();
        factory(App\Attribute::class, $numberSeedEntries)->create();
        factory(App\ContextAttribute::class, $numberSeedEntries)->create();
        factory(App\Literature::class, $numberSeedEntries)->create();
        factory(App\Geodata::class, $numberSeedEntries)->create();
        for($i = 0; $i < $numberSeedEntries; $i++){
            // creation needs to be done in a loop in order to have root-context relationships
            factory(App\Context::class)->create();
        }
        factory(App\Source::class, $numberSeedEntries)->create();
        factory(App\AttributeValue::class, $numberSeedEntries)->create();
    }
}
