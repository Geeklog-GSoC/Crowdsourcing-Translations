<?php

require_once '../lib-common.php';
require_once $_CONF[ 'path_system' ] . 'lib-database.php';
require_once "./lib-translator.php";

if ( !isset( $_GET[ 'site_name' ] ) ) {
    echo "You have to set the sitename in order to use this frame.";
} else {
    $site_name = DB_escapeString( $_GET[ 'site_name' ] );
    $query     = "SELECT COUNT(`id`) as count FROM {$_TABLES['translations']} WHERE `site_credentials`= '{$site_name}'";
    $result    = DB_query( $query );
    $result    = DB_fetchArray( $result );
    echo "We have submited " . $result[ 'count' ] . " translations to " . $_SERVER[ 'HTTP_HOST' ];
}

?>