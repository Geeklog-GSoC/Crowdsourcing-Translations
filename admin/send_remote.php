<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | CrowdTranslator Plugin 0.1                                                |
// +---------------------------------------------------------------------------+
// | index.php                                                                 |
// |                                                                           |
// | Public plugin page                                                        |
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
 * @package CrowdTranslator
 */

/*
 * Script will take extracted array data find the original array values from the database
 * where all variables and html tags have been replaced with <tag> and create the 
 * HTML of the translation form
 * before it is saved to the database
 */

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once '../../../crowdtranslator/lib-translator.php';
require_once $_CONF[ 'path_system' ] . 'lib-database.php';

$display = '';

// Ensure user even has the rights to access this page
if ( !SEC_hasRights( 'crowdtranslator.admin' ) ) {
    $display .= COM_siteHeader( 'menu', $MESSAGE[ 30 ] ) . COM_showMessageText( $MESSAGE[ 29 ], $MESSAGE[ 30 ] ) . COM_siteFooter();
    
    // Log attempt to access.log
    COM_accessLog( "User {$_USER['username']} tried to illegally access the CrowdTranslator plugin administration screen." );
    
    echo $display;
    exit;
}



// MAIN
$display .= COM_siteHeader( 'menu', $LANG_CROWDTRANSLATOR_1[ 'plugin_name' ] );
$display .= "<p> Rules: <ul> <li> Only translations with more than 1 positive vote are sent </li>
<li> Translations which are already sent once will not be sent again </li>
<li> You need to get credentials from the site you are submiting to </li> </ul>";

$display .= COM_startBlock( "Peers" );
$new_peer_form = "<form method='post' action='' class='compact' id='add_peer'>
<div class='admin_basic'>
    <dl>
        <dt id='errors' style='color:red'></dt>
        <dt><label for='site_name'>Enter peer site name:</label></dt> <dd><input type='text' id='site_name' name='site_name' placeholder='Geeklog' /></dd>
        <dt><label for='site_credentials'>Enter site credentials: </label></dt> <dd><input type='password' id='site_credentials' name='site_credentials' placeholder='MyCr3denti4Ls' /></dd>
        <dt><input type='submit' value='Submit' /></dt>
    </dl>
</div>
</form>";

$display .= $new_peer_form;
$display .= get_peer_list();

$display .= COM_endBlock();

$display .= COM_startBlock( "Submision form" );

$form = credentials_form();

if ( isset( $site_url ) && !empty( $site_url ) ) {
    $form .= "<br><dt><h4> You are submiting: </h4></dt>";
    $language_array = get_remote_language_array();
    $count          = 0;
    foreach ( $language_array as $key => $value ) {
        $form .= "<dt><label> {$value->string} </label></dt> <dd><input type='text'  id='translator_input_{$count}' value='{$value->translation}' name='translator_input_{$count}' readonly /></dd>";
        $form .= "<input id='translator_input_{$count}_hidden' style='display:none' name='translator_input_{$count}_hidden' value='{$value->metadata}' readonly />";
        ++$count;
    }
    $form .= "<dt><label>Total: </label> <dd> <input type='text' name='count' id='count' value='{$count}' readonly /></dd> </dl></div></form>";
}





$display .= $form;
$display .= COM_endBlock();
$display .= COM_siteFooter();
echo $display;

/**
* Get the list of sites allowed to submit translations
* @return string HTML code for the list
*/
function get_peer_list( )
{
    global $_TABLES;
    
    $query  = "SELECT `site_name` FROM {$_TABLES['remote_credentials']} WHERE 1";
    $result = DB_query( $query );
    
    $return = "<ul> Peer list: ";
    
    if ( DB_numRows( $result ) < 1 ) {
        $return .= "<li>list is empty</li>";
    }
    
    while ( $row = DB_fetchArray( $result ) ) {
        $return .= "<li id='{$row['site_name']}'> {$row['site_name']}  <a href='javascript:void(0)' onclick='remove_peer(\"{$row['site_name']}\")'> Remove </a> </li>";
    }
    
    $return .= "</ul>";
    
    return $return;
}


/**
* Generates the form for submiting translations to a remote web page
* @return string HTML code for the form
*/
function credentials_form( )
{
    global $_TABLES, $language, $site_url, $site_name, $site_credentials;
    
    if ( !isset( $_POST[ 'site_url' ] ) || empty( $_POST[ 'site_url' ] ) || !isset( $_POST[ 'site_name' ] ) || empty( $_POST[ 'site_name' ] ) || !isset( $_POST[ 'site_credentials' ] ) || empty( $_POST[ 'site_credentials' ] ) || !isset( $_POST[ 'language' ] ) && empty( $_POST[ 'language' ] ) ) {
        $options = '';
        $result  = DB_query( "SELECT DISTINCT language_full_name, language_file FROM {$_TABLES['translations']}" );
        if ( DB_numRows( $result ) > 0 ) {
            while ( $language = DB_fetchArray( $result ) ) {
                if ( !array_key_exists( $language[ 'language_file' ], $language ) )
                    $lang[ $language[ 'language_file' ] ] = $language[ 'language_full_name' ];
            }
            foreach ( $lang as $key => $value ) {
                $options .= "<option value={$key}>{$value}</option>";
            }
            $form = "<form action='./send_remote.php' method='post' class='compact'>
    <div class='admin_basic'>
        <dl>
         <dt><label for='language'>Language: </label> </dt> <dd> <select id='language' name='language' > {$options} </select> </dd>
         <dt><label for='site_url'>Enter site url: http://www.</label></dt> <dd><input type='text' id='site_url' name='site_url' placeholder='geeklog.net' /> </dd>
         <dt><label for='site_name'>Enter your site name:</label></dt> <dd><input type='text' id='site_name' name='site_name' placeholder='Geeklog' /></dd>
         <dt><label for='site_credentials'>Enter your credentials: </label></dt> <dd><input type='password' id='site_credentials' name='site_credentials' placeholder='MyCr3denti4Ls' /></dd>
         <dt><input type='submit' value='Submit' /></dt>
     </dl>
 </div>
</form>";
        } else {
            $form = "<p style='color:red'>You have not translations to send.</p>";
        }
    } else {
        $language = $_POST[ 'language' ];
        $site_url = "http://www." . $_POST[ 'site_url' ];
        $site_url .= '/crowdtranslator/recieve_remote.php';
        $site_name        = $_POST[ 'site_name' ];
        $site_credentials = $_POST[ 'site_credentials' ];
        
        $form = "<form action='{$site_url}' method='post' class='compact'>
    <div class='admin_basic'>
        <dl>
         <dt><label for='language'>Language: </label> </dt> <dd> <select id='language' name='language' > <option>{$_POST['language']}</option> </select> </dd>
         <dt><label for='site_url'>Enter site url: http://www.</label></dt> <dd><input type='text' id='site_url' name='site_url' value='{$_POST['site_url']}' /> </dd>
         <dt><label for='site_name'>Enter your site name:</label></dt> <dd><input type='text' id='site_name' name='site_name' value='{$_POST['site_name']}' /></dd>
         <dt><label for='site_credentials'>Enter your credentials: </label></dt> <dd><input type='password' id='site_credentials' name='site_credentials' value='{$_POST['site_credentials']}' /></dd>
         <dt><input type='submit' value='Submit' /></dt>";
    }
    
    return $form;
}

/**
 * @param string form The HTML of the translation form
 * @param int count number of current input field
 * @param object value current translation object, holds all relevant data for the string
 * @param string base_url base url for the required resources
 * @param string disable_up Will either be empty string or disable if the vote button should be disabled
 * @param string disable_down Will either be empty string or disable if the vote button should be disabled
 */
function add_remote_form_element( &$form, $count, $value, $base_url, $disabled_up, $disabled_down )
{
    
    $form_label = "<label for='translator_input_{$count}'>{$value->string}</label>";
    
    $form_input2       = "<input type='text' id='translator_input_{$count}' value='{$value->translation}'name='translator_input_{$count}' /><button>Remove this from submission</button>";
    $form_hidden_input = "<input id='translator_input_{$count}_hidden' class='hidden' name='translator_input_{$count}_hidden' value='{$value->metadata}' />";
    if ( strlen( $value->translation ) > 0 ) {
        if ( $count > 5 ) {
            $template = "<span id='input_span_{$count}' class='group_input temp_hidden'>{$form_label} {$form_input2}<label>{$form_hidden_input}</span>";
        } else {
            $template = "<span id='input_span_{$count}' class='group_input'>{$form_label}{$form_input2}{$form_hidden_input}</span>";
        }
    }
    $form .= $template;
}

/**
* Generates an array of objects holding translations and their metadata
* @return array Objects holding translations and their metadata
*/
function get_remote_language_array( )
{
    global $_TABLES, $language;
    
    $query  = "SELECT t.*, o.string FROM {$_TABLES['translations']} as t INNER JOIN {$_TABLES['originals']} as o on 
    t.plugin_name = o.plugin_name AND t.language_array = o.language_array AND t.array_key = o.array_index AND t.array_subindex = o.sub_index
    WHERE `language_full_name` = '{$language}' AND `approval_counts` > '1'";
    $result = DB_query( $query );
    
    $language_array = array( );
    while ( $row = DB_fetchArray( $result ) ) {
        $obj                 = new stdClass();
        $array               = $row[ 'language_array' ];
        $index               = $row[ 'array_key' ];
        $subindex            = $row[ 'array_subindex' ];
        $obj->array_name     = $array;
        $obj->array_index    = $index;
        $obj->array_subindex = $subindex;
        $obj->metadata       = "array_{$array}index_{$index}subindex_{$subindex}";
        $obj->translation    = $row[ 'translation' ];
        $obj->string         = $row[ 'string' ];
        array_push( $language_array, $obj );
    }
    return $language_array;
}

?>