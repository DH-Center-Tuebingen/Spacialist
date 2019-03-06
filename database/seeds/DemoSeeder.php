<?php

use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ThLanguageTableSeeder::class);
        $this->call(ThConceptTableSeeder::class);
        $this->call(ThConceptMasterTableSeeder::class);
        $this->call(ThConceptLabelTableSeeder::class);
        $this->call(ThConceptLabelMasterTableSeeder::class);
        $this->call(ThBroadersTableSeeder::class);
        $this->call(ThBroadersMasterTableSeeder::class);
        $this->call(ThConceptNotesTableSeeder::class);
        $this->call(ThConceptNotesMasterTableSeeder::class);

        $this->call(UsersTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(PermissionRoleTableSeeder::class);
        $this->call(RoleUserTableSeeder::class);

        $this->call(BibliographyTableSeeder::class);
        $this->call(GeodataTableSeeder::class);
        $this->call(AttributesTableSeeder::class);
        $this->call(EntityTypesTableSeeder::class);
        $this->call(EntitiesTableSeeder::class);
        $this->call(FilesTableSeeder::class);
        $this->call(AvailableLayersTableSeeder::class);
        $this->call(EntityTypeRelationsTableSeeder::class);
        $this->call(EntityAttributesTableSeeder::class);
        $this->call(AttributeValuesTableSeeder::class);
        $this->call(EntityFilesTableSeeder::class);
        $this->call(ReferencesTableSeeder::class);

        if(\DB::connection()->getDriverName() === 'pgsql') {
            $this->call(FixSequencesSeeder::class);
        }
    }
}
