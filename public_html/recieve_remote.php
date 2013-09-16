<?php
require_once '../lib-common.php';
require_once $_CONF[ 'path_system' ] . 'lib-database.php';
require_once "./lib-translator.php";


$display = '';
$display .= COM_siteHeader( 'menu', $LANG_CROWDTRANSLATOR_1[ 'plugin_name' ] );
$display .= COM_startBlock( $LANG_CROWDTRANSLATOR_1[ 'plugin_name' ] . " - Remote Submision" );
$display .= "<p> Rules: <ul> <li> Only translations with more than 1 positive vote are sent </li>
<li> Translations which are already sent once will not be sent again </li>
<li> You need to get credentials from the site you are submiting to </li> </ul>";

if ( !isset( $_POST[ 'site_url' ] ) || empty( $_POST[ 'site_url' ] ) || !isset( $_POST[ 'site_name' ] ) || empty( $_POST[ 'site_name' ] ) || !isset( $_POST[ 'site_credentials' ] ) || empty( $_POST[ 'site_credentials' ] ) || !isset( $_POST[ 'language' ] ) && empty( $_POST[ 'language' ] ) ) {
    $display .= "<h1> You did not provide the credentials, please try again </h1>";
} else {
    $query  = "SELECT * FROM {$_TABLES['remote_credentials']} WHERE `site_name` = '{$_POST['site_name']}'";
    $result = DB_query( $query );
    
    if ( DB_numRows( $result ) != 1 ) {
        $display .= "<h1>You credentials don't appear to be valid</h1>";
    } else {
        $row = DB_fetchArray( $result );
        if ( $row[ 'password' ] != SEC_encryptPassword( $_POST[ 'site_credentials' ], $row[ 'salt' ], $_CONF[ 'pass_alg' ], $_CONF[ 'pass_stretch' ] ) ) {
            $display .= "<h1>You credentials don't appear to be valid</h1>";
        } else {
            $process_q = array( );
            $count     = $_POST[ 'count' ];
            $language  = $_POST[ 'language' ];
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
                        $input->site_credentials   = $_POST[ 'site_name' ];
                        $input->user_id            = -1;
                        $input->approval_counts    = 1;
                        $input->translation        = $_POST[ $base_name ];
                        //just to get on the safe side
                        if ( !get_magic_quotes_gpc() ) {
                            $input->language_full_name = DB_escapeString( $input->language_full_name );
                            $input->site_credentials   = DB_escapeString( $input->site_credentials );
                            $input->translation        = DB_escapeString( $input->translation );
                        } //!get_magic_quotes_gpc()
                        
                        //the process_q hold all object(translations) which should be saved to the database
                        if ( DB_count( $_TABLES[ 'translations' ], array(
                             'site_credentials',
                            'language_array',
                            'array_key',
                            'array_subindex',
                            'translation' 
                        ), array(
                             $input->site_credentials,
                            $input->language_array,
                            $input->array_key,
                            $input->array_subindex,
                            $input->translation 
                        ) ) == 0 )
                            array_push( $process_q, $input );
                    } //$faulty == false
                } //isset( $_POST[ $base_name ] ) && !empty( $_POST[ $base_name ] )
            } //$i = 0; $i < $count; $i++
            
            save_to_database( $process_q );
            $display .= count( $process_q ) . " translations saved! Thank you for your submision.";
            $display .= "Include this code to your site for bragging : <input type='text' value=\"<iframe src='http://{$_SERVER['HTTP_HOST']}/crowdtranslator/iframe_badge.php?site_name=local'></iframe>\" readonly />";
        }
        
    }
    
}
$display .= COM_endBlock();
$display .= COM_siteFooter();
echo $display;

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