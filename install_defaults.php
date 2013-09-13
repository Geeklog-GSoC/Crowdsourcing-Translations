<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | crowdtranslator Plugin 0.1                                                |
// +---------------------------------------------------------------------------+
// | install_defaults.php                                                      |
// |                                                                           |
// | This file is used to hook into Geeklog's configuration UI                 |
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
if ( strpos( strtolower( $_SERVER[ 'PHP_SELF' ] ), 'functions.inc' ) !== false ) {
    die( 'This file can not be used on its own.' );
}
$plugin_path = $_CONF[ 'path' ] . 'plugins/crowdtranslator/';

require_once $plugin_path . "language_maper.php";
language_maper();
/**
 * crowdtranslator default settings
 *
 * Initial Installation Defaults used when loading the online configuration
 * records.  These settings are only used during the initial installation
 * and not referenced any more once the plugin is installed
 */
global $_CROWDTRANSLATOR_DEFAULT;
$_CROWDTRANSLATOR_DEFAULT                   = array( );

$_CROWDTRANSLATOR_DEFAULT[ 'enabled' ]      = true;
$_CROWDTRANSLATOR_DEFAULT[ 'block_enable' ] = true;
$_CROWDTRANSLATOR_DEFAULT ['full_install'] = false;
/**
 * Initialize crowdtranslator plugin configuration
 *
 * Creates the database entries for the configuation if they don't already
 * exist.  Initial values will be taken from $_CROWDTRANSLATOR_DEFAULT.
 *
 * @return   boolean     TRUE: success; FALSE: an error occurred
 */
function plugin_initconfig_crowdtranslator( )
{
    global $_CROWDTRANSLATOR_CONF, $_CROWDTRANSLATOR_DEFAULT;
    if ( is_array( $_CROWDTRANSLATOR_CONF ) && ( count( $_CROWDTRANSLATOR_CONF ) > 1 ) ) {
        $_CROWDTRANSLATOR_DEFAULT = array_merge( $_CROWDTRANSLATOR_DEFAULT, $_CROWDTRANSLATOR_CONF );
    }
    $me = 'crowdtranslator';
    $c  = config::get_instance();
    if ( !$c->group_exists( $me ) ) {
        $c->add( 'sg_main', NULL, 'subgroup', 0, 0, NULL, 0, true, $me, 0 );
        $c->add( 'tab_main', NULL, 'tab', 0, 0, NULL, 0, true, $me, 0 );
        $c->add( 'fs_main', NULL, 'fieldset', 0, 0, NULL, 0, true, $me, 0 );
        // The below two lines add two settings to Geeklog's config UI
        $c->add( 'enabled', $_CROWDTRANSLATOR_DEFAULT[ 'enabled' ], 'select', 0, 0, 0, 10, true, $me, 0 ); // This adds a drop-down box
        $c->add( 'block_enable', $_CROWDTRANSLATOR_DEFAULT[ 'block_enable' ], 'select', 0, 0, 0, 10, true, $me, 0 );
    }
    return true;
}
?>