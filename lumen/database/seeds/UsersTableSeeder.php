<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run() {
        DB::table('users')->delete();
        \App\User::create(array(
            'name'     => 'Vinzenz Rosenkranz',
            'email'    => 'vinzenz.rosenkranz@uni-tuebingen.de',
            'password' => Hash::make('vinz1'),
        ));
	\App\User::create(array(
	    'name'     => 'Dirk Seidensticker',
            'email'    => 'dirk.seidensticker@gmail.com',
            'password' => Hash::make('dirk1'),
        ));
    }
}
