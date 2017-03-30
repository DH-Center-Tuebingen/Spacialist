<?php

use Illuminate\Support\Facades\Hash;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\LineString;
use Phaza\LaravelPostgis\Geometries\Polygon;

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
$factory->defineAs(App\User::class, 'guest', function () {
    return [
        'name' => "Guest User",
        'email' => "udontneedtoseehisidentification@rebels.tld",
        'password' => Hash::make("thesearentthedroidsuarelookingfor"),
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
        $broader = factory(App\ThConcept::class)->create();
    }
    $narrower = App\ThConcept::inRandomOrder()->first();
    if(!isset($narrower)){
        $narrower = factory(App\ThConcept::class)->create();
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
        $concept = factory(App\ThConcept::class)->create();
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
        $concept = factory(App\ThConcept::class)->create();
    }
    return [
        'thesaurus_url' => $concept->concept_url,
        'type' => $faker->randomElement([0,1]),
    ];
});

$factory->define(App\Attribute::class, function(Faker\Generator $faker) {
    $concept = App\ThConcept::inRandomOrder()->first();
    if(!isset($concept)){
        $concept = factory(App\ThConcept::class)->create();
    }
    $root_concept = App\ThConcept::inRandomOrder()->first();
    if(!isset($root_concept)){
        $root_concept = factory(App\ThConcept::class)->create();
    }
    return [
        'thesaurus_url' => $concept->concept_url,
        'thesaurus_root_url' => $faker->optional()->randomElement([$root_concept->concept_url]),
        'datatype' => $faker->randomElement(['string', 'list', 'date', 'stringf', 'epoch', 'string-sc', 'string-mc', 'dimension']),
    ];
});

$factory->define(App\ContextAttribute::class, function(Faker\Generator $faker) {
    $contextType = App\ContextType::inRandomOrder()->first();
    if(!isset($contextType)){
        $contextType = factory(App\ContextType::class)->create();
    }
    $attribute = App\Attribute::inRandomOrder()->first();
    if(!isset($attribute)){
        $attribute = factory(App\Attribute::class)->create();
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
    $lat = $faker->latitude($min = 47, $max = 56);
    $lng = $faker->longitude($min = 6, $max = 15);

    switch ($faker->randomElement(['point', 'polygon', 'linestring'])) {
    case 'point':
        $geom = new Point($lat, $lng);
        break;
    case 'polygon':
        for($i = 0; $i < $faker->numberBetween($min = 3, $max = 6); $i++){
            $deltaLat = $faker->randomFloat($nbMaxDecimals = NULL, $min = 0, $max = 0.5);
            $deltaLng = $faker->randomFloat($nbMaxDecimals = NULL, $min = 0, $max = 0.5);
            $points[$i] = new Point($lat + $deltaLat, $lng + $deltaLng);
        }
        $points[] = $points[0]; // polygon must be closed
        $geom = new Polygon([new LineString($points)]);
        break;
    case 'linestring':
        for($i = 0; $i < $faker->numberBetween($min = 3, $max = 6); $i++){
            $deltaLat = $faker->randomFloat($nbMaxDecimals = NULL, $min = 0, $max = 0.5);
            $deltaLng = $faker->randomFloat($nbMaxDecimals = NULL, $min = 0, $max = 0.5);
            $points[$i] = new Point($lat + $deltaLat, $lng + $deltaLng);
        }
        $geom = new LineString($points);
        break;
    }

    return [
        'geom' => $geom,
        'color' => $faker->randomElement([$faker->hexcolor]),
    ];
});

$factory->define(App\Context::class, function(Faker\Generator $faker) {
    $contextType = App\ContextType::inRandomOrder()->first();
    if(!isset($contextType)){
        $contextType = factory(App\ContextType::class)->create();
    }
    $geodata = factory(App\Geodata::class)->create();

    $rootContext = App\Context::inRandomOrder()->first();
    if(!isset($rootContext)){
        // if there is no entry in the Contexts table, do not recursively call this factory
        $rootContext = new App\Context();
    }
    return [
        'context_type_id' => $contextType->id,
        'root_context_id' => $faker->optional()->randomElement([$rootContext->id]),
        'name' => $faker->word,
        'geodata_id' => $geodata->id,
        'lasteditor' => $faker->name,
    ];
});

$factory->define(App\Source::class, function(Faker\Generator $faker) {
    $context = App\Context::inRandomOrder()->first();
    if(!isset($context)){
        $context = factory(App\Context::class)->create();
    }
    $attribute = App\Attribute::inRandomOrder()->first();
    if(!isset($attribute)){
        $attribute = factory(App\Attribute::class)->create();
    }
    $literature = App\Literature::inRandomOrder()->first();
    if(!isset($literature)){
        $literature = factory(App\Literature::class)->create();
    }
    return [
        'context_id' => $context->id,
        'attribute_id' => $attribute->id,
        'literature_id' => $literature->id,
        'description' => $faker->sentence($nbWords = 6, $variableNbWords = true),
        'lasteditor' => $faker->name,
    ];
});


$factory->define(App\AttributeValue::class, function(Faker\Generator $faker) {
    $context = App\Context::inRandomOrder()->first();
    if(!isset($context)){
        $context = factory(App\Context::class)->create();
    }
    $attribute = App\Attribute::inRandomOrder()->first();
    if(!isset($attribute)){
        $attribute = factory(App\Attribute::class)->create();
    }
    $context_val = App\Context::inRandomOrder()->first();
    if(!isset($context_val)){
        $context_val = factory(App\Context::class)->create();
    }
    return [
        'context_id' => $context->id,
        'attribute_id' => $attribute->id,
        'context_val' => $faker->optional()->randomElement([$context_val->id]),
        'str_val' => $faker->optional()->sentence($nbWords = 6, $variableNbWords = true),
        'int_val' => $faker->optional()->randomDigit,
        'dbl_val' => $faker->optional()->randomFloat($nbMaxDecimals = NULL, $min = 0, $max = 42),
        'dt_val' => $faker->optional()->dateTime(),
        'possibility' => $faker->numberBetween($min = 0, $max = 100),
        'thesaurus_val' => $faker->optional()->randomElement([App\ThConcept::inRandomOrder()->first()->value('concept_url')]),
        // 'json_val' => TODO
        'lasteditor' => $faker->name,
    ];
});

//TODO seed photos?
$factory->define(App\Photo::class, function(Faker\Generator $faker) {
    return [
        'name' => '',
        'modified' => $faker->dateTime(),
        'cameraname' => '',
        'photographer_id' => $faker->randomDigit,
        'created' => $faker->dateTime(),
        'thumb' => '',
        'orientation' => $faker->randomElement([0,1]),
        'copyright' => '',
        'description' => $faker->sentence($nbWords = 6, $variableNbWords = true),
    ];
});
