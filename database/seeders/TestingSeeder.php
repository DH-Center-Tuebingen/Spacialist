<?php

namespace Database\Seeders;

use Database\Seeders\Demo\AttributeValuesTableSeeder;
use Database\Seeders\Demo\AttributesTableSeeder;
use Database\Seeders\Demo\EntitiesTableSeeder;
use Database\Seeders\Demo\EntityAttributesTableSeeder;
use Database\Seeders\Demo\EntityTypeRelationsTableSeeder;
use Database\Seeders\Demo\EntityTypesTableSeeder;
use Database\Seeders\Demo\ReferencesTableSeeder;
use Database\Seeders\Demo\RoleUserTableSeeder;
use Database\Seeders\Demo\ThesaurexSeeder;
use Database\Seeders\Demo\UsersTableSeeder;
use Database\Seeders\General\RolesPermissionsSeeder;
use Database\Seeders\General\RolesTableSeeder;
use Database\Seeders\Testing\BibliographyTableSeeder;
use Database\Seeders\Testing\CommentsSeeder;
use Database\Seeders\Testing\UserSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestingSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        activity()->disableLogging();

        $this->call(UsersTableSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(ThesaurexSeeder::class);

        $this->call(RolesTableSeeder::class);
        $this->call(RolesPermissionsSeeder::class);
        $this->call(RoleUserTableSeeder::class);

        $this->call(BibliographyTableSeeder::class);
        $this->call(AttributesTableSeeder::class);
        $this->call(EntityTypesTableSeeder::class);
        $this->call(EntitiesTableSeeder::class);
        $this->call(EntityTypeRelationsTableSeeder::class);
        $this->call(EntityAttributesTableSeeder::class);
        $this->call(AttributeValuesTableSeeder::class);
        $this->call(ReferencesTableSeeder::class);
        $this->call(CommentsSeeder::class);

        // Set different root for tags
        DB::table('preferences')
            ->where('label', 'prefs.tag-root')
            ->update(['default_value' => '{"uri": "https://spacialist.escience.uni-tuebingen.de/<user-project>/eigenschaften#20171220100251"}']);

        if(DB::connection()->getDriverName() === 'pgsql') {
            $this->call(FixSequencesSeeder::class);
        }

        activity()->enableLogging();
    }
}
