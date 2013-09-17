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

if ( !isset( $_POST[ 'language' ] ) || empty( $_POST[ 'language' ] ) ) {
    $error = array(
         'error' => 'Language not set' 
    );
    echo json_encode( $error );
    exit;
}

define( 'XHTML', '" . XHTML . "' );
require_once '../lib-common.php';
require_once './lib-translator.php';
require_once $_CONF[ 'path_system' ] . 'lib-database.php';

$quote_type           = array( );
$language_array_names = get_language_array_names( $quote_type );

/*VARIABLE SET*/
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


$replace[ 'site_url' ]          = "{\$_CONF['site_url']}";
$replace[ 'commentspeedlimit' ] = "{\$_CONF['commentspeedlimit']}";
$replace[ 'site_name' ]         = "{\$_CONF['site_name']}";
$replace[ 'speedlimit' ]        = "{\$_CONF['speedlimit']}";
$replace[ 'site_admin_url' ]    = "{\$_CONF[ 'site_admin_url' ]";
$replace[ 'mysqldump_path' ]    = "{\$_DB_mysqldump_path";
$replace[ 'backup_path' ]       = "{\$_CONF[ 'backup_path' ]";
$replace[ 'username' ]          = "{\$_USER[ 'username' ]";

$_CONF[ 'site_url' ]          = "{\$_CONF['site_url']}";
$_CONF[ 'commentspeedlimit' ] = "{\$_CONF['commentspeedlimit']}";
$_CONF[ 'site_name' ]         = "{\$_CONF['site_name']}";
$_CONF[ 'speedlimit' ]        = "{\$_CONF['speedlimit']}";
$_CONF[ 'site_admin_url' ]    = "{\$_CONF['site_admin_url']}";
$_DB_mysqldump_path           = "{\$_DB_mysqldump_path}";
$_CONF[ 'backup_path' ]       = "{\$_CONF['backup_path']}";
$_USER[ 'username' ]          = "{\$_USER['username']}";



require_once $_CONF[ 'path' ] . "language/english_utf-8.php";

$language_name = $_POST[ 'language' ];
//$header_string 
$file_content  = "<?php

    ###############################################################################
# {$language_name}.php
#
# This is the {$language_name} language file for Geeklog
# This file was getenrated with CrowdTranslator plugin
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#
###############################################################################

\$LANG_CHARSET = '{$LANG_CHARSET}';

###############################################################################
# Array Format:
# \$LANGXX[YY]:  \$LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

###############################################################################
# USER PHRASES - These are file phrases used in end user scripts
###############################################################################

###############################################################################
# lib-common.php\n\n";


$query    = "SELECT `language_file` FROM {$_TABLES['translations']} WHERE `language_full_name`='{$language_name}' ";
$result   = DB_query( $query );
$result   = DB_fetchArray( $result );
$filename = $result[ 'language_file' ];

$filename = $_CONF[ 'path' ] . "language/" . $language_name . ".php";

$spacing          = "    ";
$quote_type_count = 0;
$all_elements     = 0;
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
    if ( $index != '' && !empty( $index ) )
        $file_content .= "\${$array}['{$index}'] = array( \n";
    else
        $file_content .= "\${$array} = array( \n";
    
    print_array( $file_content, $current_array, $spacing, $quote_type, $quote_type_count );
    
} //$language_array_names as $key => $value


$file_content .= "\n\n ?>";
file_put_contents( $filename, $file_content );
$response = array(
     "packed" => "true",
    "filename" => $filename 
);
echo json_encode( $response );

/**
* Will format the array output for the new language file, the output is generated line by line - each line is one array element
* @param string file_content the previously generated content of the file
* @param array current_array The currently processed array
* @param string spacing The indentation used
* @param array quote_type The type of quotes (' or ") used for the current line
* @param array quote_type_count counter keeping track on which quote should be used
*/
function print_array( &$file_content, $current_array, $spacing, $quote_type, &$quote_type_count )
{
    global $_CONF, $real, $replace;
    
    $num_elements = count( $current_array );
    $count        = 0;
    foreach ( $current_array as $key2 => $value2 ) {
        
        if ( !is_array( $value2 ) ) {
            
            $value2 = getPhraseFromDB( $current_array, $key2, $value2 );
            
            if ( strpos( $value2, '{$' ) !== false )
                $quote_type[ $quote_type_count ] = 'd';
            
            if ( strpos( $value2, "{" ) !== false ) {
                $value2_new = '';
                while ( strpos( $value2, "{" ) !== false ) {
                    $value2_new .= addslashesPacking( $quote_type[ $quote_type_count ], substr( $value2, 0, strpos( $value2, "{" ) ) ) . substr( $value2, strpos( $value2, "{" ), strpos( $value2, "}" ) + 1 - strpos( $value2, "{" ) );
                    $value2 = substr( $value2, strpos( $value2, "}" ) + 1 );
                } //strpos( $value2, "{" ) !== false
                $value2 = $value2_new . addslashesPacking( $quote_type[ $quote_type_count ], $value2 );
                if ( strpos( $value2, '\" . XHTML . \"' ) !== false )
                    $value2 = str_replace( '\" . XHTML . \"', '". XHTML ."', $value2 );
            } //strpos( $value2, "{" ) !== false
            else
                $value2 = addslashesPacking( $quote_type[ $quote_type_count ], $value2 );
            
            if ( $quote_type[ $quote_type_count ] == 's' )
                $value2 = "'{$value2}'";
            else
                $value2 = "\"{$value2}\"";
            $quote_type_count++;
            if ( strpos( $value2, '\" . XHTML . \"' ) !== false ) {
                $value2 = str_replace( '\" . XHTML . \"', '". XHTML ."', $value2 );
            } //strpos( $value2, '\" . XHTML . \"' ) !== false
            if ( is_numeric( $key2 ) )
                $file_content .= $spacing . "{$key2} => {$value2}";
            else
                $file_content .= $spacing . "'{$key2}' => {$value2}";
            
        } //!is_array( $value2 )
        else {
            print_sub_array( $file_content, $value2, $key2, $spacing, $quote_type, $quote_type_count );
        }
        if ( ++$count != $num_elements ) {
            $file_content .= ",\n";
        } //++$count != $num_elements
        else {
            $file_content .= "\n ); \n\n";
        }
    } //$current_array as $key2 => $value2
}

/**
* Will format the sub array output for the new language file, the output is generated line by line - each line is one array element
* @param string file_content the previously generated content of the file
* @param array value2 The currently processed sub array
* @param array key2 The currently processed sub array name/index
* @param string spacing The indentation used
* @param array quote_type The type of quotes (' or ") used for the current line
* @param array quote_type_count counter keeping track on which quote should be used
*/
function print_sub_array( &$file_content, $value2, $key2, $spacing, $quote_type, &$quote_type_count )
{
    
    global $_CONF;
    $quote_type_count++;
    $file_content .= $spacing . "{$key2} => array(";
    $num_elements = count( $value2 );
    $count        = 0;
    foreach ( $value2 as $key_sub => $value_sub ) {
        $value_sub = getPhraseFromDB( $current_array, $key2, $key_sub, $value2 );
        
        if ( strpos( $value_sub, '{$' ) !== false )
            $quote_type[ $quote_type_count ] = 'd';
        
        $value_sub = addslashesPacking( $quote_type[ $quote_type_count ], $value_sub );
        
        $key_sub = addslashes( $key_sub );
        if ( !is_numeric( $value_sub ) ) {
            if ( $quote_type[ $quote_type_count ] == 's' )
                $value_sub = "'{$value_sub}'";
            else
                $value_sub = "\"{$value_sub}\"";
            
        }
        if ( is_numeric( $key_sub ) )
            $file_content .= "{$key_sub}=>{$value_sub}";
        else
            $file_content .= "'{$key_sub}'=>{$value_sub}";
        if ( ++$count != $num_elements ) {
            $file_content .= ',';
        } //++$count != $num_elements
        else {
            $file_content .= ")";
        }
    } //$value2 as $key_sub => $value_sub
    
}

/**
* Gets the list of array names used by Geeklog, it connects to the current language file and parses
* it as a string
* @param array quote_type This will be populated by quote types used on certain lines
* @return array the array of language array names
*/
function get_language_array_names( &$quote_type )
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
        
        if ( $pivot !== false ) {
            $string = substr( $line, $pivot );
            $s      = strpos( $string, "'" );
            $d      = strpos( $string, '"' );
            if ( $s == false )
                $s = 99999999999999999999;
            if ( $d == false )
                $d = 99999999999999999999;
            if ( $s < $d )
                array_push( $quote_type, "s" );
            else
                array_push( $quote_type, "d" );
        }
        
        
    } //$file as $line_num => $line
    
    return $language_array_names;
}

/**
* In order to prevent escaping phrase caracters (e.g. Don't => Don\'t) this function
* will do custom escaping
* @param string this_quote_type The quote type used for the current line (s for ' or d for ")
* @param string value The actual string we are escaping
* @return string the escaped string
*/
function addslashesPacking( $this_quote_type, $value )
{
    if ( $this_quote_type == 's' ) {
        return str_replace( "'", "\'", $value );
    } else {
        return str_replace( '"', '\"', $value );
    }
}

/**
* Checks if current LANG element is translated (inside the database) if it is it will be formated and returned
* @param array current_array currently processed array
* @param string array_key the current array key
* @param string value2 the curretn phrase
* @param string array_subindex if set the index of the sub-array default value = -1
* @return string eaither value2 or the translation found in the database
*/
function getPhraseFromDB( $current_array, $array_key, $value2, $array_subindex = -1 )
{
    global $language_name, $array, $index, $_TABLES, $real, $replace;
    
    if ( $index != '' ) {
        $array_index    = $index;
        $array_subindex = $array_key;
    } else {
        $array_index = $array_key;
    }
    
    
    $query = "SELECT `translation` FROM {$_TABLES['translations']} WHERE `language_array` = '{$array}' AND `array_key` = '{$array_index}' AND `array_subindex` = '{$array_subindex}'
    ORDER BY `approval_counts` LIMIT 1";
    
    $result = DB_query( $query );
    
    if ( $result && DB_numRows( $result ) == 1 ) {
        $row         = DB_fetchArray( $result );
        $translation = $row[ 'translation' ];
        $query       = "SELECT `tags` FROM {$_TABLES['originals']} WHERE `language_array` = '{$array}' AND `array_index` = '{$array_index}' AND `sub_index` = '{$array_subindex}'";
        $result      = DB_query( $query );
        if ( $result && DB_numRows( $result ) > 0 ) {
            $row  = DB_fetchArray( $result );
            $tags = json_decode( $row[ 'tags' ] );
            foreach ( $tags->html as $key => $value ) {
                $translation = str_replace_limit( "<tag>", $value, $translation, $count, 1 );
            }
            foreach ( $tags->vars as $key => $value ) {
                $translation = str_replace_limit( "<var>", $value, $translation, $count, 1 );
            }
        }
        foreach ( $real as $key => $value ) {
            $translation = str_replace( $value, $replace[ $key ], $translation );
        }
        return $translation;
    }
    
    return $value2;
}


?>