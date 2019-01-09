# Spacialist

[![Join the chat at https://gitter.im/eScienceCenter/Spacialist](https://badges.gitter.im/eScienceCenter/Spacialist.svg)](https://gitter.im/eScienceCenter/Spacialist?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

Spacialist is a customizable Web-based platform for collecting, managing, analyzing and publishing research data with a focus on the integration of object-related and spatial data for the digital humanities.

## Installation
Installation procedures and system requirements are described [here](INSTALL.md).

## Testing
1. Create a testing database and reference it in your `.env` file
2. Run migrations and seeds (`db:seed`)
3. Run `vendor/bin/phpunit`

## Screenshots
For all screenshots have a look at the [screenshot folder][scr_folder]

![scr_start]

## Acknowledgments
Development of Spacialist was co-funded from 2015-2018 by the Ministry of Science, Research and the Arts Baden-Württemberg in the "E-Science" funding programme.

[scr_start]: screenshots/selected_element.png "Spacialist Main Screen"
[scr_folder]: screenshots/
