<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Preference;

class AddUserPrefs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preferences', function (Blueprint $table) {
            $table->increments('id');
            $table->text('label');
            $table->jsonb('default_value');
            $table->boolean('allow_override')->nullable()->default(false);
            $table->timestamps();
        });

        Schema::create('user_preferences', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pref_id');
            $table->integer('user_id');
            $table->jsonb('value');
            $table->timestamps();

            $table->foreign('pref_id')->references('id')->on('preferences')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        $defaultPrefs = [
            [
                'label' => 'prefs.gui-language',
                'default_value' => json_encode(['language_key' => 'en']),
                'allow_override' => true
            ],
            [
                'label' => 'prefs.columns',
                'default_value' => json_encode(['left' => 2, 'center' => 5, 'right' => 5]),
                'allow_override' => true
            ],
            [
                'label' => 'prefs.show-tooltips',
                'default_value' => json_encode(['show' => true]),
                'allow_override' => true
            ],
            [
                'label' => 'prefs.tag-root',
                'default_value' => json_encode(['uri' => 'http://thesaurus.archeoinf.de/lukanien#fototyp']),
                'allow_override' => false
            ],
            [
                'label' => 'prefs.load-extensions',
                'default_value' => json_encode(['map' => true, 'files' => true, 'bibliography' => true, 'data-analysis' => true]),
                'allow_override' => false
            ],
            [
                'label' => 'prefs.link-to-thesaurex',
                'default_value' => json_encode(['url' => '']),
                'allow_override' => false
            ],
            [
                'label' => 'prefs.project-name',
                'default_value' => json_encode(['name' => 'Spacialist']),
                'allow_override' => false
            ]
        ];

        foreach($defaultPrefs as $dp) {
            $p = new Preference();
            $p->label = $dp['label'];
            $p->default_value = $dp['default_value'];
            $p->allow_override = $dp['allow_override'];
            $p->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('preferences');
    }
}
