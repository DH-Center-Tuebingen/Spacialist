<?php

it('may welcome the user', function () {
    $this->unsetTestUser();
    
    visit('/')
      ->assertSee('Spacialist');
});

it('get 404 on invalid root page', function () {
    $this->unsetTestUser();
    
    visit('/invalid-page')
      ->assertSee('404');
});