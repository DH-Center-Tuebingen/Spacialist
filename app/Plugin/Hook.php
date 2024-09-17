<?php


namespace App\Plugin;


/**
 * The Hook class contains all available hooks for the application.
 * This should give the user a better overview of the available hooks.
 * And a more error-proof way to use them.
 */
enum Hook: string {
    case GLOBAL = 'HomeController@getGlobalData';
    case ENTITY_TYPE_UPDATE = 'EditorController@setRelationInfo';
    case ENTITY_TYPE_GET = 'EditorController@getEntityType';
}