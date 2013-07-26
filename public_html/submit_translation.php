<?php

require_once '../lib-common.php';
require_once ($_CONF['path_system'] . 'lib-database.php');
require_once "./get_translation_percent.php";

$response=array();


$taged_strings=json_decode($_POST['taged_strings']);
$bad_input=array();
$good_input=array();
$process_q=array();
$count=$_POST['count'];


//loop through all the input fields
for($i=0; $i<$count; $i++){
    $base_name="translator_input_{$i}";
    $metadata_name="translator_input_{$i}_hidden";

    //checks if the user has submited a translation to input field
    if(isset($_POST[$base_name]) && !empty($_POST[$base_name])){
        $faulty=false;
        
        //check for bad inputs- missing <var> and <tag> 
        if(isset($taged_strings->$i)){
            $_POST[$base_name]=str_replace("&lttag&gt", "<tag>",  $_POST[$base_name]);
            $_POST[$base_name]=str_replace("&ltvar&gt", "<var>",  $_POST[$base_name]);
            
            //if there is a lack of <tag> or <var> in the translation the input is marked as faulty
            if(  substr_count($_POST[$base_name], "<tag>") != $taged_strings->$i->tag || substr_count($_POST[$base_name], "<var>") != $taged_strings->$i->var  ){
                //will be used by the JS to mark faulty inputs
                array_push($bad_input, $i);
                $faulty=true;
            }
        }

        //if the input passed the previous test a new object is created with all relevant data for the translation
        if($faulty==false){
            $input=new stdClass();  
            extract_metadata($_POST[$metadata_name], $input->language_array, $input->array_key);
            $input->language_full_name=$language;
            $input->language_file=preg_replace('/[^a-z]/i','_', strtolower($language) );
            $input->plugin_name='core';
            $input->site_credentials='test credentials';
            $input->user_id=$_USER['uid'];
            $input->approval_counts=1;
            $input->translation=$_POST[$base_name];

            //just to get on the safe side
            if (!get_magic_quotes_gpc()){
                $input->language_full_name=addslashes($input->language_full_name);
                $input->site_credentials=addslashes($input->site_credentials);
                $input->translation=addslashes($input->translation);
            }

            //this will be used by the script to remove input fields
            array_push($good_input, $i);
            //the process_q hold all object(translations) which should be saved to the database
            array_push($process_q, $input);
        }

    }

}

$response['bad_input']=$bad_input;
$response['translated']=$translated;
$response['good_input']=$good_input;
//will save translations
save_to_database($process_q);
$response['awards_number'] = (int)(awards());
echo json_encode($response);

/**
* @param string metadata passed on via POST
* @param string language_array empty - the value will be extracted from the metadata string
* @param string language_array empty - the value will be extracted from the metadata string
*/
function extract_metadata($metadata, &$language_array, &$array_key)
{

    $begin=strpos($metadata, '_')+1;
    $end=strpos($metadata, 'index')-$begin;
    $language_array=substr($metadata, $begin, $end );

    $begin=strpos($metadata, '_', $begin+strlen($language_array))+1;
    $array_key=substr($metadata, $begin);
}

/**
* @param array process_q array of objects/translations to be saved to the database
*/
function save_to_database($process_q)
{

    global $_TABLES;

    $date=date('Y-m-d H:i:s');

    //saving translation to database
    foreach ($process_q as $key => $input) {
        $query="
        INSERT INTO {$_TABLES['translations']}(`id`, `language_full_name`, `language_file`, `plugin_name`, `site_credentials`, `user_id`,
            `timestamp`, `approval_counts`, `language_array`, `array_key`, `translation`)
VALUES ('', '{$input->language_full_name}' , 
    '{$input->language_file}', '{$input->plugin_name}', '{$input->site_credentials}', '{$input->user_id}', '{$date}', '{$input->approval_counts}',
    '{$input->language_array}','{$input->array_key}','{$input->translation}')";

$result=DB_query($query);

        //after the translation is saved the first vote is added to it (assuming the user who submited the vote would vote it up)
if($result==true){
    $query="SELECT MAX(`id`) as translation_id FROM {$_TABLES['translations']} ";
    $result=DB_query($query);

    $translation_id=DB_fetchArray($result)['translation_id'];
    $query="INSERT INTO {$_TABLES['votes']} (`translation_id`, `user_id`, `sign`) VALUES ('{$translation_id}', '{$input->user_id}', '1') ";
    DB_query($query);
}   
}
}

/**
* Checks for the awards the user has not recieved and if criteria is met assignes them
* for repetative awards the check is always done
* @return int number of awards given
*/
function awards()
{
    global $_USER, $_TABLES;

    $counter=0;

    $query = "SELECT g.gem_id FROM `gl_gems` g WHERE g.gem_id NOT IN (SELECT a.gem_id FROM `gl_awarded_gems` a WHERE a.user_id='2')";
    $possible_gems = DB_query($query);

    $query =  "SELECT COUNT(`id`) as count FROM {$_TABLES['translations']} WHERE `user_id` = {$_USER['uid']}";
    $result = DB_query($query);
    $translation_count = DB_fetchArray($result)['count'];

    if($translation_count < 0)
        return ;

    $gems = array();
    while($gem = DB_fetchArray($possible_gems) )
        array_push($gems, $gem['gem_id']);

    if(in_array(2, $gems) == false){
       array_push($gems, 2);
   }

   foreach ($gems as $index => $gem_id) {
    if( $gem_id == 2){

        while (award_nth_translation($translation_count, $gem_id)  == true){
            $counter++;
        }
        continue;
    }
    if( check_if_awarded($gem_id) == false ){
        give_award($gem_id);
        $counter++;
    }
}

return $counter;
}

/**
* @param int translation_count number of translation the user has submited
* @param int gem_id the id under which the award has been given
* @return boolean true if award is given, false otherwise
*/
function  award_nth_translation($translation_count, $gem_id)
{   

    //minimal translations here is 5
    if( $translation_count < 5)
        return false;

    global $_TABLES, $_USER;

    $query = "SELECT `award_lvl` FROM {$_TABLES['awarded_gems']} WHERE `user_id` = {$_USER['uid']} AND `gem_id` = {$gem_id}";
    $result = DB_query($query);
    if( $row = DB_fetchArray($result) )
        $award_lvl = $row['award_lvl'] + 1;
    else
        $award_lvl = 2;

    $award_mark = ( 3 * ($award_lvl*$award_lvl) - $award_lvl )/2;

    if ( $translation_count >= $award_mark ){
        give_award($gem_id, $award_lvl);
        return true;
    } else {
        return false;
    }

}

/**
* Check if award with id gem_id is given to user
* @param int gem_id the id under which the award has been given
* @return boolean true if user has award
*/
function check_if_awarded($gem_id)
{

    global $_USER, $_TABLES;

    $query = "SELECT COUNT(`gem_id`) AS count FROM {$_TABLES['awarded_gems']} WHERE `gem_id` = 1 AND `user_id` = {$_USER['uid']}";
    $result = DB_query($query);

    $count = DB_fetchArray($result)['count'];

    if($count > 0)
        return true;

    return false;
}

/**
* The award is given to the user by saving it to the awarded_gems table
* @param int gem_id the id under which the award has been given
* @param int award_lvl the level of the award
*/
function give_award($gem_id=null, $award_lvl=0)
{   

    if($gem_id == null)
        return;

    global $_USER, $_TABLES;

    if($award_lvl == 0)
        $query = "INSERT INTO {$_TABLES['awarded_gems']} (`gem_id`, `user_id`, `award_lvl`) VALUES ({$gem_id}, {$_USER['uid']}, {$award_lvl})";
    else
        $query = "UPDATE {$_TABLES['awarded_gems']} SET `award_lvl` = {$award_lvl} WHERE `gem_id` = {$gem_id}";
    
    DB_query($query);
}





?>