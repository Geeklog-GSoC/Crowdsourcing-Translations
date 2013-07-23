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

if (strpos(strtolower($_SERVER['PHP_SELF']), 'functions.inc') !== false) {
    die ('This file can not be used on its own.');
}

/**
 * Language file include
 */
$plugin_path = $_CONF['path'] . 'plugins/crowdtranslator/';
$langfile = $plugin_path . 'language/' . $_CONF['language'] . '.php';

if (file_exists($langfile)) {
    require_once $langfile;
} else {
    require_once $plugin_path . 'language/english.php';
}

/**
* Check and see if we need to load the plugin configuration
*/ 
if (!isset($_CROWDTRANSLATOR_CONF)) {
    require_once $_CONF['path_system'] . 'classes/config.class.php';
    
    $CROWDTRANSLATOR_config = config::get_instance();
    $_CROWDTRANSLATOR_CONF = $CROWDTRANSLATOR_config->get_config('crowdtranslator');
}
/**
 * crowdtranslator plugin table(s)
 */
$_TABLES['translations'] = $_DB_table_prefix . 'translations';
$_TABLES['originals'] = $_DB_table_prefix . 'originals';
$_TABLES['votes'] = $_DB_table_prefix . 'votes';
$_TABLES['gems'] = $_DB_table_prefix . 'gems';
$_TABLES['awarded_gems'] = $_DB_table_prefix . 'awarded_gems';
$_TABLES['blocked_users'] = $_DB_table_prefix . 'blocked_users';


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
function plugin_getmenuitems_crowdtranslator()
{
    global $_CONF, $LANG_CROWDTRANSLATOR_1;

    $url = $_CONF['site_url'] . '/crowdtranslator/index.php';
    $menuitems[$LANG_CROWDTRANSLATOR_1['plugin_name']] = $url;

    return $menuitems;
}

/**
* Geeklog is asking us to provide any items that show up in the type
* drop-down on search.php.  Let's users search for links.
*
* @return   array   (plugin name/entry title) pair for the dropdown
*
*/
/*
function plugin_searchtypes_crowdtranslator()
{
    global $LANG_CROWDTRANSLATOR_1;

    $tmp['crowdtranslator'] = $LANG_CROWDTRANSLATOR_1['plugin_name'];

    return $tmp;
}
*/

/**
* This searches for entries matching the user query and returns an array for the
* header and table rows back to search.php where it will be formated and printed
*
* @param    string  $query      Keywords user is looking for
* @param    date    $datestart  Start date to get results for
* @param    date    $dateend    End date to get results for
* @param    string  $topic      The topic they were searching in
* @param    string  $type       Type of items they are searching, or 'all' (deprecated)
* @param    int     $author     Get all results by this author
* @param    string  $keyType    search key type: 'all', 'phrase', 'any'
* @param    int     $page       page number of current search (deprecated)
* @param    int     $perpage    number of results per page (deprecated)
* @return   object              search result object
*
*/
/*
function plugin_dopluginsearch_crowdtranslator($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage)
{
    global $_TABLES, $LANG_CROWDTRANSLATOR_1;

    // Make sure the query is SQL safe
    $query = trim(addslashes($query));

    // NOTE: Update SQL query to match the actual table used by the
    //       crowdtranslator plugin!

    $sql = "SELECT Cid AS id, title, description, UNIX_TIMESTAMP(date) AS date, owner_id AS uid, hits, ";
    $sql .= "CONCAT('/crowdtranslator/index.php?item=', Cid) AS url ";
    $sql .= "FROM {$_TABLES['crowdtranslator']} WHERE date <> 1 ";
    $sql .= COM_getPermSQL('AND') . ' ';

    if (!empty($datestart) && !empty($dateend)) {
        $delim = substr($datestart, 4, 1);
        if (!empty($delim)) {
            $DS = explode($delim, $datestart);
            $DE = explode($delim, $dateend);
            $startdate = mktime(0,0,0,$DS[1],$DS[2],$DS[0]);
            $enddate = mktime(23,59,59,$DE[1],$DE[2],$DE[0]);
            $sql .= "AND (UNIX_TIMESTAMP(date) BETWEEN '$startdate' AND '$enddate') ";
        }
    }
    if (!empty ($author)) {
        $sql .= "AND (owner_id = '$author') ";
    }

    $search = new SearchCriteria('crowdtranslator', $LANG_CROWDTRANSLATOR_1['plugin_name']);
    $columns = array('title', 'description');
    list($sql,$ftsql) = $search->buildSearchSQL($keyType, $query, $columns, $sql);
    $search->setSQL($sql);
    $search->setFTSQL($ftsql);
    $search->setRank(3);

    return $search;
}
*/

/**
* Implements a [crowdtranslator:] autotag.
*
* @param    string  $op         operation to perform
* @param    string  $content    item (e.g. story text), including the autotag
* @param    array   $autotag    parameters used in the autotag
* @param    mixed               tag names (for $op='tagname') or formatted content
*
*/
/*
function plugin_autotags_crowdtranslator($op, $content = '', $autotag = '')
{
    global $_CONF, $_TABLES, $LANG_CROWDTRANSLATOR_1;

    if ($op == 'tagname') {
        return 'crowdtranslator';
    } elseif ($op == 'parse') {
        $Cid = COM_applyFilter($autotag['parm1']);
        if (! empty($Cid)) {
            $url = $_CONF['site_url'] . '/crowdtranslator/index.php?item=' . $Cid;
            if (empty($autotag['parm2'])) {
                $linktext = 'crowdtranslator'; // or get title from db
            } else {
                $linktext = $autotag['parm2'];
            }
            $link = COM_createLink($linktext, $url);
            $content = str_replace($autotag['tagstr'], $link, $content);
        }

        return $content;
    }
}
*/

/**
* Return plugin entry for "Command and Control" (moderation.php)
*
* @return  array  Array containing (plugin name, admin url, url of plugin icon)
*
*/
function plugin_cclabel_crowdtranslator()
{
    global $_CONF, $LANG_CROWDTRANSLATOR_1;

    $retval = array();
    if (SEC_hasRights('crowdtranslator.admin')) {
        $retval = array($LANG_CROWDTRANSLATOR_1['plugin_name'],
            $_CONF['site_admin_url'] . '/plugins/crowdtranslator/index.php',
            plugin_geticon_crowdtranslator());
    }

    return $retval;
}

/**
* Return plugin entry for the Admins Only block
*
* @return array Array containing (plugin name, plugin admin url, # of plugin items or '')
*/
function plugin_getadminoption_crowdtranslator()
{
    global $_CONF, $LANG_CROWDTRANSLATOR_1;

    if (SEC_hasRights('crowdtranslator.admin')) {
        return array($LANG_CROWDTRANSLATOR_1['plugin_name'],
            $_CONF['site_admin_url'] . '/plugins/crowdtranslator/index.php', 0);
    }
}

/**
* Returns the URL of the plugin's icon
*
* @return   string      URL of the icon
*
*/
function plugin_geticon_crowdtranslator()
{
    global $_CONF;

    // popular alternative location:
    // return $_CONF['site_url'] . '/crowdtranslator/images/crowdtranslator.png';

    return $_CONF['site_admin_url'] . '/plugins/crowdtranslator/images/crowdtranslator.png';
}

/**
 * Returns the current plugin code version
 *
 * @return string    plugin version
 */
function plugin_chkVersion_crowdtranslator()
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/crowdtranslator/autoinstall.php';

    $inst_parms = plugin_autoinstall_crowdtranslator('crowdtranslator');

    return $inst_parms['info']['pi_version'];
}

/**
* Update the plugin
*
* @return   int     Number of message to display or true for success
*
*/
function plugin_upgrade_crowdtranslator()
{
    global $_CONF, $_TABLES;

    $installed_version = DB_getItem($_TABLES['plugins'], 'pi_version',
        "pi_name = 'crowdtranslator'");
    $code_version = plugin_chkVersion_crowdtranslator();
    if ($installed_version == $code_version) {
        // nothing to do
        return true;
    }

    require_once $_CONF['path'] . 'plugins/crowdtranslator/autoinstall.php';

    if (! plugin_compatible_with_this_version_crowdtranslator('crowdtranslator')) {
        return 3002;
    }

    // other update code goes here

    // update plugin version number
    $inst_parms = plugin_autoinstall_crowdtranslator('crowdtranslator');
    $pi_gl_version = $inst_parms['info']['pi_gl_version'];

    DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '$code_version', pi_gl_version = '$pi_gl_version' WHERE pi_name = 'crowdtranslator'");

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
function plugin_autouninstall_crowdtranslator()
{
    require_once $plugin_path."/language_markup.php";
    remove_identifier_to_lanugage_file();
    $out = array (
        /* give the name of the tables, without $_TABLES[] */
        'tables' => array('translations', 'originals', 'votes', 'gems', 'awarded_gems', 'blocked_users'),
        /* give the full name of the group, as in the db */
        'groups' => array('crowdtranslator Admin'),
        /* give the full name of the feature, as in the db */
        'features' => array('crowdtranslator.admin'),
        /* give the full name of the block, including 'phpblock_', etc */
        'php_blocks' => array(),
        /* give all vars with their name */
        'vars' => array()
        );

    return $out;
}


function plugin_getBlocks_crowdtranslator($side, $topic=''){

    global $_CROWDTRANSLATOR_DEFAULT,$_CROWDTRANSLATOR_CONF, $_USER, $_TABLES;

    $retval=array();

    

    if( $_CROWDTRANSLATOR_CONF['enabled']==true && $side=="left" && !COM_isAnonUser() ){

        $query="SELECT `user_id` FROM {$_TABLES['blocked_users']} WHERE `user_id`= {$_USER['uid']}";
        $result=DB_query($query);
        if(DB_numRows($result)==0){
            $display="

            <div class='translator' id='translator'>

            </br>
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


        $retval[] = array('name'           => 'crowdtranslator',
          'type'           => 'dynamic',
          'onleft'         => true,
          'title'         => "Translator (<a style='font-size:9px' href='javascript:void(0)' onclick='show_guidelines()'>guidelines</a>)",
          'content'        => $display,
          'allow_autotags' => false,
          'help'           => '');
    }

    return $retval;
}

function plugin_getBlocksConfig_crowdtranslator($side, $topic='')
{


    $retval = array();
    
    

    if($side=="left"){    
        $retval[] = array('plugin'         => 'crowdtranslator',
          'name'           => 'Translator',
          'title'          => 'Translator',
          'type'           => 'dynamic',
          'onleft'         => true,
          'allow_autotags' => false,
          'help'           => '',
          'enable'         => true,
          'topic_option'   => $_CROWDTRANSLATOR_CONF['block_topic_option'],
          'inherit'        => array()
          );
    }
    
    return $retval;
}

function plugin_getheadercode_crowdtranslator()
{
    global $_SCRIPTS;
    $_SCRIPTS->setJavaScriptFile('helper', '/crowdtranslator/string_extract.js');
    $_SCRIPTS->setCSSFile('helper', '/crowdtranslator/style.css');
    $_SCRIPTS->setJavaScriptLibrary('jquery.ui.autocomplete');
    $_SCRIPTS->setJavaScriptLibrary('jquery.ui.menu');
    $_SCRIPTS->setJavaScriptLibrary('jquery');
    
    
}


?>