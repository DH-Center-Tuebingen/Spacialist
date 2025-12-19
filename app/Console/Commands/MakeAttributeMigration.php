<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeAttributeMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:attribute-migration {type : The attribute type (e.g., integer, string, geometry)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new attribute value migration using the AttributeMigration base class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = strtolower($this->argument('type'));

        // Generate migration file name
        $timestamp = date('Y_m_d_His');
        $className = 'Create' . Str::studly($type) . 'AttributeValuesTable';
        $fileName = $timestamp . '_create_' . $type . '_attribute_values_table.php';
        $path = database_path('migrations/' . $fileName);

        // Check if file already exists
        if(File::exists($path)) {
            $this->error("Migration already exists: {$fileName}");
            return 1;
        }

        // Generate the migration content
        $content = $this->generateMigrationContent($className, $type);

        // Write the file
        File::put($path, $content);

        $this->info("Migration created successfully!");
        $this->info("Location: {$path}");

        return 0;
    }

    /**
     * Generate the migration file content.
     */
    protected function generateMigrationContent(string $className, string $type): string
    {
        return <<<PHP
<?php

use App\Migration\AttributeMigration;
use Illuminate\Database\Schema\Blueprint;

return new class extends AttributeMigration
{
    protected function getAttributeType(): string
    {
        return '{$type}';
    }

    protected function defineValueColumn(Blueprint \$table): void
    {
        // TODO: Define your value column(s) here
        // Examples:
        //   \$table->bigInteger('value');
        //   \$table->foreignId('value')->constrained('entities')->onDelete('cascade');
    }
};

PHP;
    }
}

