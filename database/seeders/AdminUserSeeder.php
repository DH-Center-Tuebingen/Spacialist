<?php

namespace Database\Seeders;

use App\User;
use App\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::where('name', 'Admin')->first();
        if($admin === null) {
            $admin = new User();
            $admin->name = 'Admin';
            $admin->nickname = 'admin';
            $admin->email = 'admin@localhost';
            $admin->password = Hash::make('admin');
            $admin->save();
        }

        $adminRole = Role::where('name', 'admin')->first();
        $admin->assignRole($adminRole);
    }
}
