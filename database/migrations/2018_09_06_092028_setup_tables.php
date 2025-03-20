<?php

use App\Attribute;
use App\AttributeValue;
use App\Preference;
use App\ThConcept;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SetupTables extends Migration
{
    private static $newTableNames = [
        'context_attributes' => 'entity_attributes',
        'context_type_relations' => 'entity_type_relations',
        'context_types' => 'entity_types',
        'contexts' => 'entities',
        'literature' => 'bibliography',
        'sources' => 'references',
    ];

    private static $newColumnNames = [
        'attribute_values' => [
            'context_id' => 'entity_id',
            'context_val' => 'entity_val',
            'possibility' => 'certainty',
            'possibility_description' => 'certainty_description'
        ],
        'available_layers' => [
            'context_type_id' => 'entity_type_id'
        ],
        'entity_attributes' => [
            'context_type_id' => 'entity_type_id'
        ],
        'entities' => [
            'context_type_id' => 'entity_type_id',
            'root_context_id' => 'root_entity_id'
        ],
        'references' => [
            'context_id' => 'entity_id',
            'literature_id' => 'bibliography_id'
        ]
    ];

    private static $newPermissions = [
        'literature' => 'bibliography'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        activity()->disableLogging();

        // Check if a previous version exists
        if($this->isFromScratch()) {
            echo "No previous version of Spacialist found. Migrating from scratch...\n";
            $this->migrateFromScratch();
        } else {
            echo "Spacialist version found. Migrating to latest version...\n";
            $this->migrateFromPreviousVersion();
        }

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        activity()->disableLogging();

        // Check if a previous version exists
        if($this->isFromScratch()) {
            $this->rollbackToScratch();
        } else {
            $this->rollbackToPreviousVersion();
        }

        activity()->enableLogging();
    }

    private function isFromScratch() {
        if(!Schema::hasTable('migrations')) {
            return true;
        }
        return !DB::table('migrations')
            ->where('migration', '2017_10_26_094535_fix_ranks')
            ->exists();
    }

    private function migrateFromPreviousVersion() {
        // Create Password Resets
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        // Add Maintainer Preference
        $label = 'prefs.project-maintainer';
        $value = json_encode([
            'name' => '',
            'email' => '',
            'description' => '',
            'public' => false
        ]);
        $override = false;

        $preference = new Preference();
        $preference->label = $label;
        $preference->default_value = $value;
        $preference->allow_override = $override;
        $preference->save();
        // Add Map Projection Preference
        $label = 'prefs.map-projection';
        $value = json_encode([
            'epsg' => '4326'
        ]);
        $override = false;

        $preference = new Preference();
        $preference->label = $label;
        $preference->default_value = $value;
        $preference->allow_override = $override;
        $preference->save();

        // Add text column
        Schema::table('attributes', function (Blueprint $table) {
            $table->text('text')->nullable();
        });
        // Add depends on column
        Schema::table('context_attributes', function (Blueprint $table) {
            $table->jsonb('depends_on')->nullable();
        });

        $this->migrateTableNames();
        $this->migrateColumnNames();
        $this->migrateDatatypes();
        $this->migratePermissionNames();
        $this->migrateEntityRelations();
    }

    private function migrateDatatypes() {
        // epoch entries sometimes had keys from other attributes.
        // Valid keys are only ['start', 'startLabel', 'end', 'endLabel', 'epoch']
        $allowed = ['start', 'startLabel', 'end', 'endLabel', 'epoch'];

        $epoch_aid = Attribute::where('datatype', 'epoch')->pluck('id')->toArray();
        $epochs = AttributeValue::whereIn('attribute_id', $epoch_aid)->get();

        foreach($epochs as $e) {
            $value = json_decode($e->json_val, TRUE);
            if(!isset($value)) continue;
            $value = array_intersect_key($value, array_flip($allowed));
            $value = json_encode($value);
            $e->json_val = $value;
            $e->save();
        }

        // string-mc data format changes
        $list_aid = Attribute::where('datatype', 'string-mc')->pluck('id')->toArray();
        foreach($list_aid as $aid) {
            $lists = AttributeValue::where('attribute_id', $aid)->get();
            $entity_ids = $lists->pluck('entity_id')->unique()->toArray();
            foreach($entity_ids as $eid) {
                $list = $lists->where('entity_id', $eid)->values();
                $entries = $list->map(function ($item, $key) {
                    $url = $item->getValue();
                    $concept = ThConcept::where('concept_url', $url)
                        ->select('id', 'concept_url')
                        ->first();
                    if(!isset($concept)) {
                        return [];
                    }
                    return $concept->toArray();
                });
                $tmp = $list[0];
                $av = new AttributeValue();
                $av->entity_id = $eid;
                $av->attribute_id = $aid;
                $av->json_val = $entries;
                $av->created_at = $tmp->created_at;
                $av->updated_at = $tmp->updated_at;
                $av->certainty = $tmp->certainty;
                $av->lasteditor = $tmp->lasteditor;
                $av->certainty_description = $tmp->certainty_description;
                $av->save();
                AttributeValue::whereIn('id', $list->pluck('id'))->delete();
            }
        }

        // list data format changes
        $list_aid = Attribute::where('datatype', 'list')->pluck('id')->toArray();
        foreach($list_aid as $aid) {
            $lists = AttributeValue::where('attribute_id', $aid)->get();
            $entity_ids = $lists->pluck('entity_id')->unique()->toArray();
            foreach($entity_ids as $eid) {
                $list = $lists->where('entity_id', $eid)->values();
                $entries = $list->map(function ($item, $key) {
                    return $item->getValue();
                });
                $tmp = $list[0];
                $av = new AttributeValue();
                $av->entity_id = $eid;
                $av->attribute_id = $aid;
                $av->json_val = $entries;
                $av->created_at = $tmp->created_at;
                $av->updated_at = $tmp->updated_at;
                $av->certainty = $tmp->certainty;
                $av->lasteditor = $tmp->lasteditor;
                $av->certainty_description = $tmp->certainty_description;
                $av->save();
                AttributeValue::whereIn('id', $list->pluck('id'))->delete();
            }
        }

        // table data format changes
        $table_aid = Attribute::where('datatype', 'table')->pluck('id')->toArray();
        foreach($table_aid as $table_id) {
            $tables = AttributeValue::where('attribute_id', $table_id)->orderBy('id')->get();
            $entity_ids = $tables->pluck('entity_id')->unique()->toArray();
            foreach($entity_ids as $eid) {
                $table = $tables->where('entity_id', $eid)->sortBy('id')->values();
                $rows = $table->map(function ($item, $key) {
                    return $item->getValue();
                });
                $newRows = [];
                foreach($rows as $row) {
                    $newRow = [];
                    foreach($row as $column) {
                        if(isset($column->attribute_id) && isset($column->value)) {
                            $aid = $column->attribute_id;
                            $value = $column->value;
                            if(is_object($value) && property_exists($value, 'concept_url')) {
                                $value->id = ThConcept::where('concept_url', $value->concept_url)->value('id');
                            }
                            $newRow[$aid] = $value;
                        }
                    }
                    array_push($newRows, $newRow);
                }
                $tmp = $table[0];
                $av = new AttributeValue();
                $av->entity_id = $eid;
                $av->attribute_id = $table_id;
                $av->json_val = json_encode($newRows);
                $av->created_at = $tmp->created_at;
                $av->updated_at = $tmp->updated_at;
                $av->certainty = $tmp->certainty;
                $av->lasteditor = $tmp->lasteditor;
                $av->certainty_description = $tmp->certainty_description;
                $av->save();
                foreach($table as $row) {
                    $row->delete();
                }
            }
        }
    }

    private function migratePermissionNames() {
        foreach(self::$newPermissions as $old => $new) {
            DB::unprepared("
                UPDATE permissions
                SET name = replace(name, '$old', '$new'), display_name = replace(display_name, '$old', '$new'), description = replace(description, '$old', '$new')
                WHERE name LIKE '%$old%' OR display_name LIKE '%$old%' OR description LIKE '%$old%'
            ");
        }
    }

    private function migrateTableNames() {
        foreach(self::$newTableNames as $oldTable => $newTable) {
            if(Schema::hasTable($oldTable)) {
                Schema::rename($oldTable, $newTable);
            }
        }
    }

    private function migrateColumnNames() {
        foreach(self::$newColumnNames as $tbl => $columns) {
            Schema::table($tbl, function (Blueprint $table) use($columns) {
                foreach($columns as $oldName => $newName) {
                    $table->renameColumn($oldName, $newName);
                }
            });
        }
    }

    private function migrateEntityRelations() {
        Schema::create('entity_type_relations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned();
            $table->integer('child_id')->unsigned();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('entity_types')->onDelete('cascade');
            $table->foreign('child_id')->references('id')->on('entity_types')->onDelete('cascade');
        });

        $ids = DB::table('entity_types')->select('id')->get();
        foreach(DB::table('entity_types')->get() as $ct) {
            if($ct->type === 0) {
                foreach($ids as $id) {
                    DB::table('entity_type_relations')
                        ->insert([
                            'parent_id' => $ct->id,
                            'child_id' => $id->id
                        ]);
                }
            }
        }

        // Remove default value from is_root
        Schema::table('entity_types', function (Blueprint $table) {
            $table->boolean('is_root')->default(NULL)->change();
        });

        /*Schema::table('entity_types', function (Blueprint $table) {
            $table->dropColumn('type');
        });*/
    }

    private function migrateFromScratch() {
        // enable the postgis extension
        Schema::getConnection()->statement('CREATE EXTENSION IF NOT EXISTS postgis');
        // Create Bibliography
        Schema::create('bibliography', function (Blueprint $table) {
            $table->increments('id');
            $table->text('type');
            $table->text('citekey')->unique();
            $table->text('title');
            $table->text('author')->nullable();
            $table->text('editor')->nullable();
            $table->text('journal')->nullable();
            $table->text('year')->nullable();
            $table->text('pages')->nullable();
            $table->text('volume')->nullable();
            $table->text('number')->nullable();
            $table->text('booktitle')->nullable();
            $table->text('publisher')->nullable();
            $table->text('address')->nullable();
            $table->text('misc')->nullable();
            $table->text('howpublished')->nullable();
            $table->text('annote')->nullable();
        	$table->text('chapter')->nullable();
        	$table->text('crossref')->nullable();
        	$table->text('edition')->nullable();
        	$table->text('institution')->nullable();
        	$table->text('key')->nullable();
        	$table->text('month')->nullable();
        	$table->text('note')->nullable();
        	$table->text('organization')->nullable();
        	$table->text('school')->nullable();
        	$table->text('series')->nullable();
            $table->text('lasteditor');
            $table->timestamps();
        });
        // Create ThConcept
        Schema::create('th_concept', function (Blueprint $table) {
            $table->increments('id');
            $table->text('concept_url')->unique();
            $table->text('concept_scheme');
            $table->boolean('is_top_concept')->default(false);
            $table->text('lasteditor');
            $table->timestamps();
        });
        // Create ThLanguage
        Schema::create('th_language', function (Blueprint $table) {
            $table->increments('id');
            $table->text('lasteditor');
            $table->text('display_name');
            $table->text('short_name');
            $table->timestamps();
        });
        // Create ThBroaders
        Schema::create('th_broaders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('broader_id')->unsigned();
            $table->integer('narrower_id')->unsigned();
            $table->timestamps();

            $table->foreign('broader_id')->references('id')->on('th_concept')->onDelete('cascade');
            $table->foreign('narrower_id')->references('id')->on('th_concept')->onDelete('cascade');
        });
        // Create ThConceptLabels
        Schema::create('th_concept_label', function (Blueprint $table) {
            $table->increments('id');
            $table->text('lasteditor');
            $table->text('label');
            $table->integer('concept_id')->unsigned();
            $table->integer('language_id')->unsigned();
            $table->integer('concept_label_type')->default(1);
            $table->timestamps();

            $table->foreign('concept_id')->references('id')->on('th_concept')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('th_language')->onDelete('cascade');
        });
        // Create ThNotes
        Schema::create('th_concept_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->text('content');
            $table->integer('concept_id');
            $table->integer('language_id');
            $table->timestamps();
            $table->foreign('concept_id')->references('id')->on('th_concept')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('language_id')->references('id')->on('th_language')->onDelete('cascade')->onUpdate('cascade');
        });
        // Create Users
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');
            $table->text('email')->unique();
            $table->text('password');
            $table->rememberToken('remember_token')->nullable();
            $table->timestamps();
        });
        // Create Password Resets
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        // Create attributes
        Schema::create('attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->text('thesaurus_url');
            $table->text('datatype');
            $table->text('text')->nullable();
            $table->text('thesaurus_root_url')->nullable()->comment('only for string-sc and string-mc');
            $table->integer('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('thesaurus_url')->references('concept_url')->on('th_concept')->onDelete('cascade');
            $table->foreign('thesaurus_root_url')->references('concept_url')->on('th_concept');
            $table->foreign('parent_id')->references('id')->on('attributes')->onDelete('cascade');
        });
        // Create Entity Types
        Schema::create('entity_types', function (Blueprint $table) {
            $table->increments('id');
            $table->text('thesaurus_url');
            $table->boolean('is_root');
            $table->timestamps();

            $table->foreign('thesaurus_url')->references('concept_url')->on('th_concept')->onDelete('cascade');
        });
        Schema::create('entity_type_relations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned();
            $table->integer('child_id')->unsigned();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('entity_types')->onDelete('cascade');
            $table->foreign('child_id')->references('id')->on('entity_types')->onDelete('cascade');
        });
        // Create Entity Attributes
        Schema::create('entity_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('entity_type_id')->unsigned();
            $table->integer('attribute_id')->unsigned();
            $table->integer('position');
            $table->jsonb('depends_on')->nullable();
            $table->timestamps();

            $table->foreign('entity_type_id')->references('id')->on('entity_types')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
        });
        // Create Entities
        Schema::create('entities', function (Blueprint $table) {
            $table->increments('id');
			$table->text('name');
            $table->integer('entity_type_id');
			$table->integer('root_entity_id')->nullable();
            $table->integer('rank')->nullable();
            $table->text('lasteditor');
            $table->timestamps();

			$table->foreign('entity_type_id')->references('id')->on('entity_types')->onDelete('cascade');
			$table->foreign('root_entity_id')->references('id')->on('entities')->onDelete('cascade');
        });
        // Create AttributeValues
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('entity_id')->unsigned();
            $table->integer('attribute_id')->unsigned();
            $table->text('str_val')->nullable();
            $table->integer('int_val')->nullable();
            $table->double('dbl_val')->nullable();
            $table->date('dt_val')->nullable();
            $table->integer('entity_val')->unsigned()->nullable();
            $table->text('thesaurus_val')->nullable();
            $table->jsonb('json_val')->nullable();
            $table->geography('geography_val')->nullable();
            $table->integer('certainty')->nullable();
            $table->text('certainty_description')->nullable();
            $table->text('lasteditor');
            $table->timestamps();

            $table->foreign('entity_id')->references('id')->on('entities')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
            $table->foreign('entity_val')->references('id')->on('entities')->onDelete('cascade');
            $table->foreign('thesaurus_val')->references('concept_url')->on('th_concept');
        });
        // Create References
        Schema::create('references', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('entity_id')->unsigned();
            $table->integer('attribute_id')->unsigned();
            $table->integer('bibliography_id')->unsigned();
            $table->text('description');
            $table->text('lasteditor');
            $table->timestamps();

            $table->foreign('entity_id')->references('id')->on('entities')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
            $table->foreign('bibliography_id')->references('id')->on('bibliography')->onDelete('cascade');
        });
        // Setup Entrust Roles/Permissions
        // Create table for storing roles
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        // Create table for associating roles to users (Many-to-Many)
        Schema::create('role_user', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['user_id', 'role_id']);
        });
        // Create table for storing permissions
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        // Create table for associating permissions to roles (Many-to-Many)
        Schema::create('permission_role', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();

            $table->foreign('permission_id')->references('id')->on('permissions')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['permission_id', 'role_id']);
        });
        // Create AvailableLayers
        Schema::create('available_layers', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');
            $table->text('url');
            $table->text('type');
            $table->text('subdomains')->nullable();
            $table->text('attribution')->nullable();
            $table->float('opacity')->nullable()->default(1);
            $table->text('layers')->nullable();
            $table->text('styles')->nullable();
            $table->text('format')->nullable();
            $table->text('version')->nullable();
            $table->boolean('visible')->nullable()->default(true);
            $table->boolean('is_overlay')->default(false);
            $table->text('api_key')->nullable();
            $table->text('layer_type')->nullable();
            $table->integer('position')->nullable();
            $table->integer('entity_type_id')->nullable();
            $table->text('color')->nullable();
            $table->timestamps();

            $table->foreign('entity_type_id')->references('id')->on('entity_types')->onDelete('cascade');
        });
        // Create Preferences
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
                'default_value' => json_encode(['uri' => '']),
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
            ],
            [
                'label' => 'prefs.project-maintainer',
                'default_value' => json_encode([
                    'name' => '',
                    'email' => '',
                    'description' => '',
                    'public' => false
                ]),
                'allow_override' => false
            ],
            [
                'label' => 'prefs.map-projection',
                'default_value' => json_encode(['epsg' => '4326']),
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

    private function rollbackToScratch() {
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('preferences');
        Schema::dropIfExists('available_layers');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('references');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('entities');
        Schema::dropIfExists('entity_attributes');
        Schema::dropIfExists('entity_type_relations');
        Schema::dropIfExists('entity_types');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
        Schema::dropIfExists('th_concept_label');
        Schema::dropIfExists('th_concept_label_master');
        Schema::dropIfExists('th_concept_notes');
        Schema::dropIfExists('th_concept_notes_master');
        Schema::dropIfExists('th_broaders');
        Schema::dropIfExists('th_broaders_master');
        Schema::dropIfExists('th_language');
        Schema::dropIfExists('th_concept');
        Schema::dropIfExists('th_concept_master');
        Schema::dropIfExists('bibliography');
        Schema::getConnection()->statement('DROP EXTENSION postgis');
    }

    private function rollbackToPreviousVersion() {
        // Drop Password Resets
        Schema::drop('password_resets');
        // Remove Maintainer and Map Projection Preferences
        Preference::where('label', 'prefs.project-maintainer')
            ->orWhere('label', 'prefs.map-projection')
            ->delete();

        // Drop text column
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('text');
        });
        // Drop depends on column
        Schema::table('entity_attributes', function (Blueprint $table) {
            $table->dropColumn('depends_on');
        });

        $this->rollbackEntityRelations();
        $this->rollbackDatatypes();
        $this->rollbackColumnNames();
        $this->rollbackTableNames();
        $this->rollbackPermissionNames();
    }

    private function rollbackDatatypes() {
        // revert list data format changes
        $list_aid = Attribute::where('datatype', 'list')->pluck('id')->toArray();
        foreach($list_aid as $aid) {
            $lists = AttributeValue::where('attribute_id', $aid)->get();
            foreach($lists as $list) {
                $entries = $list->getValue();
                foreach($entries as $e) {
                    $av = new AttributeValue();
                    $av->entity_id = $list->entity_id;
                    $av->attribute_id = $list->attribute_id;
                    $av->created_at = $list->created_at;
                    $av->updated_at = $list->updated_at;
                    $av->certainty = $list->certainty;
                    $av->lasteditor = $list->lasteditor;
                    $av->certainty_description = $list->certainty_description;
                    $av->str_val = $e;
                    $av->save();
                }
                $list->delete();
            }
        }

        // revert table data format changes
        $table_aid = Attribute::where('datatype', 'table')->pluck('id')->toArray();
        foreach($table_aid as $table_id) {
            $tables = AttributeValue::where('attribute_id', $table_id)->get();
            foreach($tables as $table) {
                $rows = $table->getValue();
                foreach($rows as $r) {
                    $av = new AttributeValue();
                    $av->entity_id = $table->entity_id;
                    $av->attribute_id = $table->attribute_id;
                    $av->created_at = $table->created_at;
                    $av->updated_at = $table->updated_at;
                    $av->certainty = $table->certainty;
                    $av->lasteditor = $table->lasteditor;
                    $av->certainty_description = $table->certainty_description;
                    $value = [];
                    $i = 0;
                    foreach($r as $key => $column) {
                        $value[$i] = [
                            'attribute_id' => $key,
                            'datatype' => Attribute::find($key)->datatype,
                            'value' => $column
                        ];
                        $i++;
                    }
                    $av->json_val = json_encode($value, JSON_FORCE_OBJECT);
                    $av->save();
                }
                $table->delete();
            }
        }
    }

    private function rollbackTableNames() {
        foreach(self::$newTableNames as $newTable => $oldTable) {
            if(Schema::hasTable($oldTable)) {
                Schema::rename($oldTable, $newTable);
            }
        }
    }

    private function rollbackColumnNames() {
        foreach(self::$newColumnNames as $tbl => $columns) {
            Schema::table($tbl, function (Blueprint $table) use($columns) {
                foreach($columns as $newName => $oldName) {
                    $table->renameColumn($oldName, $newName);
                }
            });
        }
    }

    private function rollbackPermissionNames() {
        foreach(self::$newPermissions as $new => $old) {
            DB::unprepared("
                UPDATE permissions
                SET name = replace(name, '$old', '$new'), display_name = replace(display_name, '$old', '$new'), description = replace(description, '$old', '$new')
                WHERE name LIKE '%$old%' OR display_name LIKE '%$old%' OR description LIKE '%$old%'
            ");
        }
    }

    private function rollbackEntityRelations() {
        Schema::table('entity_types', function (Blueprint $table) {
            $table->dropColumn('is_root');
            $table->integer('type')->default(0); // init all as context
        });

        // Remove default value from type
        Schema::table('entity_types', function (Blueprint $table) {
            $table->integer('type')->default(NULL)->change();
        });

        Schema::dropIfExists('entity_type_relations');
    }
}
