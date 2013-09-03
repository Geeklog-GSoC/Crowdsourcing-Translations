<?php
/*
 * Script will take extracted array data find the original array values from the database
 * where all variables and html tags have been replaced with <tag> and create the 
 * HTML of the translation form
 * before it is saved to the database
 */
require_once '../lib-common.php';
require_once $_CONF[ 'path_system' ] . 'lib-database.php';
require_once "./lib-translator.php";
$html           = $_POST[ 'html' ];
$language       = $_POST[ 'language' ];
$response       = array( );
$language_array = get_language_array();
$taged_strings  = purge_language_array( $html, $language_array );

/*foreach ($language_array as $key => $value) {
echo "String: {$value->string}</br> Parsed: {$value->parsed} </br></br>";
}*/
$user_id        = $_USER[ 'uid' ];
$base_url       = $_CONF[ 'site_url' ] . "/crowdtranslator/images/";
$up_image       = $base_url . "up.png";
$down_image     = $base_url . "down.png";
$form           = "<form id='translator_form_submission' method='post' action='{$_CONF['site_url']}/crowdtranslator/submit_translation.php' >" . "<div id='submision_success' class='success'></div>" . "<div id='submision_error' class='error'></div>" . "<span><img id='up_img' src='{$up_image}' onclick='show_previous()' class='hidden navigation_images' /></span></br>";
//when count hits a certain number remaining input fields will be assigned a CSS class to hide them
$count          = 0;
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
function get_page_url( )
{
    global $_POST;
    $page_url =  $_POST[ 'url' ];
    if ( strpos( $page_url, ".php" ) == false )
        $page_url = "index.php";
    $page_url = basename( $page_url );
    COM_accessLog( "URL: $page_url" );
    return $page_url;
}
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
function get_array( $string )
{
    $regexp = "/\w{1,}\[/";
    preg_match( $regexp, $string, $matches, PREG_OFFSET_CAPTURE );
    $array = $matches[ 0 ][ 0 ];
    $array = substr( $array, 0, strlen( $array ) - 1 );
    return $array;
}
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

function get_subindex( $array, $index, $string )
{
    $subindex = substr( $string, strlen("{$array}[{$index}]"));
    $regexp = "/\['*\w{1,}'*\]/";
    preg_match( $regexp, $subindex, $matches, PREG_OFFSET_CAPTURE );
    if (!empty($matches) ) {
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
    foreach ( $reference as $key => $value ) {
        $obj              = new stdClass();
        $array            = get_array( $value );
        $index            = get_index( $value );
        $subindex         = get_subindex($array, $index, $value);
        $obj->array_name  = $array;
        $obj->array_index = $index;
        $obj->array_subindex = $subindex;
        $obj->metadata    = "array_{$array}index_{$index}subindex_{$subindex}";
        $obj->string      = '';
        array_push( $language_array, $obj );
    }
    return $language_array;
}


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
                    $language_array[ $key ]->parsed = $GLOBALS[$language_array[$key]->array_name][$language_array[$key]->array_index];
                }
            } else {
                unset( $language_array[ $key ] );
            }
        } else {
            //echo $value->string."</br>";
            $this_string = $value->string;
            $this_string = str_replace( "&lttag&gt", "splitstring", $this_string );
            $this_string = str_replace( "&ltvar&gt", "splitstring", $this_string );
            $substrings  = explode( "splitstring", $this_string );
            $in_html     = true;
            $offset = 0;
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
                $language_array[ $key ]->parsed = $GLOBALS[$language_array[$key]->array_name][$language_array[$key]->array_index];
            }
        }
    }
    $language_array = array_values( $language_array );
   
    $taged_strings = array_values( $taged_strings );
    return $taged_strings;
}
?>