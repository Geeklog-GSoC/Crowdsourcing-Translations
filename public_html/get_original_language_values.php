<?php

/*
* Script will take extracted array data find the original array values from the database
* where all variables and html tags have been replaced with <tag> and create the 
* HTML of the translation form
* before it is saved to the database
*/

require_once '../lib-common.php';
require_once ($_CONF['path_system'] . 'lib-database.php');
require_once "./get_translation_percent.php";


$language=$_REQUEST['language'];
$myArray = json_decode($_REQUEST['objects']);

$response=array();
$language_array=array();
$taged_strings=array();

foreach ($myArray as $key => $value) {
    array_push($language_array, $value);
}

$user_id=$_USER['uid'];
$base_url=$_CONF['site_url'] . "/CrowdTranslator/images/";
$up_image=$base_url."up.png";
$down_image=$base_url."down.png";

$form="<form id='translator_form_submission' method='post' action='{$_CONF['site_url']}/CrowdTranslator/submit_translation.php' >"
."<div id='submision_success' class='success'></div>" 
."<div id='submision_error' class='error'></div>"
."<span><img id='up_img' src='{$up_image}' onclick='show_previous()' class='hidden navigation_images' /></span></br>";


//when count hits a certain number remaining input fields will be assigned a CSS class to hide them
$count=0;
foreach ($language_array as $key => $value) {
    $result=DB_query("SELECT  `string`, `tags` FROM {$_TABLES['originals']} WHERE `language_array`='{$value->array_name}' AND `array_index`='{$value->array_index}' ");
    
    while($row=DB_fetchArray ($result) ){
        //making <var> and <tag> html friendly
        if( strpos($row['string'], "<tag>") !== false || strpos($row['string'], "<var>") !== false ){
            
            $taged=new stdClass();
            $taged->tag=substr_count($row['string'],"<tag>");
            $taged->var=substr_count($row['string'],"<var>");

            $value->string=str_replace("<tag>", "&lttag&gt", $row['string']);
            $value->string=str_replace("<var>", "&ltvar&gt", $value->string);
            $value->tags=$row['tags'];

            $taged_strings[$key]=$taged;
        } else {
            $value->string=$row['string'];
        }
        
        
    }

    //check if current string has translation in the database, picks the one with the best vote
    $result=DB_query("SELECT `translation`, `id` FROM {$_TABLES['translations']} WHERE `language_full_name`='{$language}' AND  `language_array`='{$value->array_name}' AND `array_key`='{$value->array_index}' ORDER BY `approval_counts` DESC LIMIT 1");
    if($row=DB_fetchArray($result)){
        $value->translation=$row['translation'];
        $value->translation_id=$row['id'];
    } else {
        $value->translation='';
    }

    $disabled_up=''; $disabled_down='';
    //if the user has voted for the current string the upvote or downvote buttons should be disabled
    if($value->translation_id){
        $result = DB_query("SELECT `sign` FROM {$_TABLES['votes']} WHERE `user_id` = {$user_id} AND `translation_id`='{$value->translation_id}'");

        if($row=DB_fetchArray($result)){
            $sign=$row['sign'];
            if($sign=='1'){
                $disabled_up='disabled';
            }
            else
                $disabled_down='disabled';
        }
    }

    //assembles the next input element
    add_form_element($form, $count, $value, $base_url, $disabled_up, $disabled_down);

    $count++;
}


//finalizes the form
$form .= "<span><img id='down_img' src='{$down_image}' onclick='show_next()' class='navigation_images' /></span>"
."<button type='submit' id='submit_form'>Submit Translations</button>"
."</form>";


$response['language_array']=$language_array;
$response['form']=$form;
$response['taged_strings']=$taged_strings;
$response['translated']=$translated;

echo json_encode($response);


/**
* @param string form The HTML of the translation form
* @param int count number of current input field
* @param object value current translation object, holds all relevant data for the string
* @param string base_url base url for the required resources
* @param string disable_up Will either be empty string or disable if the vote button should be disabled
* @param string disable_down Will either be empty string or disable if the vote button should be disabled
*/


function add_form_element(&$form, $count, $value, $base_url, $disabled_up, $disabled_down)
{

    $highlight_image=$base_url."highlight.png";
    $remove_highlight_image=$base_url."rhighlight.png";
    $vote_up_image=$base_url."vote_up.png";
    $vote_down_image=$base_url."vote_down.png";
    
    

    $form_label = "<label for='translator_input_{$count}'>{$value->string}</label>"
    ." <img class='form_image' src='{$remove_highlight_image}' id='translator_input_{$count}_image' onclick=remove_highlight() />"
    ." <img class='form_image' src='{$highlight_image}' id='translator_input_{$count}_image' onclick=highlight() />";

    $form_input1 = "<input type='text' id='translator_input_{$count}' name='translator_input_{$count}' />";

    $form_input2 = "<div class='suggested'> <span id='translator_input_{$count}' >   {$value->translation} </span>"
    ."<span class='votes'> <button type='button' id='vote_up_button_{$count}' {$disabled_up} class='vote-button'  onclick='vote(1, {$count}, this)'   > <img src='{$vote_up_image}' /> </button>"
    ." <button type='button' class='vote-button' id='vote_down_button_{$count}' {$disabled_down} onclick='vote(-1, {$count}, this)'  > <img src='{$vote_down_image}' />  </button> </span> </div>";

    $form_hidden_input = "<input id='translator_input_{$count}_hidden' class='hidden' name='translator_input_{$count}_hidden' value='{$value->metadata}' />";

    if(strlen($value->translation)>0){

        if ($count > 5) {
            $template = "<span id='input_span_{$count}' class='group_input temp_hidden'>{$form_label} {$form_input2}<label >"
            ."or enter your own: </label>{$form_input1} {$form_hidden_input}</span>";
        } else {
            $template = "<span id='input_span_{$count}' class='group_input'>{$form_label}{$form_input2} <label > or enter your own: </label>"
            ."{$form_input1} {$form_hidden_input} </span>";
        }

    } else {
        if ($count > 5) {
            $template = "<span id='input_span_{$count}' class='temp_hidden'> {$form_label} {$form_input1} {$form_hidden_input} </span>";
        } else {
            $template = "<span id='input_span_{$count}'>{$form_label}{$form_input1} {$form_hidden_input} </span>";
        }
    }

    $form.=$template;
}


?>