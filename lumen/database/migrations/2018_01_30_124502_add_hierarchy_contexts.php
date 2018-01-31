<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\ContextType;
use App\ContextTypeRelation;

class AddHierarchyContexts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('context_types', function (Blueprint $table) {
            $table->boolean('is_root')->default(true); // Everything can be root!
        });

        Schema::create('context_type_relations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned();
            $table->integer('child_id')->unsigned();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('context_types')->onDelete('cascade');
            $table->foreign('child_id')->references('id')->on('context_types')->onDelete('cascade');
        });

        $ids = ContextType::select('id')->get();
        foreach(ContextType::all() as $ct) {
            if($ct->type === 0) {
                foreach($ids as $id) {
                    $rel = new ContextTypeRelation();
                    $rel->parent_id = $ct->id;
                    $rel->child_id = $id->id;
                    $rel->save();
                }
            }
        }

        // Remove default value from is_root
        Schema::table('context_types', function (Blueprint $table) {
            $table->boolean('is_root')->default(NULL)->change();
        });

        Schema::table('context_types', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('context_types', function (Blueprint $table) {
            $table->dropColumn('is_root');
            $table->integer('type')->default(0); // init all as context
        });

        // Remove default value from type
        Schema::table('context_types', function (Blueprint $table) {
            $table->integer('type')->default(NULL)->change();
        });

        Schema::dropIfExists('context_type_relations');
    }
}
