<?php
define( 'XHTML', '" . XHTML . "' );
require_once $_CONF[ 'path_html' ] . "lib-common.php";
require_once $_CONF[ 'path_system' ] . 'lib-database.php';
global $_CONF;

$real                        = array( );
$real[ 'site_url' ]          = $_CONF[ 'site_url' ];
$real[ 'commentspeedlimit' ] = $_CONF[ 'commentspeedlimit' ];
$real[ 'site_name' ]         = $_CONF[ 'site_name' ];
$real[ 'speedlimit' ]        = $_CONF[ 'speedlimit' ];
$real[ 'site_admin_url' ]    = $_CONF[ 'site_admin_url' ];
$real[ 'mysqldump_path' ]    = $_DB_mysqldump_path;
$real[ 'backup_path' ]       = $_CONF[ 'backup_path' ];
$real[ 'username' ]          = $_USER[ 'username' ];

$GLOBALS[ 'real_values' ] = $real;

$_CONF[ 'site_url' ]          = "{\$_CONF['site_url']}";
$_CONF[ 'commentspeedlimit' ] = "{\$_CONF['commentspeedlimit']}";
$_CONF[ 'site_name' ]         = "{\$_CONF['site_name']}";
$_CONF[ 'speedlimit' ]        = "{\$_CONF['speedlimit']}";
$_CONF[ 'site_admin_url' ]    = "{\$_CONF['site_admin_url']}";
$_DB_mysqldump_path           = "{\$_DB_mysqldump_path}";
$_CONF[ 'backup_path' ]       = "{\$_CONF['backup_path']}";
$_USER[ 'username' ]          = "{\$_USER['username']}";
require_once $_CONF[ 'path' ] . "language/english_utf-8.php";


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
function str_lreplace( $search, $replace, $subject )
{
    $pos = strrpos( $subject, $search );
    if ( $pos !== false ) {
        $subject = substr_replace( $subject, $replace, $pos, strlen( $search ) );
    } //$pos !== false
    return $subject;
}


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





//Following code is taken from stackoverflow, provided by user bfrohs - http://stackoverflow.com/users/526741/bfrohs
/**
 * Checks if $string is a valid integer. Integers provided as strings (e.g. '2' vs 2)
 * are also supported.
 * @param mixed $string
 * @return bool Returns boolean TRUE if string is a valid integer, or FALSE if it is not 
 */
function valid_integer( $string )
{
    // 1. Cast as string (in case integer is provided)
    // 1. Convert the string to an integer and back to a string
    // 2. Check if identical (note: 'identical', NOT just 'equal')
    // Note: TRUE, FALSE, and NULL $string values all return FALSE
    $string = strval( $string );
    return ( $string === strval( intval( $string ) ) );
}
/**
 * Replace $limit occurences of the search string with the replacement string
 * @param mixed $search The value being searched for, otherwise known as the needle. An
 * array may be used to designate multiple needles.
 * @param mixed $replace The replacement value that replaces found search values. An
 * array may be used to designate multiple replacements.
 * @param mixed $subject The string or array being searched and replaced on, otherwise
 * known as the haystack. If subject is an array, then the search and replace is
 * performed with every entry of subject, and the return value is an array as well. 
 * @param string $count If passed, this will be set to the number of replacements
 * performed.
 * @param int $limit The maximum possible replacements for each pattern in each subject
 * string. Defaults to -1 (no limit).
 * @return string This function returns a string with the replaced values.
 */
function str_replace_limit( $search, $replace, $subject, &$count, $limit = -1 )
{
    // Set some defaults
    $count = 0;
    // Invalid $limit provided. Throw a warning.
    if ( !valid_integer( $limit ) ) {
        $backtrace = debug_backtrace();
        trigger_error( 'Invalid $limit `' . $limit . '` provided to ' . __function__ . '() in ' . '`' . $backtrace[ 0 ][ 'file' ] . '` on line ' . $backtrace[ 0 ][ 'line' ] . '. Expecting an ' . 'integer', E_USER_WARNING );
        return $subject;
    } //!valid_integer( $limit )
    // Invalid $limit provided. Throw a warning.
    if ( $limit < -1 ) {
        $backtrace = debug_backtrace();
        trigger_error( 'Invalid $limit `' . $limit . '` provided to ' . __function__ . '() in ' . '`' . $backtrace[ 0 ][ 'file' ] . '` on line ' . $backtrace[ 0 ][ 'line' ] . '. Expecting -1 or ' . 'a positive integer', E_USER_WARNING );
        return $subject;
    } //$limit < -1
    // No replacements necessary. Throw a notice as this was most likely not the intended
    // use. And, if it was (e.g. part of a loop, setting $limit dynamically), it can be
    // worked around by simply checking to see if $limit===0, and if it does, skip the
    // function call (and set $count to 0, if applicable).
    if ( $limit === 0 ) {
        $backtrace = debug_backtrace();
        trigger_error( 'Invalid $limit `' . $limit . '` provided to ' . __function__ . '() in ' . '`' . $backtrace[ 0 ][ 'file' ] . '` on line ' . $backtrace[ 0 ][ 'line' ] . '. Expecting -1 or ' . 'a positive integer', E_USER_NOTICE );
        return $subject;
    } //$limit === 0
    // Use str_replace() whenever possible (for performance reasons)
    if ( $limit === -1 ) {
        return str_replace( $search, $replace, $subject, $count );
    } //$limit === -1
    if ( is_array( $subject ) ) {
        // Loop through $subject values and call this function for each one.
        foreach ( $subject as $key => $this_subject ) {
            // Skip values that are arrays (to match str_replace()).
            if ( !is_array( $this_subject ) ) {
                // Call this function again for
                $this_function   = __FUNCTION__;
                $subject[ $key ] = $this_function( $search, $replace, $this_subject, $this_count, $limit );
                // Adjust $count
                $count += $this_count;
                // Adjust $limit, if not -1
                if ( $limit != -1 ) {
                    $limit -= $this_count;
                } //$limit != -1
                // Reached $limit, return $subject
                if ( $limit === 0 ) {
                    return $subject;
                } //$limit === 0
            } //!is_array( $this_subject )
        } //$subject as $key => $this_subject
        return $subject;
    } //is_array( $subject )
    elseif ( is_array( $search ) ) {
        // Only treat $replace as an array if $search is also an array (to match str_replace())
        // Clear keys of $search (to match str_replace()).
        $search = array_values( $search );
        // Clear keys of $replace, if applicable (to match str_replace()).
        if ( is_array( $replace ) ) {
            $replace = array_values( $replace );
        } //is_array( $replace )
        // Loop through $search array.
        foreach ( $search as $key => $this_search ) {
            // Don't support multi-dimensional arrays (to match str_replace()).
            $this_search = strval( $this_search );
            // If $replace is an array, use the value of $replace[$key] as the replacement. If
            // $replace[$key] doesn't exist, just an empty string (to match str_replace()).
            if ( is_array( $replace ) ) {
                if ( array_key_exists( $key, $replace ) ) {
                    $this_replace = strval( $replace[ $key ] );
                } //array_key_exists( $key, $replace )
                else {
                    $this_replace = '';
                }
            } //is_array( $replace )
            else {
                $this_replace = strval( $replace );
            }
            // Call this function again for
            $this_function = __FUNCTION__;
            $subject       = $this_function( $this_search, $this_replace, $subject, $this_count, $limit );
            // Adjust $count
            $count += $this_count;
            // Adjust $limit, if not -1
            if ( $limit != -1 ) {
                $limit -= $this_count;
            } //$limit != -1
            // Reached $limit, return $subject
            if ( $limit === 0 ) {
                return $subject;
            } //$limit === 0
        } //$search as $key => $this_search
        return $subject;
    } //is_array( $search )
    else {
        $search  = strval( $search );
        $replace = strval( $replace );
        // Get position of first $search
        $pos     = strpos( $subject, $search );
        // Return $subject if $search cannot be found
        if ( $pos === false ) {
            return $subject;
        } //$pos === false
        // Get length of $search, to make proper replacement later on
        $search_len = strlen( $search );
        // Loop until $search can no longer be found, or $limit is reached
        for ( $i = 0; ( ( $i < $limit ) || ( $limit === -1 ) ); $i++ ) {
            // Replace 
            $subject = substr_replace( $subject, $replace, $pos, $search_len );
            // Increase $count
            $count++;
            // Get location of next $search
            $pos = strpos( $subject, $search );
            // Break out of loop if $needle
            if ( $pos === false ) {
                break;
            } //$pos === false
        } //$i = 0; ( ( $i < $limit ) || ( $limit === -1 ) ); $i++
        // Return new $subject
        return $subject;
    }
}
?>