<?php

use Illuminate\Support\Facades\Hash;
/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/



/*
| Factory for the test user
*/
$factory->defineAs(App\User::class, 'test', function () {
    return [
        'name' => "Test User",
        'email' => "test@user.com",
        'password' => Hash::make("testpassword"),
    ];
});

/*
| Factories for entries with completely random values
*/
$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => $faker->password,
    ];
});

// TODO: a concept_label should exist for each (concept x language) pair
$factory->define(App\ThConcept::class, function(Faker\Generator $faker) {
    return [
        'concept_url' => $faker->unique()->url,
        'concept_scheme' => '',
        'lasteditor' => $faker->name,
    ];
});

$factory->define(App\ThBroader::class, function(Faker\Generator $faker) {
    $broader = App\ThConcept::inRandomOrder()->first();
    if(!isset($broader)){
        factory(App\ThConcept::class, 1)->create();
        $broader = App\ThConcept::inRandomOrder()->first();
    }
    $narrower = App\ThConcept::inRandomOrder()->first();
    if(!isset($narrower)){
        factory(App\ThConcept::class, 1)->create();
        $narrower = App\ThConcept::inRandomOrder()->first();
    }
    return [
        'broader_id' => $broader->id,
        'narrower_id' => $narrower->id,
    ];
});

$factory->define(App\ThConceptLabel::class, function(Faker\Generator $faker) {
    $language = App\ThLanguage::first();
    if(!isset($language)){
        $de = new App\ThLanguage;
        $de->lasteditor     = 'postgres';
        $de->display_name   = 'Deutsch';
        $de->short_name     = 'de';
        $de->save();
        $language = $de;
    }

    $concept = App\ThConcept::inRandomOrder()->first();
    if(!isset($concept)){
        factory(App\ThConcept::class, 1)->create();
        $concept = App\ThConcept::inRandomOrder()->first();
    }

    return [
        'concept_id' => $concept->id,
        'language_id' => $language->id,
        'label' => $faker->word,
        'concept_label_type' => 1,
        'lasteditor' => $faker->name
    ];
});

$factory->define(App\ContextType::class, function(Faker\Generator $faker) {
    $concept = App\ThConcept::inRandomOrder()->first();
    if(!isset($concept)){
        factory(App\ThConcept::class, 1)->create();
        $concept = App\ThConcept::inRandomOrder()->first();
    }
    return [
        'thesaurus_url' => $concept->concept_url,
        'type' => $faker->randomElement([0,1]),
    ];
});

$factory->define(App\Attribute::class, function(Faker\Generator $faker) {
    $concept = App\ThConcept::inRandomOrder()->first();
    if(!isset($concept)){
        factory(App\ThConcept::class, 1)->create();
        $concept = App\ThConcept::inRandomOrder()->first();
    }
    $root_concept = App\ThConcept::inRandomOrder()->first();
    if(!isset($root_concept)){
        factory(App\ThConcept::class, 1)->create();
        $root_concept = App\ThConcept::inRandomOrder()->first();
    }
    return [
        'thesaurus_url' => $concept->concept_url,
        'thesaurus_root_url' => $faker->optional()->randomElement([$root_concept->concept_url]),
        'datatype' => $faker->randomElement(['string', 'list', 'date', 'stringf', 'epoch', 'string-sc', 'string-mc']),
    ];
});

$factory->define(App\ContextAttribute::class, function(Faker\Generator $faker) {
    $contextType = App\ContextType::inRandomOrder()->first();
    if(!isset($contextType)){
        factory(App\ContextType::class, 1)->create();
        $contextType = App\ContextType::inRandomOrder()->first();
    }
    $attribute = App\Attribute::inRandomOrder()->first();
    if(!isset($attribute)){
        factory(App\Attribute::class, 1)->create();
        $attribute = App\Attribute::inRandomOrder()->first();
    }
    return [
        'context_type_id' => $contextType->id,
        'attribute_id' => $attribute->id,
    ];
});

$factory->define(App\Literature::class, function(Faker\Generator $faker) {
    return [
        'author' => $faker->optional($weight=0.9)->name,
        'editor'=> $faker->optional($weight=0.1)->name,
        'title' => $faker->sentence($faker->numberBetween(3, 10)),
        'journal' => $faker->optional($weight=0.9)->word,
        'year' => $faker->optional($weight=0.9)->year,
        'pages' => $faker->optional($weight=0.9)->randomDigit(3),
        'volume' => $faker->optional($weight=0.5)->randomDigit,
        'number' => $faker->optional($weight=0.5)->randomDigit,
        'booktitle' => $faker->optional($weight=0.9)->sentence($faker->numberBetween(3, 10)),
        'publisher' => $faker->optional($weight=0.9)->name,
        'address' => $faker->optional($weight=0.9)->address,
        'type' => $faker->randomElement(['book', 'article', 'web']),
    ];
});

$factory->define(App\Geodata::class, function(Faker\Generator $faker) {
    $point = new Phaza\LaravelPostgis\Geometries\Point($faker->latitude($min = -90, $max = 90), $faker->longitude($min = -180, $max = 180));
    $deltaLat = $faker->randomFloat($min = -0.5, $max = 0.5);
    $deltaLng = $faker->randomFloat($min = -0.5, $max = 0.5);
    $point2 = new Phaza\LaravelPostgis\Geometries\Point($point->getLat() + $deltaLat, $point->getLng() + $deltaLng);
    $deltaLat = $faker->randomFloat($min = -0.5, $max = 0.5);
    $deltaLng = $faker->randomFloat($min = -0.5, $max = 0.5);
    $point3 = new Phaza\LaravelPostgis\Geometries\Point($point2->getLat() + $deltaLat, $point2->getLng() + $deltaLng);
    $deltaLat = $faker->randomFloat($min = -0.5, $max = 0.5);
    $deltaLng = $faker->randomFloat($min = -0.5, $max = 0.5);
    $point4 = new Phaza\LaravelPostgis\Geometries\Point($point3->getLat() + $deltaLat, $point3->getLng() + $deltaLng);
    $linestring = new Phaza\LaravelPostgis\Geometries\LineString([$point, $point2, $point3, $point4, $point]);
    $polygon = new Phaza\LaravelPostgis\Geometries\Polygon([$linestring]);
    return [
        'geom' => $faker->randomElement([$point, $polygon, $linestring]),
    ];
});

$factory->define(App\Context::class, function(Faker\Generator $faker) {
    $contextType = App\ContextType::inRandomOrder()->first();
    if(!isset($contextType)){
        factory(App\ContextType::class, 1)->create();
        $contextType = App\ContextType::inRandomOrder()->first();
    }
    $geodata = App\Geodata::inRandomOrder()->first();
    if(!isset($geodata)){
        $geodata = new App\Geodata();
    }
    $rootContext = App\Context::inRandomOrder()->first();
    if(!isset($rootContext)){
        $rootContext = new App\Context();
    }
    return [
        'context_type_id' => $contextType->id,
        'root_context_id' => $faker->optional()->randomElement([$rootContext->id]),
        'name' => $faker->word,
        // 'icon' => $faker->optional($weight=0.9)->randomElement([]), //TODO
        // 'color' => $faker->optional()->randomElement([$faker->hexcolor]), //TODO
        'geodata_id' => $faker->optional($weight=0.7)->randomElement([$geodata->id]),
        'lasteditor' => $faker->name,
    ];
});

$factory->define(App\Source::class, function(Faker\Generator $faker) {
    $point = new Phaza\LaravelPostgis\Geometries\Point();
    $polygon = new Phaza\LaravelPostgis\Geometries\Polygon();
    return [
        'geom' => $faker->randomElement([$point, $polygon]),
    ];
});
