<?php

beforeEach(function () {
    $this->unsetTestUser();
});

it('login page accessible', function () {    
    visit('/')
        ->assertSee('Spacialist')
        ->assertSee('Login')
        ;
});

it('redirected to login page, when accessing protected page unauthenticated', function () {
    visit('/#/data-model')
        ->assertSee('Spacialist')
        ->assertSee('Login')
        // ->assertQueryStringHas('redirectTo', '/') // TODO: This seems wrong, it should point to /#/data-model; Those assertions are not working with Vue Hash Routing!
        ;
});

it('login page displays all required fields', function () {
    
        visit('/')
            ->assertSee('E-Mail')
            ->assertSee('Password')
            ->assertSee('Login')
                
            // Ignore exceptions from unauthenticated access
            ->assertPresent('input[name=email]')
            ->assertPresent('input[name="email"]')
            ->assertPresent('button[type=submit]')
            ;
});

it('login with invalid credentials fails', function () {
    visit('/')
        ->type('email', 'invalid@mail.com')
        ->type('password', 'wrongpassword')
        ->click('[type="submit"]')
        ->assertDontSee('Entities')
        ->assertSee('Invalid Credentials')
        ->assertSee('Login')
        ;
});

it('login with email succeeds', function () {
    visit('/')
        ->type('email', 'admin@localhost')
        ->type('password', 'admin')
        ->click('[type="submit"]')
        ->assertSee('Entities')
        ;
});

it('login with nickname succeeds', function () {
    visit('/')
        ->type('email', 'admin')
        ->type('password', 'admin')
        ->click('[type="submit"]')
        ->assertSee('Entities')
        ;
});