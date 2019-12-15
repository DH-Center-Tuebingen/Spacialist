<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupRestrictions extends Migration
{
    private static $newPermissions = [
        'add_edit_group' => [
            'display_name' => 'Add and edit groups',
            'description' => 'Add and edit groups added to users',
        ],
        'delete_group' => [
            'display_name' => 'Delete groups',
            'description' => 'Delete groups',
        ]
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('user_groups', function (Blueprint $table) {
            $table->integer('user_id');
            $table->integer('group_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });

        Schema::create('access_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('objectable_id');
            $table->text('objectable_type');
            $table->integer('group_id');
            $table->text('rules')->nullable();
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });

        $adminRole = Role::where('name', 'admin')->first();
        foreach(self::$newPermissions as $pk => $p) {
            $new_perm = Permission::create([
                'name' => $pk,
                'guard_name' => 'web'
            ]);
            $new_perm->display_name = $p['display_name'];
            $new_perm->description = $p['description'];
            $new_perm->save();

            if(isset($adminRole)) {
                $adminRole->givePermissionTo($new_perm);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('access_rules');
        Schema::dropIfExists('user_groups');
        Schema::dropIfExists('groups');

        foreach(self::$newPermissions as $pk => $p) {
            Permission::where('name', $pk)->delete();
        }
    }
}
