<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;


/**
 * Overwrite of the default Laravel make:migration command to create a migration
 * that extends our base Migration class instead of the default Laravel one.
 */
class MakeMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:migration {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a blank test file for every file of a specific subdirectory of the app directory';

    /**
     * Execute the console command.
     */
    public function handle() {
        
        
        database_path('migrations');
        $name = $this->argument('name');
        $timestamp = date('Y_m_d_His');

        $snakedName = Str::snake($name);
        $filename = database_path("migrations/{$timestamp}_{$snakedName}.php");
        $template = "
<?php

    use App\Migration\Migration;
    
    return new class extends Migration
    {
        public function migrate(): void
        {
            // add custom spacialist migration logic here ...
        }
        public function rollback(): void
        {
            // add custom spacialist migration logic here ...
        }
    };
";

        file_put_contents($filename, $template);
        $this->info("Migration file created: {$filename}");
        return 0;
    }
}