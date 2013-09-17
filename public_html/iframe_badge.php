<?php

// +---------------------------------------------------------------------------+
// | CrowdTranslator Plugin 0.1                                                |
// +---------------------------------------------------------------------------+
// | index.php                                                                 |
// |                                                                           |
// | Plugin administration page                                                |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2013 by the following authors:                              |
// |                                                                           |
// | Authors: Benjamin Talic - b DOT ttalic AT gmail DOT com                   |
// +---------------------------------------------------------------------------+
// | Created with the Geeklog Plugin Toolkit.                                  |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+


/**
 * @package crowdtranslator
 */

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