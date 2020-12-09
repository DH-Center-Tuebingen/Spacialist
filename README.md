<p>
    <a href="https://codecov.io/gh/eScienceCenter/Spacialist">
        <img src="https://codecov.io/gh/eScienceCenter/Spacialist/branch/master/graph/badge.svg?token=4VVZQDJXSM"/>
    </a>
    <img src="https://github.com/eScienceCenter/Spacialist/workflows/PhpUnit/badge.svg"/>
    <a href='https://david-dm.org/eScienceCenter/Spacialist'>
        <img src='https://david-dm.org/eScienceCenter/Spacialist.svg' alt='Dependency Status' />
    </a>
    <a href='https://opensource.org/licenses/MIT'>
        <img src='https://img.shields.io/badge/License-MIT-yellow.svg' alt='License: MIT' />
    </a>
</p>

# Spacialist

Spacialist is a customizable Web-based platform for collecting, managing, analyzing and publishing research data with a focus on the integration of object-related and spatial data for the digital humanities.

## Installation
Installation procedures and system requirements are described [here](INSTALL.md).

## Testing
All PHPUnit tests are based on the `DemoSeeder` seed. To run tests, follow these steps:

1. Create a new database and a `.env.testing` file (`cp .env .env.testing`) and reference the DB in your `.env.testing` or, if you don't have/want one, in your `.env` file
2. Run migrations
    - For `.env.testing`: `php artisan migrate --env=testing`
    - For `.env`: `php artisan migrate`
3. Run seeds
    - For `.env.testing`: `php artisan db:seed --class=DemoSeeder --env=testing`
    - For `.env`: `php artisan db:seed --class=DemoSeeder`
4. Run `vendor/bin/phpunit`

## Screenshots
For more screenshots have a look at the [screenshot folder][scr_folder]

![scr_start]

## Acknowledgments
Development of Spacialist was co-funded from 2015-2018 by the Ministry of Science, Research and the Arts Baden-Württemberg in the "E-Science" funding programme.

[scr_start]: screenshots/selected_element.png "Spacialist Main Screen"
[scr_folder]: screenshots/
