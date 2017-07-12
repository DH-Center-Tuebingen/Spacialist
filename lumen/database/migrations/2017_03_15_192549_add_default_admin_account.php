<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Role;

class AddDefaultAdminAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // empty for compatibility with existing databases
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // empty for compatibility with existing databases
    }
}
