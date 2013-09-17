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


/* when using AJAX the function is specified, this is wherre it is retrieved
 */
$function = '';
if ( isset( $_REQUEST[ 'function' ] ) && !empty( $_REQUEST[ 'function' ] ) ) {
    $function = $_REQUEST[ 'function' ];
} //isset( $_REQUEST[ 'function' ] ) && !empty( $_REQUEST[ 'function' ] )
/* If the function is set we include the lib-common
othervise lib-common should be included by the lib user page
*/
if ( ( isset( $function ) && !empty( $function ) ) )
    include_once '../lib-common.php';
/* Geeklogs security protocol */
if ( strpos( strtolower( $_SERVER[ 'PHP_SELF' ] ), 'lib-translator.php' ) !== false && $function == '' ) {
    include_once '../lib-common.php';
    echo COM_refresh( $_CONF[ 'site_url' ] . '/index.php' );
    exit;
} //strpos( strtolower( $_SERVER[ 'PHP_SELF' ] ), 'lib-translator.php' ) !== false && $function == ''
require_once $_CONF[ 'path_system' ] . 'lib-database.php';
include_once $_CONF[ 'path' ] . 'plugins/crowdtranslator/custom_string_replace.php';
/* If the lib is used by AJAX this is where its decided
which function will be called
*/
if ( $function == 'get_user_translations_table' ) {
    echo get_user_translations_table();
} //$function == 'get_user_translations_table'
elseif ( $function == 'get_translations_table' ) {
    echo get_translations_table();
} //$function == 'get_translations_table'
    elseif ( $function == 'delete_translation' ) {
    echo delete_translation();
} //$function == 'delete_translation'
    elseif ( $function == 'get_user_badges' ) {
    echo get_user_badges();
} //$function == 'get_user_badges'
else if ( $function == 'block_user' ) {
    echo block_user();
} //$function == 'block_user'
else if ( $function == 'remove_block' ) {
    echo remove_block();
} //$function == 'remove_block'
else if ( $function == 'add_peer' ) {
    echo add_peer();
} else if ( $function == 'remove_peer' ) {
    echo remove_peer();
} else if ( $function == 'get_languages' ) {
    echo get_languages();
} else if ( $function == 'vote' ) {
    echo vote();
} else if ( $function == 'get_original_language_values' ) {
    echo get_original_language_values();
} else if ( $function == 'submit_translation' ) {
    echo submit_translation();
}





/**
 * Removing a single translation from translations table as well as its votes from votes table
 * @param integer id the unique id of the translation to be removed
 * @return boolean true if the deletion was successfull, false othervise
 */
function delete_translation( $id = null )
{
    global $_TABLES;
    if ( isset( $_REQUEST[ 'id' ] ) && !empty( $_REQUEST[ 'id' ] ) ) {
        $id = $_REQUEST[ 'id' ];
    } //isset( $_REQUEST[ 'id' ] ) && !empty( $_REQUEST[ 'id' ] )
    if ( $id != null ) {
        $query  = "DELETE FROM {$_TABLES['translations']} WHERE `id`={$id}";
        $result = DB_query( $query );
        if ( $result ) {
            $query  = "DELETE FROM {$_TABLES['votes']} WHERE `translation_id` = {$id} ";
            $result = DB_query( $query );
            return true;
        } //$result
        else {
            return false;
        }
    } //$id != null
}
/**
 *     Get the sum of all aprovals accross translations for current user
 * @return the sum of all approvals for a single user
 */

function get_total_approval_for_user( )
{
    global $_USER, $_TABLES;
    $query  = "SELECT SUM(`approval_counts`) as sum FROM {$_TABLES['translations']} WHERE `user_id` = {$_USER['uid']}";
    $result = DB_query( $query );
    $sum    = DB_fetchArray( $result );
    $sum    = $sum[ 'sum' ];
    return $sum > 0 ? $sum : "0";
}

/**
 * @param integer user_id If set to null the function will return all languages being translated
 * @return The HTML of the progress bars for each retrieved language
 */
function get_user_translated_languages( $user_id = null )
{
    global $_TABLES;
    $query   = "SELECT DISTINCT `language_full_name` as language FROM {$_TABLES['translations']} WHERE `user_id` = {$user_id}";
    $result  = DB_query( $query );
    $display = '';
    while ( $row = DB_fetchArray( $result ) ) {
        $translated     = get_translation_percent( $row[ 'language' ] );
        $translated     = round( $translated, 2 );
        $not_translated = 100 - $translated;
        $query          = "SELECT COUNT(`id`) as count FROM {$_TABLES['translations']} WHERE `user_id` = {$user_id} AND `language_full_name`='{$row['language']}'";
        $result2        = DB_query( $query );
        $count          = DB_fetchArray( $result2 );
        $count          = $count[ 'count' ];
        $display .= "<div class='index_language_graph'> <h3> {$row['language']} </h3>
        <div class='progress_bar'> <span class='translated' style='width: {$translated}%'> {$translated}% </span> " . "<span class='not_translated' style='width: {$not_translated}%'> {$not_translated}% </span> </div> </div>
        <span> Translated by you: {$count} </span>";
    } //$row = DB_fetchArray( $result )
    return $display;
}

/**
 *  Calculates the percentage of translation for a language
 *     @param string language The for which the percentage is calculated
 *   @return float The percentage of translatiousn
 */
function get_translation_percent( $language = null )
{
    global $_TABLES;
    if ( $language == null ) {
        $language = $_COOKIE[ 'selected_language' ];
    } //$language == null
    $result                        = DB_query( "SELECT COUNT(`id`) as count FROM {$_TABLES['originals']} " );
    $number_of_original_elements   = DB_fetchArray( $result );
    $number_of_original_elements   = $number_of_original_elements[ 'count' ];
    $result                        = DB_query( "SELECT COUNT(DISTINCT `language_array`,`array_key`) as count FROM {$_TABLES['translations']} WHERE `language_full_name`='{$language}'" );
    $number_of_translated_elements = DB_fetchArray( $result );
    $number_of_translated_elements = $number_of_translated_elements[ 'count' ];
    $translated                    = ( $number_of_translated_elements / $number_of_original_elements ) * 100;
    return (float) $translated;
}

/**
 * Retrieves badges accumulated in admin mode retrieves all available badges
 * @param int limit The number of badges to be displayed
 * @param int admin Weather to use user or admin mode 
 */
function get_user_badges( $limit = -1, $admin = 0, $show_not_awarded = true )
{
    global $_USER, $_TABLES;
    $display = '';
    if ( ( $admin == 0 ) && isset( $_REQUEST[ 'admin' ] ) && !empty( $_REQUEST[ 'admin' ] ) ) {
        $admin = $_REQUEST[ 'admin' ];
    } //( $admin == 0 ) && isset( $_REQUEST[ 'admin' ] ) && !empty( $_REQUEST[ 'admin' ] )
    if ( isset( $admin ) && !empty( $admin ) && $admin == 1 ) {
        if ( $limit > 0 )
            $limit = "LIMIT {$limit}";
        else
            $limit = "";
        $query = "SELECT  `title`, `tooltip`, `image` FROM {$_TABLES['gems']} WHERE '1' {$limit} ";
        $gems  = DB_query( $query );
        $count = 0;
        while ( $gem = DB_fetchArray( $gems ) ) {
            $display .= display_badge( $gem, $count );
            $count++;
        } //$gem = DB_fetchArray( $gems )
        return $display;
    } //isset( $admin ) && !empty( $admin ) && $admin == 1
    if ( $limit > 0 ) {
        $query = "SELECT g.title, g.tooltip, g.image, a.award_lvl FROM {$_TABLES['awarded_gems']} as a INNER JOIN {$_TABLES['gems']} as g ON a.gem_id = g.gem_id  WHERE a.user_id = {$_USER['uid']} LIMIT {$limit}";
    } //$limit > 0
    else {
        $query = "SELECT g.title, g.tooltip, g.image, a.award_lvl FROM {$_TABLES['awarded_gems']} as a INNER JOIN {$_TABLES['gems']} as g ON a.gem_id = g.gem_id  WHERE a.user_id = {$_USER['uid']}";
    }
    $result = DB_query( $query );
    $limit -= DB_numRows( $result );
    $count = 0;
    if ( DB_numRows( $result ) > 0 ) {
        while ( $row = DB_fetchArray( $result ) ) {
            if ( $row[ 'award_lvl' ] > 0 )
                $award_lvl = "level {$row['award_lvl']}";
            else
                $award_lvl = '';
            $display .= display_badge( $row, $count, $award_lvl );
        } //$row = DB_fetchArray( $result )
    } //DB_numRows( $result ) > 0
    if ( $show_not_awarded == true ) {
        if ( $limit > 0 ) {
            $query = "SELECT g.title, g.tooltip, g.image FROM {$_TABLES['gems']} g WHERE g.gem_id NOT IN (SELECT a.gem_id FROM {$_TABLES['awarded_gems']} a WHERE a.user_id= {$_USER['uid']} ) LIMIT {$limit}";
        } //$limit > 0
        elseif ( $limit < 0 ) {
            $query = "SELECT g.title, g.tooltip, g.image FROM {$_TABLES['gems']} g WHERE g.gem_id NOT IN (SELECT a.gem_id FROM {$_TABLES['awarded_gems']} a WHERE a.user_id= {$_USER['uid']} )";
        } //$limit < 0
        $result = DB_query( $query );
        while ( $row = DB_fetchArray( $result ) ) {
            $display .= display_badge( $row, $count, '', 'disabled_badge' );
        } //$row = DB_fetchArray( $result )
    } //$show_not_awarded == true
    return $display;
}

/**
 * When displaying badges this is where the actuall HTML code is assembled
 * @param object gem The badge data retrieved from database
 * @param int count Keeps count on number of displayed gems, gems will be displayed 4 in a row
 */
function display_badge( $gem, $count, $lvl = '', $disabled = '' )
{
    global $_CONF;
    $base_url = $_CONF[ 'site_url' ] . "/crowdtranslator/images/badges/";
    $display  = "<div class='achievement {$disabled}' title='{$gem['tooltip']} {$lvl}' >" . "<div class='badge' > <img src='{$base_url}{$gem['image']}' /></div>" . "<p class='achievement_name'>{$gem['title']}</br>{$lvl}</p></div>";
    if ( ++$count % 4 == 0 )
        $display .= "</br>";
    return $display;
}

/**
 * Get the number of votes casted by current user
 * @return returns number of votes casted by current user
 */
function get_user_votes( )
{
    global $_USER, $_TABLES;
    $query  = "SELECT COUNT(`user_id`) as count FROM {$_TABLES['votes']} WHERE `user_id` = {$_USER['uid']}";
    $result = DB_query( $query );
    $result = DB_fetchArray( $result );
    return $result[ 'count' ];
}

/**
 * Returns number of translations submited by single user or in total depending on rge $admin param
 * @param int admin Indicates if the function will return the number of votes submited by one user or in total
 * @return int returns number of translations submited by current user/in total
 */
function get_translated_count( $admin )
{
    global $_TABLES, $_USER;
    if ( $admin == 1 )
        $query = "SELECT COUNT(`id`) as count FROM {$_TABLES['translations']} WHERE 1";
    else
        $query = "SELECT COUNT(`id`) as count FROM {$_TABLES['translations']} WHERE `user_id`= {$_USER['uid']}";
    $result = DB_query( $query );
    $result = DB_fetchArray( $result );
    return $result[ 'count' ];
}



function get_remote_submission_count( )
{
    global $_TABLES, $_USER;
    
    $query  = "SELECT COUNT(`id`) as count FROM {$_TABLES['translations']} WHERE `user_id`= '-1' ";
    $result = DB_query( $query );
    $result = DB_fetchArray( $result );
    return $result[ 'count' ];
}

/**
 * @return int number of votes casted accross translations
 */
function get_votes_count( )
{
    global $_TABLES;
    $query  = "SELECT COUNT(`user_id`) as count FROM {$_TABLES['votes']} WHERE 1";
    $result = DB_query( $query );
    $result = DB_fetchArray( $result );
    return $result[ 'count' ];
}

/**
 * Returns translation with the most upvotes
 * @param int criterion if set to zero returns the bigest number of upvotes for translations by current user, otherwise the bigest number of upvotes
 * @return int bigest number of upvotes for single translation
 */
function get_most_upvotes( $criterion )
{
    global $_TABLES, $_USER;
    if ( $criterion == 0 )
        $criterion = "`user_id`={$_USER['uid']}";
    $query  = "SELECT MAX(`approval_counts`) as max FROM {$_TABLES['translations']} WHERE {$criterion}";
    $result = DB_query( $query );
    $result = DB_fetchArray( $result );
    $result = $result[ 'max' ];
    if ( !$result )
        $result = 0;
    return $result;
}

/**
 * Returns number of users translating
 * @return int number of users using the plugin
 */
function get_users_translating( )
{
    global $_TABLES;
    $query  = "SELECT COUNT( DISTINCT (`user_id`) ) as count FROM {$_TABLES['translations']} WHERE 1";
    $result = DB_query( $query );
    $result = DB_fetchArray( $result );
    return $result[ 'count' ];
}

/**
 * Returns number of languages being translated
 * @return int number of distinct language names in the database
 */
function get_languages_translated_count( )
{
    global $_TABLES;
    $query  = "SELECT  COUNT( DISTINCT `language_full_name`) as count FROM {$_TABLES['translations']} WHERE 1";
    $result = DB_query( $query );
    $result = DB_fetchArray( $result );
    return $result[ 'count' ];
}

/**
 * Returns number of translations with negative approval count
 * @return int number of translations with negative aproval_count
 */
function get_translations_with_negative_vote_count( )
{
    global $_TABLES;
    $query  = "SELECT  COUNT( `id`) as count FROM {$_TABLES['translations']} WHERE `approval_counts`<0 ";
    $result = DB_query( $query );
    $result = DB_fetchArray( $result );
    return $result[ 'count' ];
}

/**
 * Returns HTML code for the progress bars of languages being translated
 * @return string HTML code of progress bars for languages being translated
 */
function get_translated_languages( )
{
    global $_TABLES;
    $query   = "SELECT DISTINCT `language_full_name` as language FROM {$_TABLES['translations']} WHERE 1";
    $result  = DB_query( $query );
    $display = '';
    while ( $row = DB_fetchArray( $result ) ) {
        $translated     = get_translation_percent( $row[ 'language' ] );
        $translated     = round( $translated, 2 );
        $not_translated = 100 - $translated;
        $query          = "SELECT COUNT(`id`) as count FROM {$_TABLES['translations']} WHERE `language_full_name`='{$row['language']}'";
        $result2        = DB_query( $query );
        $count          = DB_fetchArray( $result2 );
        $count          = $count[ 'count' ];
        $display .= "<div class='index_language_graph'>  <h2> {$row['language']} <a href='javascript:void(0)'' onclick='pack_translation(\"{$row['language']}\")'>(pack this)</a></h2> 
        <div class='progress_bar'> <span class='translated' style='width: {$translated}%'> {$translated}% </span> " . "<span class='not_translated' style='width: {$not_translated}%'> {$not_translated}% </span> </div> </div>
        <span> Translated: {$count} </span>";
    } //$row = DB_fetchArray( $result )
    return $display;
}
/**
 * When an AJAX call is used to show the table with translations the params are retrieved here 
 * @param int &limit the number of translations to be shown
 * @param int &start the first translation to be shown
 * @param string &order_by the ordering of translations
 * @see get_translations_table_query
 */
function get_translations_options( &$limit, &$start, &$order_by )
{
    if ( isset( $_REQUEST[ 'limit' ] ) && !empty( $_REQUEST[ 'limit' ] ) ) {
        $limit = $_REQUEST[ 'limit' ];
    } //isset( $_REQUEST[ 'limit' ] ) && !empty( $_REQUEST[ 'limit' ] )
    if ( isset( $_REQUEST[ 'start' ] ) && !empty( $_REQUEST[ 'start' ] ) ) {
        $start = $_REQUEST[ 'start' ];
    } //isset( $_REQUEST[ 'start' ] ) && !empty( $_REQUEST[ 'start' ] )
    if ( isset( $_REQUEST[ 'order_by' ] ) && !empty( $_REQUEST[ 'order_by' ] ) ) {
        $order_by = $_REQUEST[ 'order_by' ];
    } //isset( $_REQUEST[ 'order_by' ] ) && !empty( $_REQUEST[ 'order_by' ] )
    
}

/**
 * The html code of the table header for the table displaying translations
 * @param int admin if set to 1 the table will have a username header
 * @param int limit number of translations to be shown, used for onclick method setting inside the headers
 * @return string HTML code for the table  header
 */
function get_translations_table_headers( $admin, $limit )
{
    $display = "<table class='translations_view'> <tbody> <tr> ";
    
    if ( $admin == 1 )
        $display .= "<th> <a href='javascript:void(0)' onclick='show_more_translations({$limit}, -1, 1, \"`user_id` DESC\")'> User\Site </a> 
    <a href='javascript:void(0)' onclick='show_more_translations({$limit}, -1, {$admin}, \"`user_id` ASC\")'> (ASC) </a> </th>";
    $display .= "<th> <a href='javascript:void(0)' onclick='show_more_translations({$limit}, -1, 1, \"`language_full_name` DESC\")'> Language </a>
    <a href='javascript:void(0)' onclick='show_more_translations({$limit}, -1, {$admin}, \"`language_full_name` ASC\")'> (ASC) </a> </th>
    <th> Translation </a> </th>
    <th> <a href='javascript:void(0)' onclick='show_more_translations({$limit}, -1, {$admin}, \"`approval_counts` DESC\")'> Upvotes </a>
        <a href='javascript:void(0)' onclick='show_more_translations({$limit}, -1, {$admin}, \"`approval_counts` ASC\")'> (ASC) </a> </th> 
        <th> <a href='javascript:void(0)' onclick='show_more_translations({$limit}, -1, {$admin}, \"`posted` DESC\")'> Posted </a> 
            <a href='javascript:void(0)' onclick='show_more_translations({$limit}, -1, {$admin}, \"`posted` ASC\")'> (ASC) </a></th> 
            <th> </th> </tr> ";
    return $display;
}

/**
 * Assembles last row of the translations table ncludes the click for Previous show, Next show and input box for limit
 * @param int previous indicates the first translation to be shown
 * @param int next indicates the first translation to be shown
 * @param int admin used for onclick method inside the code indicating if the table is admin mode or user mode
 * @param int limit used for onclick method inside the code indicating the number of translations to be shown
 * @return string display HTML code of the last table row which includes the click for Previous show, Next show and input box for limit
 */
function get_translations_table_finalize( $previous, $next, $admin, $limit, $order_by = '`posted`, `id` DESC' )
{
    $display = '';
    if ( $previous >= 0 ) {
        $display .= "<td>  <a href='javascript:void(0)' onclick='show_more_translations({$limit}, {$previous}, {$admin}, \"{$order_by}\")'> <- Show previous </a> </td>";
    } //$previous >= 0
    else {
        $display .= "<td></td>";
    }
    $display .= "<td><label for='limit' >Show: </label> <input type='text' id='limit' class='small' value='{$limit}' onblur='translation_table_change_limit()'/></td>";
    if ( $next < get_translated_count( $admin ) ) {
        $display .= " <td> <a id='show_next' href='javascript:void(0)' onclick='show_more_translations({$limit}, {$next}, {$admin}, \"{$order_by}\")'> Show next -> </a> </td> <td> </td> </tr>";
    } //$next < get_translated_count( $admin )
    else {
        $display .= "<td></td>";
    }
    return $display;
}

/**
 * Assembles query used for the translations table
 * @param int criterions If 1 we are using admin mode where the translators user name has to be shown
 * @param int start First translation to be shown
 * @param string order_by The ordering rule used in the query
 * @param int limit The number of translations to be shown
 * @return string returns The sql query assembled 
 */
function get_translations_table_query( $criterion, $start, $order_by, $limit )
{
    global $_TABLES;
    if ( $criterion == 1 ) {
        if ( $start >= 0 ) {
            $query = "SELECT t.id, t.language_full_name, concat(hour(TIMEDIFF(NOW(), t.timestamp)), ' hours ago') as `posted`, t.approval_counts,
            t.translation, t.user_id, u.username FROM {$_TABLES['translations']} as t JOIN {$_TABLES['users']} as u ON t.user_id = u.uid 
            UNION
            SELECT t.id, t.language_full_name, concat(hour(TIMEDIFF(NOW(), t.timestamp)), ' hours ago') as `posted`, t.approval_counts,
            t.translation, t.user_id, t.site_credentials FROM {$_TABLES['translations']} as t JOIN {$_TABLES['remote_credentials']} as r ON t.site_credentials = r.site_name
            WHERE {$criterion} ORDER BY  {$order_by}  LIMIT {$start}, {$limit}";
            
        } //$start >= 0
        else {
            $query = "SELECT t.id, t.language_full_name, concat(hour(TIMEDIFF(NOW(), t.timestamp)), ' hours ago') as `posted`, t.approval_counts,
            t.translation, t.user_id, u.username  FROM {$_TABLES['translations']} as t JOIN {$_TABLES['users']} as u ON t.user_id = u.uid
            UNION 
            SELECT t.id, t.language_full_name, concat(hour(TIMEDIFF(NOW(), t.timestamp)), ' hours ago') as `posted`, t.approval_counts,
            t.translation, t.user_id, t.site_credentials FROM {$_TABLES['translations']} as t JOIN {$_TABLES['remote_credentials']} as r ON t.site_credentials = r.site_name
            WHERE {$criterion} ORDER BY  {$order_by} LIMIT  {$limit}";
        }
    } //$criterion == 1
    else {
        if ( $start >= 0 ) {
            $query = "SELECT `id`, `language_full_name`, concat(hour(TIMEDIFF(NOW(), `timestamp`)), ' hours ago') as `posted`, `approval_counts`,
            `translation`,  `user_id` FROM {$_TABLES['translations']} WHERE {$criterion} ORDER BY  {$order_by}  LIMIT {$start}, {$limit}";
        } //$start >= 0
        else {
            $query = "SELECT `id`, `language_full_name`, concat(hour(TIMEDIFF(NOW(), `timestamp`)), ' hours ago') as `posted`, `approval_counts`,
            `translation`, `user_id` FROM {$_TABLES['translations']} WHERE {$criterion} ORDER BY {$order_by}  LIMIT {$limit}";
        }
    }
    return $query;
}


/**
 * Makes the translations table for admins
 * @see get_user_translations_table for translations table in user mode
 * @param int limit number of translations shown per page, default value is 5
 * @param int start first translation to be shown , default value -1
 * @param string order_by the ordering of the translation default value '`posted`, `id` DESC'
 * @return string HTML code of the table 
 */
function get_translations_table( $limit = 5, $start = -1, $order_by = '`posted`, `id` DESC' )
{
    global $_TABLES, $_USER;
    $display = '';
    get_translations_options( $limit, $start, $order_by );
    $query      = get_translations_table_query( 1, $start, $order_by, $limit );
    $result     = DB_query( $query );
    $next       = ( $start + $limit ) % $limit == 0 ? $start + $limit : ( $start + $limit + 1 );
    $previous   = $start - $limit;
    $user_names = array( );
    if ( DB_numRows( $result ) > 0 ) {
        $display = get_translations_table_headers( 1, $limit );
        while ( $row = DB_fetchArray( $result ) ) {
            $class = '';
            if ( $row[ 'approval_counts' ] < 0 ) {
                $class = 'error';
            } //$row[ 'approval_counts' ] < 0
            $user_id         = $row[ 'user_id' ];
            $row[ 'posted' ] = (int) ( $row[ 'posted' ] ) < 24 ? $row[ 'posted' ] : ( (int) ( $row[ 'posted' ] / 24 ) . " days ago" );
            
            if ( $user_id == -1 )
                $display .= "<tr id='translation_{$row['id']}' class='{$class}'> <td> {$row['username']} <a href='javascript:void(0)' onclick='remove_peer(\"{$row['username']}\")'>(remove)</a> </td>";
            else
                $display .= "<tr id='translation_{$row['id']}' class='{$class}'> <td> {$row['username']}<a href='javascript:void(0)' onclick='block_user($user_id)'>(block)</a> </td>";
            
            $display .= "<td> {$row['language_full_name']} </td> <td> {$row['translation']} </td>  <td> {$row['approval_counts']} </td>
            <td> {$row['posted']} </td>" . " <td> <a href='javascript:void(0)' onclick=\"delete_translation({$row['id']}, '{$row['translation']}' )\"> delete </a> </td> </tr>";
        } //$row = DB_fetchArray( $result )
        $display .= "<tr> <td> </td> <td> </td>   ";
        $display .= get_translations_table_finalize( $previous, $next, 1, $limit, $order_by );
    } //DB_numRows( $result ) > 0
    else {
        $display .= "<tr> You have not submited any translations yet </tr>";
    }
    $display .= " </tbody></table>";
    return $display;
}


/**
 * Makes the translations table for users
 * @see get_translations_table_table for translations table in admin mode
 * @param int limit number of translations shown per page, default value is 5
 * @param int start first translation to be shown , default value -1
 * @param string order_by the ordering of the translation default value '`posted`, `id` DESC'
 * @return string HTML code of the table 
 */
function get_user_translations_table( $limit = 5, $start = -1, $order_by = '`posted`, `id` DESC' )
{
    global $_USER;
    $display = '';
    get_translations_options( $limit, $start, $order_by );
    $query  = get_translations_table_query( "`user_id`={$_USER['uid']}", $start, $order_by, $limit );
    $result = DB_query( $query );
    if ( DB_numRows( $result ) > 0 ) {
        $display = get_translations_table_headers( 0, $limit );
        while ( $row = DB_fetchArray( $result ) ) {
            $class = '';
            if ( $row[ 'approval_counts' ] < 0 ) {
                $class = 'error';
            } //$row[ 'approval_counts' ] < 0
            $row[ 'posted' ] = (int) ( $row[ 'posted' ] ) < 24 ? $row[ 'posted' ] : ( (int) ( $row[ 'posted' ] / 24 ) . " days ago" );
            $display .= "<tr id='translation_{$row['id']}' class='{$class}'> <td> {$row['language_full_name']} </td> <td> {$row['translation']} </td>  <td> {$row['approval_counts']} </td>  <td> {$row['posted']} </td>" . " <td> <a href='javascript:void(0)' onclick=\"delete_translation({$row['id']}, '{$row['translation']}' )\"> delete </a> </td>   </tr>";
        } //$row = DB_fetchArray( $result )
        $next     = ( $start + $limit ) % $limit == 0 ? $start + $limit : ( $start + $limit + 1 );
        $previous = $start - $limit;
        $display .= "<tr>  <td> </td>  ";
        $display .= get_translations_table_finalize( $previous, $next, 0, $limit, $order_by );
    } //DB_numRows( $result ) > 0
    else {
        $display .= "<tr> No submited translations </tr>";
    }
    $display .= "</tbody></table>";
    return $display;
}


/**
 * Puts specified user on block list, deletes his translations and votes for those translations and awarded gems 
 * @param int user_id ID of user to be blocked
 */
function block_user( $user_id = null )
{
    global $_TABLES;
    if ( isset( $_REQUEST[ 'user_id' ] ) && !empty( $_REQUEST[ 'user_id' ] ) ) {
        $user_id = $_REQUEST[ 'user_id' ];
    } //isset( $_REQUEST[ 'user_id' ] ) && !empty( $_REQUEST[ 'user_id' ] )
    if ( $user_id != null ) {
        $query  = "INSERT INTO {$_TABLES['blocked_users']} (`user_id`, `timestamp`) VALUES ({$user_id}, now() ) ";
        $result = DB_query( $query );
        if ( $result == true ) {
            $query  = "DELETE t.*, v.* FROM {$_TABLES['translations']} as t JOIN {$_TABLES['votes']} as v ON t.id=v.translation_id WHERE t.user_id = {$user_id}";
            $result = DB_query( $query );
            $query  = "DELETE FROM {$_TABLES['awarded_gems']} WHERE `user_id` = {$user_id} ";
            $result = DB_query( $query );
        } //$result == true
    } //$user_id != null
}


/**
 * Assebles list of blocked users
 * @return string HTML code of a table containing usernames and blocking times of blocked users
 */
function get_blocked_users_table( )
{
    global $_TABLES;
    $display = '';
    $query   = "SELECT b.*, u.username FROM {$_TABLES['blocked_users']} as b JOIN {$_TABLES['users']} AS u ON b.user_id = u.uid";
    $result  = DB_query( $query );
    if ( DB_numRows( $result ) > 0 ) {
        $display .= "<table class='translations_view'> <tbody>";
        $display .= "<tr> <th> Username </th> <th> Time Blocked </th> <th> </th>";
        $count = 0;
        while ( $row = DB_fetchArray( $result ) ) {
            $user_id = $row[ 'user_id' ];
            if ( ++$count % 2 != 0 )
                $display .= "<tr class='error'> ";
            else
                $display .= "<tr>";
            $display .= "<td> {$row['username']} </td> <td> {$row['timestamp']}  </td> <td> <a href='javascript:void(0)' onclick='remove_block($user_id)'> remove block </a> </td> </tr>";
        } //$row = DB_fetchArray( $result )
        $display .= "</tbody></table>";
    } //DB_numRows( $result ) > 0
    else {
        $display .= "No users on this list";
    }
    return $display;
}


/**
 * Removes specified user from block list
 * @param int user_id ID of user to be un-blocked
 */
function remove_block( $user_id = null )
{
    global $_TABLES;
    if ( isset( $_REQUEST[ 'user_id' ] ) && !empty( $_REQUEST[ 'user_id' ] ) ) {
        $user_id = $_REQUEST[ 'user_id' ];
    } //isset( $_REQUEST[ 'user_id' ] ) && !empty( $_REQUEST[ 'user_id' ] )
    if ( $user_id != null ) {
        $query = "DELETE FROM {$_TABLES['blocked_users']} WHERE `user_id` = {$user_id}";
        DB_query( $query );
    } //$user_id != null
}

/**
* Check if post variable is set and non=empty
* @return boolean True if the POST variable specified is set and non-empty
*/
function check_post_variable( $post_var )
{
    $error = new stdClass();
    if ( !isset( $_POST[ $post_var ] ) || empty( $_POST[ $post_var ] ) ) {
        $error->message    = "{$post_var} no set";
        $error->error_code = '1';
        echo json_encode( $error );
        return false;
    } //!isset( $_POST[ $post_var ] ) || empty( $_POST[ $post_var ] )
    return true;
}


/**
 * Checks for the awards the user has not recieved and if criteria is met assignes them
 * for repetative awards the check is always done
 * @return int number of awards given
 */
function awards( )
{
    global $_USER, $_TABLES;
    $counter           = 0;
    $query             = "SELECT g.gem_id FROM {$_TABLES['gems']} g WHERE g.gem_id NOT IN (SELECT a.gem_id FROM {$_TABLES['awarded_gems']} a WHERE a.user_id={$_USER['uid']})";
    $possible_gems     = DB_query( $query );
    $query             = "SELECT COUNT(`id`) as count FROM {$_TABLES['translations']} WHERE `user_id` = {$_USER['uid']}";
    $result            = DB_query( $query );
    $translation_count = DB_fetchArray( $result );
    $translation_count = $translation_count[ 'count' ];
    if ( $translation_count < 0 )
        return;
    $gems = array( );
    while ( $gem = DB_fetchArray( $possible_gems ) )
        array_push( $gems, $gem[ 'gem_id' ] );
    if ( in_array( 2, $gems ) == false ) {
        array_push( $gems, 2 );
    } //in_array( 2, $gems ) == false
    if ( in_array( 4, $gems ) == false ) {
        array_push( $gems, 4 );
    } //in_array( 4, $gems ) == false
    foreach ( $gems as $index => $gem_id ) {
        $awarded = check_if_awarded( $gem_id );
        //nth_translation
        if ( $gem_id == 2 ) {
            while ( award_nth_translation( $translation_count, $gem_id ) == true ) {
                $counter++;
            } //award_nth_translation( $translation_count, $gem_id ) == true
            continue;
        } //$gem_id == 2
        elseif ( $gem_id == 3 && $awarded == false ) {
            if ( award_first_vote( $gem_id ) == true )
                $counter++;
        } //$gem_id == 3 && $awarded == false
            elseif ( $gem_id == 4 ) {
            while ( award_nth_vote( $gem_id ) == true ) {
                $counter++;
            } //award_nth_vote( $gem_id ) == true
            continue;
        } //$gem_id == 4
            elseif ( $gem_id == 1 && $awarded == false ) {
            give_award( $gem_id );
            $counter++;
        } //$gem_id == 1 && $awarded == false
    } //$gems as $index => $gem_id
    return $counter;
}


/**
 * @param int translation_count number of translation the user has submited
 * @param int gem_id the id under which the award has been given
 * @return boolean true if award is given, false otherwise
 */
function award_nth_translation( $translation_count, $gem_id )
{
    //minimal translations here is 5
    if ( $translation_count < 5 )
        return false;
    global $_TABLES, $_USER;
    $award_lvl  = 0;
    $award_mark = 0;
    award_mark( $gem_id, $award_lvl, $award_mark );
    if ( $translation_count >= $award_mark ) {
        give_award( $gem_id, $award_lvl );
        return true;
    } //$translation_count >= $award_mark
    else {
        return false;
    }
}


/**
 * Check if award with id gem_id is given to user
 * @param int gem_id the id under which the award has been given
 * @return boolean true if user has award
 */
function check_if_awarded( $gem_id )
{
    global $_USER, $_TABLES;
    $query  = "SELECT COUNT(`gem_id`) AS count FROM {$_TABLES['awarded_gems']} WHERE `gem_id` = {$gem_id} AND `user_id` = {$_USER['uid']}";
    $result = DB_query( $query );
    $count  = DB_fetchArray( $result );
    $count  = $count[ 'count' ];
    if ( $count > 0 )
        return true;
    return false;
}


/**
 * The award is given to the user by saving it to the awarded_gems table
 * @param int gem_id the id under which the award has been given
 * @param int award_lvl the level of the award
 */
function give_award( $gem_id = null, $award_lvl = 0 )
{
    global $_TABLES, $_USER;
    if ( $gem_id == null )
        return;
    global $_USER, $_TABLES;
    if ( $award_lvl == 0 || $award_lvl == 2 )
        $query = "INSERT INTO {$_TABLES['awarded_gems']} (`gem_id`, `user_id`, `award_lvl`) VALUES ({$gem_id}, {$_USER['uid']}, {$award_lvl})";
    else
        $query = "UPDATE {$_TABLES['awarded_gems']} SET `award_lvl` = {$award_lvl} WHERE `gem_id` = {$gem_id}";
    DB_query( $query );
}

/**
 *award for first vote
 *@param int gem_id 
 */
function award_first_vote( $gem_id )
{
    global $_TABLES, $_USER;
    $query       = "SELECT COUNT(`translation_id`) as count FROM {$_TABLES['votes']}  WHERE `translation_id` NOT IN ( SELECT `id` FROM {$_TABLES['translations']} WHERE `user_id` = {$_USER['uid']} )";
    $result      = DB_query( $query );
    $result      = DB_fetchArray( $result );
    $votes_count = $result[ 'count' ];
    if ( $votes_count >= 1 ) {
        give_award( $gem_id );
    } //$votes_count >= 1
}

/**
 * nth vote award
 *@param int gem_id the id under which the award has been given
 */
function award_nth_vote( $gem_id )
{
    global $_TABLES, $_USER;
    $query       = "SELECT COUNT(`translation_id`) as count FROM {$_TABLES['votes']}  WHERE `translation_id` NOT IN ( SELECT `id` FROM {$_TABLES['translations']} WHERE `user_id` = {$_USER['uid']} )";
    $result      = DB_query( $query );
    $result      = DB_fetchArray( $result );
    $votes_count = $result[ 'count' ];
    if ( $votes_count < 5 )
        return;
    $award_lvl  = 0;
    $award_mark = 0;
    award_mark( $gem_id, $award_lvl, $award_mark );
    if ( $votes_count >= $award_mark ) {
        give_award( $gem_id, $award_lvl );
        return true;
    } //$votes_count >= $award_mark
    return false;
}

/**
 * nth vote award
 *@param int gem_id the id under which the award has been given
 *@param int award_lvl the level of the award - for continuos awards
 *@param int award_mark the limit required to get the next award lvl
 */
function award_mark( $gem_id, &$award_lvl, &$award_mark )
{
    global $_TABLES, $_USER;
    $query  = "SELECT `award_lvl` FROM {$_TABLES['awarded_gems']} WHERE `user_id` = {$_USER['uid']} AND `gem_id` = {$gem_id}";
    $result = DB_query( $query );
    if ( $row = DB_fetchArray( $result ) )
        $award_lvl = $row[ 'award_lvl' ] + 1;
    else
        $award_lvl = 2;
    $award_mark = ( 3 * ( $award_lvl * $award_lvl ) - $award_lvl ) / 2;
}

/**
* Add new credentials to the database, allowing a new site to submit remote translations
* @return string JSON encoded strying with failiure or success message
*/
function add_peer( )
{
    global $_TABLES, $_CONF;
    if ( isset( $_POST[ 'site_name' ] ) && !empty( $_POST[ 'site_name' ] ) && isset( $_POST[ 'site_credentials' ] ) && !empty( $_POST[ 'site_credentials' ] ) ) {
        $salt     = SEC_generateSalt();
        $password = SEC_encryptPassword( $_POST[ 'site_credentials' ], $salt, $_CONF[ 'pass_alg' ], $_CONF[ 'pass_stretch' ] );
        if ( DB_count( $_TABLES[ 'remote_credentials' ], 'site_name', DB_escapeString( $_POST[ 'site_name' ] ) ) == 0 ) {
            $query = "INSERT INTO {$_TABLES['remote_credentials']} VALUES ('{$_POST['site_name']}', '{$password}', '{$salt}')";
            DB_query( $query );
            $response = array(
                 "site_name" => $_POST[ 'site_name' ] 
            );
        } else {
            $response = array(
                 "error" => "Peer with this name already exists" 
            );
        }
        
    } else {
        $response = array(
             "error" => "Site name or Site credentials are not set" 
        );
    }
    
    return json_encode( $response );
}
/**
* Removes remote submision credentials from the database as well as translations submited by the remote site
* @return string JSON encoded strying with failiure or success message
*/
function remove_peer( )
{
    global $_TABLES;
    if ( isset( $_POST[ 'peer_name' ] ) && !empty( $_POST[ 'peer_name' ] ) ) {
        $query = "DELETE FROM {$_TABLES['remote_credentials']} WHERE `site_name`='{$_POST['peer_name']}'";
        DB_query( $query );
        $query = "DELETE t.*, v.* FROM {$_TABLES['translations']} as t JOIN {$_TABLES['votes']} as v ON t.id=v.translation_id WHERE t.site_credentials = '{$_POST['peer_name']}'";
        DB_query( $query );
        $query = "DELETE t.* FROM {$_TABLES['translations']} as t WHERE t.site_credentials = '{$_POST['peer_name']}'";
        DB_query( $query );
        $response = array(
             "site_name" => $_POST[ 'peer_name' ] 
        );
    } else {
        $response = array(
             "error" => "Site name are not set" 
        );
    }
    return json_encode( $response );
}

/** the script will return all available languages for translation
 *it will make a list of both languages from the language folder
 *and previously user created languages
 */
function get_languages( )
{
    global $_TABLES;
    //get all the languages shiped with geeklog
    $lang   = MBYTE_languageList( $LANG_CHARSET );
    //get languages previously added by users
    $result = DB_query( "SELECT DISTINCT language_full_name, language_file FROM {$_TABLES['translations']}" );
    //merge previously created languages with languages from database
    while ( $language = DB_fetchArray( $result ) ) {
        if ( !array_key_exists( $language[ 'language_file' ], $language ) )
            $lang[ $language[ 'language_file' ] ] = $language[ 'language_full_name' ];
    }
    //return to javascript via JSON
    echo json_encode( $lang );
}

/**
 * Saves the user vote or updates existing vote
 * notifies the JS if the page has to be reloaded-this happens if a translation is deleted
 * @return string JSON encoded strying with failiure or success message
 */
function vote( )
{
    global $_TABLES, $_USER;
    $user_id               = $_USER[ 'uid' ];
    $response[ 'refresh' ] = false;
    //user has to be logged in to vote
    if ( !empty( $user_id ) && isset( $user_id ) ) {
        $translation_id = $_REQUEST[ 'translation_id' ];
        $sign           = $_REQUEST[ 'sign' ];
        $result         = DB_query( "SELECT `sign` FROM {$_TABLES['votes']} WHERE `translation_id`='{$translation_id}' AND `user_id`='{$user_id}' " );
        $previous_sign  = 0;
        /*if the user has not voted this translation before the votes is saved
         * othervise the vote is updated, the sign column is changed
         */
        if ( DB_numRows( $result ) == 0 ) {
            $result = DB_query( "INSERT INTO {$_TABLES['votes']} (`translation_id`, `user_id`, `sign`) VALUES ('{$translation_id}', '{$user_id}', '{$sign}') " );
        } //DB_numRows( $result ) == 0
        else {
            $previous_sign = DB_fetchArray( $result );
            $previous_sign = $previous_sign[ 'sign' ];
            $result        = DB_query( "UPDATE {$_TABLES['votes']} SET `sign`='{$sign}'   WHERE `translation_id`='{$translation_id}' AND `user_id`='{$user_id}' " );
        }
        //the approval_counts column of the translation has to be updated
        $result          = DB_query( "UPDATE {$_TABLES['translations']} SET `approval_counts`=`approval_counts`+{$sign}-{$previous_sign} WHERE `id`='{$translation_id}' " );
        //if the approval counts reach a certain negative value they should be deleted
        $query           = ( "SELECT `approval_counts` FROM {$_TABLES['translations']} WHERE `id`='{$translation_id}' " );
        $result          = DB_query( $query );
        $approval_counts = DB_fetchArray( $result );
        $approval_counts = $approval_counts[ 'approval_counts' ];
        if ( $approval_counts <= -5 ) {
            $response[ 'refresh' ] = true;
            $query                 = "DELETE FROM {$_TABLES['translations']} WHERE `id`='{$translation_id}' ";
            $result                = DB_query( $query );
            if ( $result ) {
                $query = "DELETE FROM {$_TABLES['votes']} WHERE `translation_id`='{$translation_id}' ";
                DB_query( $query );
            } //$result
        } //$approval_counts <= -5
        echo json_encode( $response );
    } //!empty( $user_id ) && isset( $user_id )
}



/**
 * Script will take extracted array data find the original array values from the database
 * where all variables and html tags have been replaced with <tag> and create the 
 * HTML of the translation form
 * before it is saved to the database
 * @return string JSON encoded data holding the translation form, language strings, tagged strings
 */
function get_original_language_values( )
{
    global $_TABLES, $_USER, $_CONF;
    
    $html           = $_POST[ 'html' ];
    $language       = $_POST[ 'language' ];
    $response       = array( );
    $language_array = get_language_array();
    $taged_strings  = purge_language_array( $html, $language_array );
    
    
    $user_id    = $_USER[ 'uid' ];
    $base_url   = $_CONF[ 'site_url' ] . "/crowdtranslator/images/";
    $up_image   = $base_url . "up.png";
    $down_image = $base_url . "down.png";
    $form       = "<form id='translator_form_submission' method='post' action='{$_CONF['site_url']}/crowdtranslator/submit_translation.php' >" . "<div id='submision_success' class='success'></div>" . "<div id='submision_error' class='error'></div>" . "<span><img id='up_img' src='{$up_image}' onclick='show_previous()' class='hidden navigation_images' /></span></br>";
    //when count hits a certain number remaining input fields will be assigned a CSS class to hide them
    $count      = 0;
    foreach ( $language_array as $key => $value ) {
        //check if current string has translation in the database, picks the one with the best vote
        $result = DB_query( "SELECT `translation`, `id` FROM {$_TABLES['translations']} WHERE `language_full_name`='{$language}' AND  `language_array`='{$value->array_name}' AND `array_key`='{$value->array_index}' AND `array_subindex`='{$value->array_subindex}' ORDER BY `approval_counts` DESC LIMIT 1" );
        if ( $row = DB_fetchArray( $result ) ) {
            $value->translation    = $row[ 'translation' ];
            $value->translation_id = $row[ 'id' ];
        } else {
            $value->translation = '';
        }
        $disabled_up   = '';
        $disabled_down = '';
        //if the user has voted for the current string the upvote or downvote buttons should be disabled
        if ( isset( $value->translation_id ) ) {
            $result = DB_query( "SELECT `sign` FROM {$_TABLES['votes']} WHERE `user_id` = {$user_id} AND `translation_id`='{$value->translation_id}'" );
            if ( $row = DB_fetchArray( $result ) ) {
                $sign = $row[ 'sign' ];
                if ( $sign == '1' ) {
                    $disabled_up = 'disabled';
                } else
                    $disabled_down = 'disabled';
            }
        }
        //assembles the next input element
        add_form_element( $form, $count, $value, $base_url, $disabled_up, $disabled_down );
        $count++;
    }
    //finalizes the form
    $form .= "<span><img id='down_img' src='{$down_image}' onclick='show_next()' class='navigation_images' /></span>" . "<button type='submit' id='submit_form'>Submit Translations</button>" . "</form>";
    $response[ 'language_array' ] = $language_array;
    $response[ 'form' ]           = $form;
    $response[ 'taged_strings' ]  = $taged_strings;
    $response[ 'translated' ]     = get_translation_percent();
    echo json_encode( $response );
    
}
/**
 * @param string form The HTML of the translation form
 * @param int count number of current input field
 * @param object value current translation object, holds all relevant data for the string
 * @param string base_url base url for the required resources
 * @param string disable_up Will either be empty string or disable if the vote button should be disabled
 * @param string disable_down Will either be empty string or disable if the vote button should be disabled
 */
function add_form_element( &$form, $count, $value, $base_url, $disabled_up, $disabled_down )
{
    $highlight_image        = $base_url . "highlight.png";
    $remove_highlight_image = $base_url . "rhighlight.png";
    $vote_up_image          = $base_url . "vote_up.png";
    $vote_down_image        = $base_url . "vote_down.png";
    $form_label             = "<label for='translator_input_{$count}'>{$value->string}</label>" . " <img class='form_image' src='{$remove_highlight_image}' id='translator_input_{$count}_image' onclick=remove_highlight({$count}) />" . " <img class='form_image' src='{$highlight_image}' id='translator_input_{$count}_image' onclick=highlight({$count}) />";
    $form_input1            = "<input type='text' id='translator_input_{$count}' name='translator_input_{$count}' />";
    $form_input2            = "<div class='suggested'> <span id='translator_input_{$count}' >   {$value->translation} </span>" . "<span class='votes'> <button type='button' id='vote_up_button_{$count}' {$disabled_up} class='vote-button'  onclick='vote(1, {$count}, this)'   > <img src='{$vote_up_image}' /> </button>" . " <button type='button' class='vote-button' id='vote_down_button_{$count}' {$disabled_down} onclick='vote(-1, {$count}, this)'  > <img src='{$vote_down_image}' />  </button> </span> </div>";
    $form_hidden_input      = "<input id='translator_input_{$count}_hidden' class='hidden' name='translator_input_{$count}_hidden' value='{$value->metadata}' />";
    if ( strlen( $value->translation ) > 0 ) {
        if ( $count > 5 ) {
            $template = "<span id='input_span_{$count}' class='group_input temp_hidden'>{$form_label} {$form_input2}<label >" . "or enter your own: </label>{$form_input1} {$form_hidden_input}</span>";
        } else {
            $template = "<span id='input_span_{$count}' class='group_input'>{$form_label}{$form_input2} <label > or enter your own: </label>" . "{$form_input1} {$form_hidden_input} </span>";
        }
    } else {
        if ( $count > 5 ) {
            $template = "<span id='input_span_{$count}' class='temp_hidden'> {$form_label} {$form_input1} {$form_hidden_input} </span>";
        } else {
            $template = "<span id='input_span_{$count}'>{$form_label}{$form_input1} {$form_hidden_input} </span>";
        }
    }
    $form .= $template;
}

/**
* Returns current page url
* @return string Current page url
*/
function get_page_url( )
{
    global $_POST;
    $page_url = $_POST[ 'url' ];
    if ( strpos( $page_url, ".php" ) == false )
        $page_url = "index.php";
    $page_url = basename( $page_url );
    return $page_url;
}

/**
* Generates a list of LANG array elements which are used in pages included in the current page
* @see documentation on language mapping and how the plugin works
* @param array &reference the array of previously found LANG references
* @param array included the array of previously processed included url's
* @param array includes the array of url's to be processed
*/
function get_language_array_references_from_included( &$reference, $included, &$includes )
{
    global $_TABLES;
    $query  = "SELECT * FROM {$_TABLES['language_map']} WHERE ";
    $length = count( $includes );
    $count  = 0;
    for ( $i = 0; $i < $length; $i++ ) {
        if ( !in_array( $includes[ $i ], $included ) ) {
            if ( $count == 0 )
                $query .= " `page_url` LIKE '%{$includes[$i]}'  ";
            else
                $query .= "OR `page_url` LIKE '%{$includes[$i]}'";
            array_push( $included, $includes[ $i ] );
            $count++;
        }
    }
    if ( $count > 0 ) {
        $result     = DB_query( $query );
        $added_page = 0;
        while ( $row = DB_fetchArray( $result ) ) {
            $ref = json_decode( $row[ 'reference' ] );
            foreach ( $ref as $key => $value ) {
                if ( !in_array( $value, $reference ) )
                    array_push( $reference, $value );
            }
            $inc = json_decode( $row[ 'includes' ] );
            foreach ( $inc as $key => $value ) {
                if ( !in_array( $value, $included ) ) {
                    array_push( $includes, $value );
                    $added_page += 1;
                }
            }
        }
        if ( $added_page > 0 )
            get_language_array_references_from_included( $reference, $includes, $included );
    }
}

/**
* Returns the array name from the metadata string
* @param string string metadata string
* @return string array name
*/
function get_array( $string )
{
    $regexp = "/\w{1,}\[/";
    preg_match( $regexp, $string, $matches, PREG_OFFSET_CAPTURE );
    $array = $matches[ 0 ][ 0 ];
    $array = substr( $array, 0, strlen( $array ) - 1 );
    return $array;
}

/**
* Returns the array index from the metadata string
* @param string string metadata string
* @return string array index
*/
function get_index( $string )
{
    $regexp = "/\['*\w{1,}'*\]/";
    preg_match( $regexp, $string, $matches, PREG_OFFSET_CAPTURE );
    $index = $matches[ 0 ][ 0 ];
    if ( strpos( $index, "'" ) !== false )
        $index = substr( $index, 2, strlen( $index ) - 4 );
    else
        $index = substr( $index, 1, strlen( $index ) - 2 );
    return $index;
}

/**
* Returns the array sub-index from the metadata string
* @param string array array name from the metadata string
* @param string index array index from the metadata string
* @param string string metadata string
* @return string array sub-index
*/
function get_subindex( $array, $index, $string )
{
    $subindex = substr( $string, strlen( "{$array}[{$index}]" ) );
    $regexp   = "/\['*\w{1,}'*\]/";
    preg_match( $regexp, $subindex, $matches, PREG_OFFSET_CAPTURE );
    if ( !empty( $matches ) ) {
        $subindex = $matches[ 0 ][ 0 ];
        if ( strpos( $subindex, "'" ) !== false )
            $subindex = substr( $subindex, 2, strlen( $subindex ) - 4 );
        else
            $subindex = substr( $subindex, 1, strlen( $subindex ) - 2 );
        
    } else {
        $subindex = -1;
    }
    return $subindex;
    
}

/**
* Generates an array of objects holding translations and their metadata
* @return array Objects holding translations and their metadata
*/
function get_language_array( )
{
    global $_TABLES;
    $page_url  = get_page_url();
    $query     = "SELECT * FROM {$_TABLES['language_map']} WHERE `page_url` LIKE '%{$page_url}'";
    $result    = DB_query( $query );
    $result    = DB_fetchArray( $result );
    $includes  = json_decode( $result[ 'includes' ] );
    $reference = json_decode( $result[ 'reference' ] );
    $included  = array( );
    array_push( $included, basename( $page_url ) );
    get_language_array_references_from_included( $reference, $included, $includes );
    $language_array = array( );
    if ( !is_array( $reference ) || empty( $reference ) ) {
        echo json_encode( array(
             "error" => "This page is not mapped",
            "error_code" => 2 
        ) );
        exit;
    }
    foreach ( $reference as $key => $value ) {
        $obj                 = new stdClass();
        $array               = get_array( $value );
        $index               = get_index( $value );
        $subindex            = get_subindex( $array, $index, $value );
        $obj->array_name     = $array;
        $obj->array_index    = $index;
        $obj->array_subindex = $subindex;
        $obj->metadata       = "array_{$array}index_{$index}subindex_{$subindex}";
        $obj->string         = '';
        array_push( $language_array, $obj );
    }
    return $language_array;
}

/**
* The generated language array offten has more references than shown in the actual page rendering
* this function will remove strings not rendered on the page
* @see get_language_array( )
* @param string html HTML of the currrent page
* @param array language_array The array of references 
*/
function purge_language_array( $html, &$language_array )
{
    global $_TABLES;
    $taged_strings = array( );
    foreach ( $language_array as $key => $value ) {
        $query  = "SELECT  `string`, `tags` FROM {$_TABLES['originals']} WHERE `language_array`='{$value->array_name}' AND `array_index`='{$value->array_index}' AND `sub_index` = '{$value->array_subindex}' ";
        $result = DB_query( $query );
        while ( $row = DB_fetchArray( $result ) ) {
            //making <var> and <tag> html friendly
            $is_taged = false;
            if ( strpos( $row[ 'string' ], "<tag>" ) !== false || strpos( $row[ 'string' ], "<var>" ) !== false ) {
                $taged                 = new stdClass();
                $taged->tag            = substr_count( $row[ 'string' ], "<tag>" );
                $taged->var            = substr_count( $row[ 'string' ], "<var>" );
                $value->string         = str_replace( "<tag>", "&lttag&gt", $row[ 'string' ] );
                $value->string         = str_replace( "<var>", "&ltvar&gt", $value->string );
                $value->tags           = $row[ 'tags' ];
                $taged_strings[ $key ] = $taged;
                $is_taged              = true;
            } else {
                $value->string = $row[ 'string' ];
            }
        }
        if ( $is_taged == false ) {
            if ( !empty( $value->string ) ) {
                if ( strpos( $html, $value->string ) == false ) {
                    unset( $language_array[ $key ] );
                } else {
                    $language_array[ $key ]->parsed = $GLOBALS[ $language_array[ $key ]->array_name ][ $language_array[ $key ]->array_index ];
                }
            } else {
                unset( $language_array[ $key ] );
            }
        } else {
            $this_string = $value->string;
            $this_string = str_replace( "&lttag&gt", "splitstring", $this_string );
            $this_string = str_replace( "&ltvar&gt", "splitstring", $this_string );
            $substrings  = explode( "splitstring", $this_string );
            $in_html     = true;
            $offset      = 0;
            foreach ( $substrings as $key2 => $value2 ) {
                if ( !empty( $value2 ) ) {
                    $in_html = strpos( $html, $value2, $offset );
                    if ( !$in_html )
                        break;
                    else {
                        $offset = strpos( $html, $value2, $offset );
                    }
                }
            }
            if ( $in_html == false ) {
                unset( $language_array[ $key ] );
                unset( $taged_strings[ $key ] );
            } else {
                $language_array[ $key ]->parsed = $GLOBALS[ $language_array[ $key ]->array_name ][ $language_array[ $key ]->array_index ];
            }
        }
    }
    $language_array = array_values( $language_array );
    
    $taged_strings = array_values( $taged_strings );
    return $taged_strings;
}

/**
* After translations are submited an AJAX call is issued to this functions, it will process the submited translations
* @return string json encoded string holding data on number of valid and invalid translations, number of given awards and the percent of translation
* for the current language
*/
function submit_translation( )
{
    global $_USER, $_TABLES, $_CONF;
    if ( ( check_post_variable( "taged_strings" ) == false ) || ( check_post_variable( "count" ) == false ) )
        exit;
    $response      = array( );
    $taged_strings = json_decode( stripslashes( $_POST[ 'taged_strings' ] ) );
    $bad_input     = array( );
    $good_input    = array( );
    $process_q     = array( );
    $count         = $_POST[ 'count' ];
    $language      = $_COOKIE[ 'selected_language' ];
    //loop through all the input fields
    for ( $i = 0; $i < $count; $i++ ) {
        $base_name     = "translator_input_{$i}";
        $metadata_name = "translator_input_{$i}_hidden";
        //checks if the user has submited a translation to input field
        if ( isset( $_POST[ $base_name ] ) && !empty( $_POST[ $base_name ] ) ) {
            $faulty = false;
            //check for bad inputs- missing <var> and <tag> 
            if ( isset( $taged_strings->$i ) ) {
                $_POST[ $base_name ] = str_replace( "&lttag&gt", "<tag>", $_POST[ $base_name ] );
                $_POST[ $base_name ] = str_replace( "&ltvar&gt", "<var>", $_POST[ $base_name ] );
                //if there is a lack of <tag> or <var> in the translation the input is marked as faulty
                if ( substr_count( $_POST[ $base_name ], "<tag>" ) != $taged_strings->$i->tag || substr_count( $_POST[ $base_name ], "<var>" ) != $taged_strings->$i->var ) {
                    //will be used by the JS to mark faulty inputs
                    array_push( $bad_input, $i );
                    $faulty = true;
                } //substr_count( $_POST[ $base_name ], "<tag>" ) != $taged_strings->$i->tag || substr_count( $_POST[ $base_name ], "<var>" ) != $taged_strings->$i->var
            } //isset( $taged_strings->$i )
            //if the input passed the previous test a new object is created with all relevant data for the translation
            if ( $faulty == false ) {
                $input = new stdClass();
                if ( check_post_variable( $metadata_name ) == false )
                    exit;
                extract_metadata( $_POST[ $metadata_name ], $input->language_array, $input->array_key, $input->array_subindex );
                $input->language_full_name = $language;
                $input->language_file      = preg_replace( '/[^a-z]/i', '_', strtolower( $language ) );
                $input->plugin_name        = 'core';
                $input->site_credentials   = 'test credentials';
                $input->user_id            = $_USER[ 'uid' ];
                $input->approval_counts    = 1;
                $input->translation        = $_POST[ $base_name ];
                //just to get on the safe side
                if ( !get_magic_quotes_gpc() ) {
                    $input->language_full_name = DB_escapeString( $input->language_full_name );
                    $input->site_credentials   = DB_escapeString( $input->site_credentials );
                    $input->translation        = DB_escapeString( $input->translation );
                } //!get_magic_quotes_gpc()
                //this will be used by the script to remove input fields
                array_push( $good_input, $i );
                //the process_q hold all object(translations) which should be saved to the database
                array_push( $process_q, $input );
            } //$faulty == false
        } //isset( $_POST[ $base_name ] ) && !empty( $_POST[ $base_name ] )
    } //$i = 0; $i < $count; $i++
    $response[ 'bad_input' ]  = $bad_input;
    $response[ 'translated' ] = get_translation_percent();
    $response[ 'good_input' ] = $good_input;
    //will save translations
    save_to_database( $process_q );
    $response[ 'awards_number' ] = (int) ( awards() );
    echo json_encode( $response );
}

/**
 * @param string metadata passed on via POST
 * @param string language_array empty - the value will be extracted from the metadata string
 * @param string language_array empty - the value will be extracted from the metadata string
 */
function extract_metadata( $metadata, &$language_array, &$array_key, &$array_subindex )
{
    $begin          = strpos( $metadata, '_' ) + 1;
    $end            = strpos( $metadata, 'index' ) - $begin;
    $language_array = substr( $metadata, $begin, $end );
    
    $begin          = strpos( $metadata, '_', $begin + strlen( $language_array ) ) + 1;
    $end            = strpos( $metadata, 'subindex' ) - $begin;
    $array_key      = substr( $metadata, $begin, $end );
    $begin          = strpos( $metadata, '_', $begin + strlen( $array_key ) ) + 1;
    $array_subindex = substr( $metadata, $begin );
}
/**
 * @param array process_q array of objects/translations to be saved to the database
 */
function save_to_database( $process_q )
{
    global $_TABLES;
    $date = date( 'Y-m-d H:i:s' );
    //saving translation to database
    foreach ( $process_q as $key => $input ) {
        $query  = "INSERT INTO {$_TABLES['translations']}(`id`, `language_full_name`, `language_file`, `plugin_name`, `site_credentials`, `user_id`,
            `timestamp`, `approval_counts`, `language_array`, `array_key`, `array_subindex`, `translation`)
VALUES ('', '{$input->language_full_name}' , 
   '{$input->language_file}', '{$input->plugin_name}', '{$input->site_credentials}', '{$input->user_id}', '{$date}', '{$input->approval_counts}',
   '{$input->language_array}','{$input->array_key}','{$input->array_subindex}','{$input->translation}')";
        $result = DB_query( $query );
        //after the translation is saved the first vote is added to it (assuming the user who submited the vote would vote it up)
        if ( $result == true ) {
            $query          = "SELECT MAX(`id`) as translation_id FROM {$_TABLES['translations']} ";
            $result         = DB_query( $query );
            $translation_id = DB_fetchArray( $result );
            $translation_id = $translation_id[ 'translation_id' ];
            $query          = "INSERT INTO {$_TABLES['votes']} (`translation_id`, `user_id`, `sign`) VALUES ('{$translation_id}', '{$input->user_id}', '1') ";
            DB_query( $query );
        } //$result == true
    } //$process_q as $key => $input
}

?>