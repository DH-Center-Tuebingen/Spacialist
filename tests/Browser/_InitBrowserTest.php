<?php

it('may welcome the user', function () {
    $this->unsetTestUser();
    
    $page = visit('/');
 
    $page->assertSee('Spacialist');
});

it('get 404 on invalid root page', function () {
    $this->unsetTestUser();
    
    $page = visit('/invalid-page');
 
    $page->assertSee('404');
});