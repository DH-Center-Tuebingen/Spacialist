<p>
    <a href="https://codecov.io/gh/DH-Center-Tuebingen/Spacialist">
        <img src="https://codecov.io/gh/DH-Center-Tuebingen/Spacialist/branch/master/graph/badge.svg?token=4VVZQDJXSM"/>
    </a>
    <img src="https://github.com/DH-Center-Tuebingen/Spacialist/workflows/PhpUnit/badge.svg"/>
    <a href='https://david-dm.org/DH-Center-Tuebingen/Spacialist'>
        <img src='https://david-dm.org/DH-Center-Tuebingen/Spacialist.svg' alt='Dependency Status' />
    </a>
    <a href='https://opensource.org/licenses/MIT'>
        <img src='https://img.shields.io/badge/License-MIT-yellow.svg' alt='License: MIT' />
    </a>
</p>

# Spacialist

Spacialist is a customizable Web-based platform for collecting, managing, analyzing and publishing research data with a focus on the integration of object-related and spatial data for the digital humanities.

## Installation

Installation procedures and system requirements are described [here](INSTALL.md).
**Important!** Please read the installation file before any update for breaking changes or any other important steps that may **break the update**!

## Plugins (Beta)

Since Release _Isfahan_ Spacialist is extensible by plugins. If you are a developer and want to create your own plugin, please refer to this [HowTo Guide](PLUGINS.md).

A list with all available plugins is **coming soon**.

## Testing

All PHPUnit tests are based on the `DemoSeeder` seed. To run tests, follow these steps:

1. Create a new database and a `.env.testing` file (`cp .env .env.testing`) and reference the DB in your `.env.testing`.
2. Run `php artisan test:refresh` This will:
    1. Run migrations `php artisan migrate --env=testing`
    2. Run seeds `php artisan db:seed --class=DemoSeeder --env=testing`
3. Run `php artisan test` or `vendor/bin/phpunit`

### Run specific tests
You can also run specific tests in isolation:
```bash
# Runs a specific test file
php artisan test --filter NameOfTestFile

# Runs a specific test inside the specified test file
php artisan test tests/Feature/MyTestFile.php --filter testTargetMethod 
```


Note: To re-run all migrations you can use this command `php artisan migrate:fresh --seed  --seeder=DemoSeeder --env=testing`

## Screenshots

For more screenshots have a look at the [screenshot folder][scr_folder]

![scr_start]

## Acknowledgments

Development of Spacialist was co-funded from 2015-2018 by the Ministry of Science, Research and the Arts Baden-Württemberg in the "E-Science" funding programme.

[scr_start]: screenshots/selected_element.png "Spacialist Main Screen"
[scr_folder]: screenshots/
