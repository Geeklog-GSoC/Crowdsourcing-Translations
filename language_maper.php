<?php
$ignored_files;
$proces_queue;
$ignore_dir;

/**
 * Will do the LANG array mapping
 */
function language_maper( )
{
    global $_TABLES, $_CONF;
    global $ignore_dir, $proces_queue, $ignored_files;
    require_once $_CONF[ 'path_html' ] . '/lib-common.php';
    require_once $_CONF[ 'path_system' ] . 'lib-database.php';
    require_once $_CONF[ 'path' ] . 'plugins/crowdtranslator/language_markup.php';
    /* This part will compile a list of files the plugin is interested in
    files which use $LANG arrays */
    $ignore_dir   = array(
         'backups',
        'data',
        'images',
        'language',
        'logs',
        'plugins',
        'sql',
        'oauth',
        'sundication',
        'databases',
        'pear',
        'db-config.php',
        'crowdtranslator' 
    );
    $proces_queue = array( );
    $files_list   = array( );
    get_process_queue( $_CONF[ 'path_html' ] );
    if ( $_CONF[ 'path_html' ] != $_CONF[ 'path_system' ] )
        get_process_queue( $_CONF[ 'path_system' ] );
    ksort( $proces_queue );
    /*The next part will analyze all the files from the proces_que and find exactly which LANG's are used in the file
    and which files the current one includes*/
    $language_array_names  = get_language_array_names();
    $include_possibilities = array(
         'require_once' 
    );
    foreach ( $proces_queue as $path => $file_array ) {
        foreach ( $file_array as $key => $file_name ) {
            $references_on_this_page = array( );
            $includes_on_this_page   = array( );
            if ( strpos( $file_name, ".php" ) == false )
                continue;
            $file = file_get_contents( $file_name );
            /*Extracting LANG variables*/
            foreach ( $language_array_names as $key => $value ) {
                $regexp = "/" . preg_quote( $value . "[" ) . "'{0,1}\w{1,}'{0,1}" . preg_quote( "]" ) . "/i";
                preg_match_all( $regexp, $file, $matches, PREG_OFFSET_CAPTURE );
                extract_from_matches( $matches, $references_on_this_page );
            } //$language_array_names as $key => $value
            /*extracting includes*/
            foreach ( $include_possibilities as $key => $value ) {
                $regexp         = "/[a-z\-\/\.]+\.php/";
                $start_position = strpos( $file, $value );
                while ( $start_position !== false ) {
                    $start_position += strlen( $value );
                    $length  = strpos( $file, ';', $start_position ) - $start_position;
                    $include = substr( $file, $start_position, $length );
                    $include = basename( $include );
                    preg_match_all( $regexp, $include, $matches, PREG_OFFSET_CAPTURE );
                    extract_from_matches( $matches, $includes_on_this_page );
                    $file           = str_replace_limit( $value, 'found', $file, $count, 1 );
                    $start_position = strpos( $file, $value );
                } //$start_position !== false
            } //$include_possibilities as $key => $value
            $file_object                      = new stdClass();
            $file_object->path                = $path;
            $file_object->name                = $file_name;
            $file_object->includes            = $includes_on_this_page;
            $file_object->references          = $references_on_this_page;
            $files_list[ $file_object->name ] = $file_object;
        } //$file_array as $key => $file_name
    } //$proces_queue as $path => $file_array
    foreach ( $files_list as $key => $value ) {
        if ( count( $value->references ) > 0 || count( $value->includes ) > 0 ) {
            $references = DB_escapeString( json_encode( $value->references ) );
            $includes   = DB_escapeString( json_encode( $value->includes ) );
            $name       = DB_escapeString( $value->name );
            $query      = "INSERT INTO {$_TABLES['language_map']} (`page_url`, `reference`, `includes`) VALUES ('{$name}', '{$references}', '{$includes}') ";
            $result     = DB_query( $query );
        } //count( $value->references ) > 0 || count( $value->includes ) > 0
    } //$files_list as $key => $value
    add_identifier_to_lanugage_file();
    
    global $_CONF;
    
    $real                         = $GLOBALS[ 'real_values' ];
    $_CONF[ 'site_url' ]          = $real[ 'site_url' ];
    $_CONF[ 'commentspeedlimit' ] = $real[ 'commentspeedlimit' ];
    $_CONF[ 'site_name' ]         = $real[ 'site_name' ];
    $_CONF[ 'speedlimit' ]        = $real[ 'speedlimit' ];
    $_CONF[ 'site_admin_url' ]    = $real[ 'site_admin_url' ];
    $_DB_mysqldump_path           = $real[ 'mysqldump_path' ];
    $_CONF[ 'backup_path' ]       = $real[ 'backup_path' ];
    $_USER[ 'username' ]          = $real[ 'username' ];
    
    
}

/**
 *@param array matches matches found using the regex, for includes and LANG's
 *@param arrau &push_array the array holding all found matches without duplicates
 */
function extract_from_matches( $matches, &$push_array )
{
    foreach ( $matches as $key => $value ) {
        foreach ( $value as $key2 => $value2 ) {
            if ( !in_array( $value2[ 0 ], $push_array ) )
                array_push( $push_array, $value2[ 0 ] );
        } //$value as $key2 => $value2
    } //$matches as $key => $value
}

/**
 * @param string path the path of the current folder
 */
function get_process_queue( $path )
{
    global $ignore_dir, $proces_queue, $ignored_files;
    foreach ( new DirectoryIterator( $path ) as $fileInfo ) {
        if ( $fileInfo->isDot() || in_array( $fileInfo->getFilename(), $ignore_dir ) || ( $fileInfo->getExtension() != 'php' && !$fileInfo->isDir() ) ) {
            continue;
        } //$fileInfo->isDot() || in_array( $fileInfo->getFilename(), $ignore_dir ) || ( $fileInfo->getExtension() != 'php' && !$fileInfo->isDir() )
        else {
            if ( $fileInfo->isDir() ) {
                get_process_queue( $fileInfo->getRealPath() );
            } //$fileInfo->isDir()
            if ( !isset( $proces_queue[ $path ] ) ) {
                $proces_queue[ $path ] = array( );
            } //!isset( $proces_queue[ $path ] )
            array_push( $proces_queue[ $path ], $fileInfo->getRealPath() );
        }
    } //new DirectoryIterator( $path ) as $fileInfo
}
?>