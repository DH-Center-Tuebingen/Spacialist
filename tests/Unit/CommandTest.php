<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Role;
use App\User;

class CommandTest extends TestCase {
    /**
     * Test creating role and user
     *
     * @return void
     */
    public function testSpacialistCreateUserAndRoleCommand() {
        $this->artisan('app:create --role --user')
            ->expectsOutput('Can not create user and role simultaneously!')
            ->assertExitCode(1);
    }

    /**
     * Test adding a new user
     *
     * @return void
     */
    public function testSpacialistCreateUserCommand() {
        $confirmStr = "Is this correct?\n\tName: New User\n\tNickname: nuser\n\tEmail: user@example.tld\n";

        $this->artisan('app:create')
            ->expectsQuestion('Please provide the new user name', 'New User')
            ->expectsQuestion('Please enter your nick name', 'nuser')
            ->expectsQuestion('Please enter your email address', 'user@example.tld')
            ->expectsQuestion('Please enter your password', 'newpw')
            ->expectsQuestion($confirmStr, true)
            ->expectsOutput('User New User created!')
            ->assertExitCode(0);

        $user = User::latest()->first();
        $this->assertEquals($user->name, 'New User');
        $this->assertEquals($user->nickname, 'nuser');
        $this->assertEquals($user->email, 'user@example.tld');
    }

    /**
     * Test adding a new user with --flag and name set
     *
     * @return void
     */
    public function testSpacialistCreateUserWithFlagCommand() {
        $confirmStr = "Is this correct?\n\tName: New User #2\n\tNickname: nuser2\n\tEmail: user2@example.tld\n";

        $this->artisan('app:create --user "New User #2"')
            ->expectsQuestion('Please enter your nick name', 'nuser2')
            ->expectsQuestion('Please enter your email address', 'user2@example.tld')
            ->expectsQuestion('Please enter your password', 'newpw')
            ->expectsQuestion($confirmStr, true)
            ->expectsOutput('User New User #2 created!')
            ->assertExitCode(0);

        $user = User::latest()->first();
        $this->assertEquals($user->name, 'New User #2');
        $this->assertEquals($user->nickname, 'nuser2');
        $this->assertEquals($user->email, 'user2@example.tld');
    }

    /**
     * Test adding a new user with taken email address
     *
     * @return void
     */
    public function testSpacialistCreateUserWithExistingMailCommand() {
        $this->artisan('app:create --user "New User"')
            ->expectsQuestion('Please enter your nick name', 'nuser')
            ->expectsQuestion('Please enter your email address', 'admin@localhost')
            ->expectsQuestion('Please enter your password', 'newpw')
            ->expectsOutput('The email has already been taken.')
            ->assertExitCode(1);

        $user = User::firstWhere("email", "=", "admin@localhost");
        $this->assertEquals($user->name, 'Admin');

        $latest = User::latest()->first();
        $this->assertEquals($latest->name, 'Gary Guest');
    }

    /**
     * Test adding a new user with a wrong email address
     *
     * @return void
     */
    public function testSpacialistCreateUserWithWrongMailCommand() {
        $this->artisan('app:create --user "New User"')
            ->expectsQuestion('Please enter your nick name', 'nuser')
            ->expectsQuestion('Please enter your email address', 'admin(at)localhost')
            ->expectsQuestion('Please enter your password', 'newpw')
            ->expectsOutput('The email must be a valid email address.')
            ->assertExitCode(1);

        $user = User::latest()->first();
        $this->assertEquals($user->name, 'Gary Guest');
    }

    /**
     * Test adding a new user with taken email address
     *
     * @return void
     */
    public function testSpacialistCreateUserWithExistingNickCommand() {
        $this->artisan('app:create --user "New User"')
            ->expectsQuestion('Please enter your nick name', 'admin')
            ->expectsQuestion('Please enter your email address', 'admin@localhost')
            ->expectsQuestion('Please enter your password', 'newpw')
            ->expectsOutput('The nickname has already been taken.')
            ->assertExitCode(1);

        $user = User::latest()->first();
        $this->assertEquals($user->name, 'Gary Guest');
    }

    /**
     * Test adding a new user with a wrong email address
     *
     * @return void
     */
    public function testSpacialistCreateUserWithWrongNickCommand() {
        $this->artisan('app:create --user "New User"')
            ->expectsQuestion('Please enter your nick name', 'nickname with spaces')
            ->expectsQuestion('Please enter your email address', 'admin@localhost')
            ->expectsQuestion('Please enter your password', 'newpw')
            ->expectsOutput('The nickname may only contain letters, numbers, and dashes.')
            ->assertExitCode(1);

        $user = User::latest()->first();
        $this->assertEquals($user->name, 'Gary Guest');
    }

    /**
     * Test adding a new role
     *
     * @return void
     */
    public function testSpacialistCreateRoleCommand() {
        $confirmStr = "Is this correct?\n\tName: new_role\n\tDisplayName: New Role\n\tDescription: Description for new role\n";

        $this->artisan('app:create --role')
            ->expectsQuestion('Please provide the new role name', 'new_role')
            ->expectsQuestion('Please provide the desired display name', 'New Role')
            ->expectsQuestion('Please provide the desired description', 'Description for new role')
            ->expectsQuestion($confirmStr, true)
            ->expectsOutput('Role new_role created!')
            ->assertExitCode(0);

        $role = Role::latest()->first();
        $this->assertEquals($role->name, 'new_role');
        $this->assertEquals($role->display_name, 'New Role');
        $this->assertEquals($role->description, 'Description for new role');
    }

    /**
     * Test adding a new role
     *
     * @return void
     */
    public function testSpacialistCreateRoleWithExistingNameCommand() {
        $this->artisan('app:create --role admin')
            ->expectsQuestion('Please provide the new role name', 'admin')
            ->expectsQuestion('Please provide the desired display name', 'Admin')
            ->expectsQuestion('Please provide the desired description', 'The new admin')
            ->expectsOutput('The name has already been taken.')
            ->assertExitCode(1);

        $role = Role::latest()->orderBy('id', 'desc')->first();
        $this->assertEquals($role->name, 'guest');
    }
}
