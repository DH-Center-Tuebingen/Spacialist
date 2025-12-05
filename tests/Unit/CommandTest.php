<?php

uses(\Tests\TransactionedTestCase::class);
use App\Role;
use App\User;


test('spacialist create user and role command', function () {
    $this->artisan('app:create --role --user')
        ->expectsOutput('Can not create user and role simultaneously!')
        ->assertExitCode(1);
});

test('spacialist create user command', function () {
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
    expect('New User')->toEqual($user->name);
    expect('nuser')->toEqual($user->nickname);
    expect('user@example.tld')->toEqual($user->email);
});

test('spacialist create user with flag command', function () {
    $confirmStr = "Is this correct?\n\tName: New User #2\n\tNickname: nuser2\n\tEmail: user2@example.tld\n";

    $this->artisan('app:create --user "New User #2"')
        ->expectsQuestion('Please enter your nick name', 'nuser2')
        ->expectsQuestion('Please enter your email address', 'user2@example.tld')
        ->expectsQuestion('Please enter your password', 'newpw')
        ->expectsQuestion($confirmStr, true)
        ->expectsOutput('User New User #2 created!')
        ->assertExitCode(0);

    $user = User::latest()->first();
    expect('New User #2')->toEqual($user->name);
    expect('nuser2')->toEqual($user->nickname);
    expect('user2@example.tld')->toEqual($user->email);
});

test('spacialist create user with existing mail command', function () {
    $this->artisan('app:create --user "New User"')
        ->expectsQuestion('Please enter your nick name', 'nuser')
        ->expectsQuestion('Please enter your email address', 'admin@localhost')
        ->expectsQuestion('Please enter your password', 'newpw')
        ->expectsOutput('The email has already been taken.')
        ->assertExitCode(1);

    $user = User::firstWhere("email", "=", "admin@localhost");
    expect('Admin')->toEqual($user->name);

    $latest = User::withTrashed()->latest()->first();
    expect('Gary Guest')->toEqual($latest->name);
    $user = User::latest()->first();
    expect('John Doe')->toEqual($user->name);
});

test('spacialist create user with wrong mail command', function () {
    $this->artisan('app:create --user "New User"')
        ->expectsQuestion('Please enter your nick name', 'nuser')
        ->expectsQuestion('Please enter your email address', 'admin(at)localhost')
        ->expectsQuestion('Please enter your password', 'newpw')
        ->expectsOutput('The email must be a valid email address.')
        ->assertExitCode(1);

    $user = User::withTrashed()->latest()->first();
    expect('Gary Guest')->toEqual($user->name);
    $user = User::latest()->first();
    expect('John Doe')->toEqual($user->name);
});

test('spacialist create user with existing nick command', function () {
    $this->artisan('app:create --user "New User"')
        ->expectsQuestion('Please enter your nick name', 'admin')
        ->expectsQuestion('Please enter your email address', 'admin@localhost')
        ->expectsQuestion('Please enter your password', 'newpw')
        ->expectsOutput('The nickname has already been taken.')
        ->assertExitCode(1);

    $user = User::withTrashed()->latest()->first();
    expect('Gary Guest')->toEqual($user->name);
    $user = User::latest()->first();
    expect('John Doe')->toEqual($user->name);
});

test('spacialist create user with wrong nick command', function () {
    $this->artisan('app:create --user "New User"')
        ->expectsQuestion('Please enter your nick name', 'nickname with spaces')
        ->expectsQuestion('Please enter your email address', 'admin@localhost')
        ->expectsQuestion('Please enter your password', 'newpw')
        ->expectsOutput('The nickname may only contain letters, numbers, and dashes.')
        ->assertExitCode(1);

    $user = User::withTrashed()->latest()->first();
    expect('Gary Guest')->toEqual($user->name);
    $user = User::latest()->first();
    expect('John Doe')->toEqual($user->name);
});

test('spacialist create role command', function () {
    $confirmStr = "Is this correct?\n\tName: new_role\n\tDisplayName: New Role\n\tDescription: Description for new role\n";

    $this->artisan('app:create --role')
        ->expectsQuestion('Please provide the new role name', 'new_role')
        ->expectsQuestion('Please provide the desired display name', 'New Role')
        ->expectsQuestion('Please provide the desired description', 'Description for new role')
        ->expectsQuestion($confirmStr, true)
        ->expectsOutput('Role new_role created!')
        ->assertExitCode(0);

    $role = Role::latest()->first();
    expect('new_role')->toEqual($role->name);
    expect('New Role')->toEqual($role->display_name);
    expect('Description for new role')->toEqual($role->description);
});

test('spacialist create role with existing name command', function () {
    $this->artisan('app:create --role admin')
        ->expectsQuestion('Please provide the new role name', 'admin')
        ->expectsQuestion('Please provide the desired display name', 'Admin')
        ->expectsQuestion('Please provide the desired description', 'The new admin')
        ->expectsOutput('The name has already been taken.')
        ->assertExitCode(1);

    $role = Role::latest()->orderBy('id', 'desc')->first();
    expect('guest')->toEqual($role->name);
});
