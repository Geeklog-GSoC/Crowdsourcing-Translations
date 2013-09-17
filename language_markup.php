<?php
define( 'XHTML', '" . XHTML . "' );
require_once $_CONF[ 'path_html' ] . "lib-common.php";
require_once $_CONF[ 'path_system' ] . 'lib-database.php';
include_once $_CONF[ 'path' ] . 'plugins/crowdtranslator/custom_string_replace.php';


function add_identifier_to_lanugage_file( )
{
    global $_TABLES, $_CONF;
    
    
    
    $language_array_names = get_language_array_names();
    
    $db_entries     = array( );
    $current_object = array( );
    
    foreach ( $language_array_names as $key => $value ) {
        
        if ( strpos( $value, "['" ) !== false ) {
            $array         = substr( $value, 0, strpos( $value, "['" ) );
            $index         = substr( $value, strpos( $value, "['" ) + 2, strpos( $value, "']" ) - strpos( $value, "['" ) - 2 );
            $current_array = $GLOBALS[ $array ][ $index ];
        } //strpos( $value, "['" ) !== false
        else {
            $current_array = $GLOBALS[ $value ];
            $array         = $value;
            $index         = '';
        }
        if ( $index != '' && !empty( $index ) ) {
            $current_object[ 'array' ] = "{$array}";
            $current_object[ 'index' ] = "{$index}";
            
            foreach ( $current_array as $key2 => $value2 ) {
                if ( is_array( $value2 ) ) {
                    foreach ( $value2 as $key_sub => $value_sub ) {
                        //create a object for the array element
                        $current_object[ 'line' ]      = $value_sub;
                        //this will host the html and php tags
                        $current_object[ 'tags' ]      = array( );
                        $current_object[ 'line' ]      = remove_tags( $current_object[ 'line' ], $current_object[ 'tags' ] );
                        //encode tags for saving to db
                        $current_object[ 'tags' ]      = json_encode( $current_object[ 'tags' ] );
                        $current_object[ 'sub_index' ] = $key_sub;
                        array_push( $db_entries, $current_object );
                    } //$value2 as $key_sub => $value_sub
                } //is_array( $value2 )
                else {
                    $current_object[ 'line' ]      = $value2;
                    //this will host the html and php tags
                    $current_object[ 'tags' ]      = array( );
                    $current_object[ 'line' ]      = remove_tags( $current_object[ 'line' ], $current_object[ 'tags' ] );
                    //encode tags for saving to db
                    $current_object[ 'tags' ]      = json_encode( $current_object[ 'tags' ] );
                    $current_object[ 'sub_index' ] = $key2;
                    array_push( $db_entries, $current_object );
                }
            } //$current_array as $key2 => $value2
        } //$index != '' && !empty( $index )
        else {
            $current_object[ 'array' ] = "{$array}";
            
            foreach ( $current_array as $key2 => $value2 ) {
                
                if ( !is_array( $value2 ) ) {
                    //create a object for the array element
                    $current_object[ 'line' ] = $value2;
                    //this will host the html and php tags
                    $current_object[ 'tags' ] = array( );
                    $current_object[ 'line' ] = remove_tags( $current_object[ 'line' ], $current_object[ 'tags' ] );
                    //encode tags for saving to db
                    $current_object[ 'tags' ] = json_encode( $current_object[ 'tags' ] );
                    
                    $current_object[ 'index' ]     = $key2;
                    $current_object[ 'sub_index' ] = -1;
                    array_push( $db_entries, $current_object );
                } //!is_array( $value2 )
            } //$current_array as $key2 => $value2
            
        }
    } //$language_array_names as $key => $value
    
    //save the edited arrays to the database
    foreach ( $db_entries as $key => $value ) {
        $value[ 'index' ]     = DB_escapeString( $value[ 'index' ] );
        $value[ 'sub_index' ] = DB_escapeString( $value[ 'sub_index' ] );
        $value[ 'line' ]      = DB_escapeString( $value[ 'line' ] );
        $value[ 'tags' ]      = DB_escapeString( $value[ 'tags' ] );
        $query                = "INSERT INTO {$_TABLES['originals']} (`id`, `language`, `plugin_name`, `language_array`, `array_index`, `sub_index`, `string`, `tags`)
    VALUES ('', '{$_CONF['language']}', 'core', '{$value['array']}', '{$value['index']}' , '{$value['sub_index']}' , '{$value['line']}', '{$value['tags']}' ) ";
        DB_query( $query );
    } //$db_entries as $key => $value
}
/**
 * @param string $line the array element from which the tags are removed
 * @param array  &$tags after the tags are removed they are keept here for later assembly
 */
function remove_tags( $line, &$tags )
{
    $tags[ 'html' ] = array( );
    $tags[ 'vars' ] = array( );
    $variables      = array(
         "%s",
        "%t",
        "%d",
        "%n",
        "%i",
        "%t",
        "%s",
        "{\$_DB_mysqldump_path}",
        "{\$_CONF['backup_path']}",
        "{\$_CONF['commentspeedlimit']}",
        "{\$_CONF['site_admin_url']}",
        "{\$_CONF['site_name']}",
        "{\$_CONF['site_url']}",
        "{\$_CONF['speedlimit']}",
        "{\$_USER['username']}",
        "{\$failures}",
        "{\$from}",
        "{\$fromemail}",
        "{\$qid}",
        "{\$shortmsg}",
        "{\$successes}",
        "{\$topic}",
        "{\$type}" 
    );
    foreach ( $variables as $key => $value ) {
        while ( strpos( $line, $value ) !== false ) {
            array_push( $tags[ 'vars' ], $value );
            $line = str_replace( $value, "VAR", $line );
        } //strpos( $line, $value ) !== false
    } //$variables as $key => $value
    remove_standard( $line, "<", ">", $tags[ 'html' ] );
    while ( strpos( $line, "TAG" ) !== false ) {
        $line = str_replace( "TAG", "<tag>", $line );
    } //strpos( $line, "TAG" ) !== false
    while ( strpos( $line, "VAR" ) !== false ) {
        $line = str_replace( "VAR", "<var>", $line );
    } //strpos( $line, "VAR" ) !== false
    return $line;
}
/**
 *@param $line string the string from which tags are to be removed
 *@param $key_begin string the begining of the tag
 *@param $key_end string the end of the tag
 *@param $ar
 */
function remove_standard( &$line, $key_begin, $key_end, &$array )
{
    while ( strpos( $line, $key_begin ) !== false && strpos( $line, $key_end ) !== false ) {
        $begin  = strpos( $line, $key_begin );
        $length = strpos( $line, $key_end ) + 1 - $begin;
        $tag    = substr( $line, $begin, $length );
        $line   = str_replace( $tag, "TAG", $line );
        array_push( $array, $tag );
    } //strpos( $line, $key_begin ) !== false && strpos( $line, $key_end ) !== false
}


/**
* Gets the list of array names used by Geeklog, it connects to the current language file and parses
* it as a string
* @return array the array of language array names
*/
function get_language_array_names( )
{
    global $_CONF;
    
    $file                 = file( $_CONF[ 'path_language' ] . $_CONF[ 'language' ] . '.php' );
    $language_array_names = array( );
    $current_array        = "";
    $db_entries           = array( );
    $current_object       = array( );
    
    
    foreach ( $file as $line_num => $line ) {
        $pivot = strpos( $line, '=>' );
        if ( ( strpos( $line, "LANG" ) !== false || strpos( $line, "MESSAGE" ) ) && strpos( $line, "= array" ) !== false ) {
            $current_array = substr( $line, 0, strpos( $line, " =" ) );
            $current_array = str_replace( " ", "", $current_array );
            $current_array = str_replace( "$", "", $current_array );
            array_push( $language_array_names, $current_array );
        } //( strpos( $line, "LANG" ) !== false || strpos( $line, "MESSAGE" ) ) && strpos( $line, "= array" ) !== false
        
    } //$file as $line_num => $line
    
    return $language_array_names;
}


?>