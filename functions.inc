<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | crowdtranslator Plugin 0.1                                                |
// +---------------------------------------------------------------------------+
// | functions.inc                                                             |
// |                                                                           |
// | This file does two things: 1) it implements the necessary Geeklog Plugin  |
// | API methods and 2) implements all the common code needed by this plugin.  |
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
/**
 * Language file include
 */
$plugin_path = $_CONF[ 'path' ] . 'plugins/crowdtranslator/';
$langfile    = $plugin_path . 'language/' . $_CONF[ 'language' ] . '.php';
if ( file_exists( $langfile ) ) {
    require_once $langfile;
} else {
    require_once $plugin_path . 'language/english.php';
}
/**
 * Check and see if we need to load the plugin configuration
 */
if ( !isset( $_CROWDTRANSLATOR_CONF ) ) {
    require_once $_CONF[ 'path_system' ] . 'classes/config.class.php';
    $CROWDTRANSLATOR_config = config::get_instance();
    $_CROWDTRANSLATOR_CONF  = $CROWDTRANSLATOR_config->get_config( 'crowdtranslator' );
}
/**
 * crowdtranslator plugin table(s)
 */
$_TABLES[ 'translations' ]       = $_DB_table_prefix . 'translations';
$_TABLES[ 'originals' ]          = $_DB_table_prefix . 'originals';
$_TABLES[ 'votes' ]              = $_DB_table_prefix . 'votes';
$_TABLES[ 'gems' ]               = $_DB_table_prefix . 'gems';
$_TABLES[ 'awarded_gems' ]       = $_DB_table_prefix . 'awarded_gems';
$_TABLES[ 'blocked_users' ]      = $_DB_table_prefix . 'blocked_users';
$_TABLES[ 'language_map' ]       = $_DB_table_prefix . 'language_map';
$_TABLES[ 'remote_credentials' ] = $_DB_table_prefix . 'remote_credentials';
// +---------------------------------------------------------------------------+
// | Geeklog Plugin API Implementation                                         |
// +---------------------------------------------------------------------------+
/**
 * Returns the item(s) for this plugin that should appear on the main menu
 *
 * NOTE: this MUST return the url/value pairs in the following format
 * $<arrayname>[<label>] = <url>
 *
 * @return   mixed   menu entry, or boolean false if disabled / hidden
 *
 */
function plugin_getmenuitems_crowdtranslator( )
{
    global $_CONF, $LANG_CROWDTRANSLATOR_1;
    $url                                                   = $_CONF[ 'site_url' ] . '/crowdtranslator/index.php';
    $menuitems[ $LANG_CROWDTRANSLATOR_1[ 'plugin_name' ] ] = $url;
    return $menuitems;
}

/**
 * Return plugin entry for "Command and Control" (moderation.php)
 *
 * @return  array  Array containing (plugin name, admin url, url of plugin icon)
 *
 */
function plugin_cclabel_crowdtranslator( )
{
    global $_CONF, $LANG_CROWDTRANSLATOR_1;
    $retval = array( );
    if ( SEC_hasRights( 'crowdtranslator.admin' ) ) {
        $retval = array(
             $LANG_CROWDTRANSLATOR_1[ 'plugin_name' ],
            $_CONF[ 'site_admin_url' ] . '/plugins/crowdtranslator/index.php',
            plugin_geticon_crowdtranslator() 
        );
    }
    return $retval;
}
/**
 * Return plugin entry for the Admins Only block
 *
 * @return array Array containing (plugin name, plugin admin url, # of plugin items or '')
 */
function plugin_getadminoption_crowdtranslator( )
{
    global $_CONF, $LANG_CROWDTRANSLATOR_1;
    if ( SEC_hasRights( 'crowdtranslator.admin' ) ) {
        return array(
             $LANG_CROWDTRANSLATOR_1[ 'plugin_name' ],
            $_CONF[ 'site_admin_url' ] . '/plugins/crowdtranslator/index.php',
            0 
        );
    }
}
/**
 * Returns the URL of the plugin's icon
 *
 * @return   string      URL of the icon
 *
 */
function plugin_geticon_crowdtranslator( )
{
    global $_CONF;
    // popular alternative location:
    // return $_CONF['site_url'] . '/crowdtranslator/images/crowdtranslator.png';
    return $_CONF[ 'site_admin_url' ] . '/plugins/crowdtranslator/images/crowdtranslator.png';
}
/**
 * Returns the current plugin code version
 *
 * @return string    plugin version
 */
function plugin_chkVersion_crowdtranslator( )
{
    global $_CONF;
    require_once $_CONF[ 'path' ] . 'plugins/crowdtranslator/autoinstall.php';
    $inst_parms = plugin_autoinstall_crowdtranslator( 'crowdtranslator' );
    return $inst_parms[ 'info' ][ 'pi_version' ];
}
/**
 * Update the plugin
 *
 * @return   int     Number of message to display or true for success
 *
 */
function plugin_upgrade_crowdtranslator( )
{
    global $_CONF, $_TABLES;
    $installed_version = DB_getItem( $_TABLES[ 'plugins' ], 'pi_version', "pi_name = 'crowdtranslator'" );
    $code_version      = plugin_chkVersion_crowdtranslator();
    if ( $installed_version == $code_version ) {
        // nothing to do
        return true;
    }
    require_once $_CONF[ 'path' ] . 'plugins/crowdtranslator/autoinstall.php';
    if ( !plugin_compatible_with_this_version_crowdtranslator( 'crowdtranslator' ) ) {
        return 3002;
    }
    // other update code goes here
    // update plugin version number
    $inst_parms    = plugin_autoinstall_crowdtranslator( 'crowdtranslator' );
    $pi_gl_version = $inst_parms[ 'info' ][ 'pi_gl_version' ];
    DB_query( "UPDATE {$_TABLES['plugins']} SET pi_version = '$code_version', pi_gl_version = '$pi_gl_version' WHERE pi_name = 'crowdtranslator'" );
    return true;
}
/**
 * Automatic uninstall function for plugins
 *
 * This code is automatically uninstalling the plugin.
 * It passes an array to the core code function that removes
 * tables, groups, features and php blocks from the tables.
 * Additionally, this code can perform special actions that cannot be
 * foreseen by the core code (interactions with other plugins for example)
 *
 * @return   array   Plugin information
 *
 */
function plugin_autouninstall_crowdtranslator( )
{
    global $_CONF;
    $plugin_path = $_CONF[ 'path' ] . 'plugins/crowdtranslator/';
    $out         = array(
        /* give the name of the tables, without $_TABLES[] */
         'tables' => array(
             'translations',
            'originals',
            'votes',
            'gems',
            'awarded_gems',
            'blocked_users',
            'language_map',
            'remote_credentials' 
        ),
        /* give the full name of the group, as in the db */
        'groups' => array(
             'Crowd Translator Admin' 
        ),
        /* give the full name of the feature, as in the db */
        'features' => array(
             'Crowd Translator.admin' 
        ),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array( ),
        /* give all vars with their name */
        'vars' => array( ) 
    );
    return $out;
}
function plugin_getBlocks_crowdtranslator( $side, $topic = '' )
{
    
    
    global $_CROWDTRANSLATOR_DEFAULT, $_CROWDTRANSLATOR_CONF, $_USER, $_TABLES, $_CONF;
    
    require_once $_CONF[ 'path_system' ] . 'lib-database.php';
    $result = DB_query( "SELECT `id` FROM {$_TABLES['originals']} WHERE `language_array` = 'LANG01' AND `array_index` = '1'" );
    if ( DB_numRows( $result ) < 1 ) {
        require_once $_CONF[ 'path' ] . 'plugins/crowdtranslator/language_markup.php';
        add_identifier_to_lanugage_file();
    }
    
    $retval = array( );
    if ( $_CROWDTRANSLATOR_CONF[ 'enabled' ] == true && $side == "left" && !COM_isAnonUser() ) {
        $query  = "SELECT `user_id` FROM {$_TABLES['blocked_users']} WHERE `user_id`= {$_USER['uid']}";
        $result = DB_query( $query );
        if ( DB_numRows( $result ) == 0 ) {
            $display = "

            <div class='translator' id='translator'>

                <br>
                <span id='change_language'></span>
                <form id='translator_form' method=post >
                    <label id='translator_language_label'>Enter Language: </label>
                    <input id='translator_language' name='translator_language' placeholder='English' />
                    <input type='submit' id='language_select' />
                </form>

                <div class='progress_bar'> <div class='translated' id='translated'> </div> <div class='not_translated' id='not_translated'> </div> </div>

                <div id='submission_form' >
                </div>
            </div>
            ";
        } else {
            $display = "
            <div class='translator' id='translator'>
                <span class='error'> You have been blocked by the site admin and can not submit translations </span>
            </div>
            ";
        }
        $retval[ ] = array(
             'name' => 'crowdtranslator',
            'type' => 'dynamic',
            'onleft' => true,
            'title' => "Translator (<a style='font-size:9px' href='javascript:void(0)' onclick='show_guidelines()'>guidelines</a>)
           <a href='{$_CONF['site_url']}/crowdtranslator'><span class='badge-success notification_badge'></span></a>",
            'content' => $display,
            'allow_autotags' => false,
            'help' => '' 
        );
    }
    return $retval;
}
function plugin_getBlocksConfig_crowdtranslator( $side, $topic = '' )
{
    $retval = array( );
    if ( $side == "left" ) {
        $retval[ ] = array(
             'plugin' => 'crowdtranslator',
            'name' => 'Translator',
            'title' => 'Translator',
            'type' => 'dynamic',
            'onleft' => true,
            'allow_autotags' => false,
            'help' => '',
            'enable' => true,
            'topic_option' => $_CROWDTRANSLATOR_CONF[ 'block_topic_option' ],
            'inherit' => array( ) 
        );
    }
    return $retval;
}
function plugin_getheadercode_crowdtranslator( )
{
    global $_SCRIPTS;
    $_SCRIPTS->setJavaScriptFile( 'helper', '/crowdtranslator/string_extract.js' );
    $_SCRIPTS->setCSSFile( 'helper', '/crowdtranslator/style.css' );
    $_SCRIPTS->setJavaScriptLibrary( 'jquery.ui.autocomplete' );
    $_SCRIPTS->setJavaScriptLibrary( 'jquery.ui.menu' );
    $_SCRIPTS->setJavaScriptLibrary( 'jquery.ui.tooltip' );
    $_SCRIPTS->setJavaScriptLibrary( 'jquery' );
}
function plugin_profileblocksdisplay_crowdtranslator( $user_id )
{
    global $_CONF;
    require_once $_CONF[ 'path_html' ] . '/crowdtranslator/lib-translator.php';
    $display = COM_startBlock( "My badges <a  id='badges_show' href='javascript:void(0)' onclick='get_all_badges(0)'>(show all)</a>" );
    $display .= "<div class='index'>";
    $display .= get_user_badges( 4, 0, false );
    $display .= "</div>";
    $display .= COM_endBlock();
    
    
    $display .= "<div class='index'>";
    $translations = get_user_translations_table( 5 );
    $display .= COM_startBlock( "My translations" );
    $display .= " <div id='user_translations'> {$translations} </div>";
    $display .= COM_endBlock();
    $display .= "</div>";
    
    return $display;
}
?>