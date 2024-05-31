<?php

use Illuminate\Support\Facades\Artisan;

    Artisan::command('test:refresh', function () {
        $this->call('migrate:fresh', [
            '--env' => 'testing',
        ]);
        $this->call('db:seed', [
            '--env' => 'testing',
            '--class' => 'DemoSeeder',
        ]);
    })->describe('Resets the testing database and seeds the demo data.');