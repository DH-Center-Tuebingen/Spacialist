<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshTesting extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test {--r|refresh : Whether the database should be refreshed} {--s|skip : If tests should be run, e.g. when doing a refresh you may want to skip the testing.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spacialist testing utility. Can be used to reset all test to a clean state, using the --refresh (-r) option.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $testingEnv = \Dotenv\Dotenv::createMutable(base_path(), '.env.testing');
        $testingEnv->load();

        $database = env('DB_DATABASE');

        if($this->option('refresh')) {
            $this->info("Refreshing database '$database' ...");
            $this->call('config:clear');
            $this->call('migrate:fresh');
            $this->call('db:seed', [
                '--class' => 'TestingSeeder',
            ]);
        }

        if(!$this->option('skip')) {
            $this->info('Running tests...');
            $this->call('test');
        } else {
            $this->info('Skipping tests...');
        }
    }
}
