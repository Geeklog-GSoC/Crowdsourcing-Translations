<?php
require_once '../lib-common.php';
require_once ($_CONF['path_system'] . 'lib-database.php');

$function=$_REQUEST['function'];

if($function=='get_user_translations'){
	echo get_user_translations();
} elseif ($function=='delete_translation') {
	echo delete_translation();
}

function get_user_translations($limit=5, $start=-1)
{
 global $_USER, $_TABLES;

	if(isset($_REQUEST['limit']) && !empty($_REQUEST['limit']) ){
		$limit=$_REQUEST['limit'];
	}

	if(isset($_REQUEST['start']) && !empty($_REQUEST['start']) ){
		$start=$_REQUEST['start'];
	}

	

	if($start>=0){
		$query="SELECT `id`, `language_full_name`, concat(hour(TIMEDIFF(NOW(), `timestamp`)), ' hours ago') as `timestamp`, `approval_counts`,
		`translation` FROM {$_TABLES['translations']} WHERE `user_id`={$_USER['uid']} ORDER BY `timestamp` DESC LIMIT {$start}, {$limit}";
	} else {
		$query= "SELECT `id`, `language_full_name`, concat(hour(TIMEDIFF(NOW(), `timestamp`)), ' hours ago') as `timestamp`, `approval_counts`,
		`translation` FROM {$_TABLES['translations']} WHERE `user_id`={$_USER['uid']} ORDER BY `timestamp` DESC LIMIT {$limit}";
	}

	
	$display="<table class='translations_view'> <tbody> <tr> <th> Language </th> <th> Translation </th> <th> Upvotes </th> <th> Posted </th> <th> </th> </tr> ";	
	$result=DB_query($query);

	if(DB_numRows($result)>0){
		while($row=DB_fetchArray($result)){
			$class='';
			if($row['approval_counts']<0){
				$class='error';
			}
			$display .= "<tr id='translation_{$row['id']}' class='{$class}'> <td> {$row['language_full_name']} </td> <td> {$row['translation']} </td>  <td> {$row['approval_counts']} </td>  <td> {$row['timestamp']} </td>"
			." <td> <a href='javascript:void(0)' onclick=\"delete_translation({$row['id']}, '{$row['translation']}' )\"> delete </a> </td> </tr>";
		}
		$next= $start+5%5==0 ? $start+5 : $start+6;

		$previous=$start-5;
		$display .= "<tr> <td> </td> <td> </td> <td> </td> ";
		if($previous>=0){
			$display.="<td>  <a href='javascript:void(0)' onclick='show_more_translations({$limit}, {$previous})'> <- Show previous </a> </td>";
		} else {
			$display .= "<td></td>";
		}
		if($next<get_translated_by_user_count()){
			$display .=" <td> <a href='javascript:void(0)' onclick='show_more_translations({$limit}, {$next})'> Show next -> </a> </td> </tr>";
		} else {
			$display .= "<td></td>";
		}
	} else {
		$display .= "<tr> You have not submited any translations yet </tr>";
	}

	$display .= "</tbody></table>";
	return $display;
}

function delete_translation($id=null)
{

	 global  $_TABLES;

	if(isset($_REQUEST['id']) && !empty($_REQUEST['id']) ){
		$id=$_REQUEST['id'];
	}

	if($id!=null){
		$query="DELETE FROM {$_TABLES['translations']} WHERE `id`={$id}";
		$result=DB_query($query);
		if($result){
			$query="DELETE FROM {$_TABLES['votes']} WHERE `translation_id` = {$id} ";
			$result= DB_query($query);
			return true;
		} else {
			return false;
		}
	}
}

function get_translated_by_user_count()
{
	global $_USER, $_TABLES;

	$query="SELECT COUNT(`id`) as count FROM {$_TABLES['translations']} WHERE `user_id` = {$_USER['uid']}";
	$result=DB_query($query);

	return DB_fetchArray($result)['count'];
}

function get_total_approval_for_user(){
	global $_USER, $_TABLES;

	$query="SELECT SUM(`approval_counts`) as sum FROM {$_TABLES['translations']} WHERE `user_id` = {$_USER['uid']}";
	$result=DB_query($query);
	$sum=DB_fetchArray($result)['sum'];
	return  $sum > 0 ? $sum : "0";

}

function get_user_translated_languages($user_id=null)
{

	global $_TABLES;

	$query="SELECT DISTINCT `language_full_name` as language FROM {$_TABLES['translations']} WHERE `user_id` = {$user_id}";
	$result = DB_query($query);

	$display = '';

	while($row=DB_fetchArray($result)){
		$translated=get_translation_percent($row['language']);
		$translated=round($translated, 2);
		$not_translated=100-$translated;

		$query="SELECT COUNT(`id`) as count FROM {$_TABLES['translations']} WHERE `user_id` = {$user_id} AND `language_full_name`='{$row['language']}'";
		$result2=DB_query($query);
		$count=DB_fetchArray($result2)['count'];

		$display .= "<div class='index_language_graph'> <h3> {$row['language']} </h3>
		<div class='progress_bar'> <span class='translated' style='width: {$translated}%'> {$translated}% </span> "
		."<span class='not_translated' style='width: {$not_translated}%'> {$not_translated}% </span> </div> </div>
		<span> Translated by you: {$count} </span>";
	}

	return $display;

}

function get_most_user_upvotes()
{
	global $_USER, $_TABLES;

	$query="SELECT MAX(`approval_counts`) as max FROM {$_TABLES['translations']} WHERE `user_id` = {$_USER['uid']}";
	$result=DB_query($query);
	$sum=DB_fetchArray($result)['max'];
	return  $sum > 0 ? $sum : "0";
}


function get_translation_percent($language=null)
{

	global $_TABLES;

	if($language==null){
		$language= $_COOKIE['selected_language'];
	}

	$result=DB_query("SELECT COUNT(`id`) as count FROM {$_TABLES['originals']} ");
	$number_of_original_elements=DB_fetchArray($result)['count'];

	$result=DB_query("SELECT COUNT(DISTINCT `language_array`,`array_key`) as count FROM {$_TABLES['translations']} WHERE `language_full_name`='{$language}'");

	$number_of_translated_elements=DB_fetchArray($result)['count'];
	$translated=($number_of_translated_elements/$number_of_original_elements)*100;

	return (float)$translated;
}


function get_user_badges($limit=-1)
{
	global $_USER, $_TABLES;
	$display ='';
	$base_url="./images/badges/";

	if($limit>0)
		$query="SELECT `gem_id` FROM {$_TABLES['awarded_gems']} WHERE `user_id` = {$_USER['uid']} LIMIT {$limit}";
	else
		$query="SELECT `gem_id` FROM {$_TABLES['awarded_gems']} WHERE `user_id` = {$_USER['uid']}";
	
	$result=DB_query($query);
	$count=0;
	if(DB_numRows($result)>0){
		while( $row=DB_fetchArray($result) ){

			$query="SELECT  `title`, `tooltip`, `image` FROM {$_TABLES['gems']} WHERE `gem_id` = {$row['gem_id']} ";
			$gems=DB_query($query);
			$gem=DB_fetchArray($gems);
			$display .= "<div class='achievement' title='{$gem['tooltip']}' >"
			."<div class='badge' > <img src='{$base_url}{$gem['image']}' /></div>"
			."<p class='achievement_name'>{$gem['title']}</p></div>";
			if(++$count%4==0)
				$display .= "</br>";
		}

		
	} else {
		$display = "You don't have any badges... :( Start translating!";
	}

	return $display;
}


function get_user_votes()
{
	global $_USER, $_TABLES ;

	$query="SELECT COUNT(`user_id`) as count FROM {$_TABLES['votes']} WHERE `user_id` = {$_USER['uid']}";
	$result=DB_query($query);

	return DB_fetchArray($result)['count'];
}

function get_translated_count()
{

	global $_TABLES;

	$query="SELECT COUNT(`id`) as count FROM {$_TABLES['translations']} WHERE 1";
	$result=DB_query($query);

	return DB_fetchArray($result)['count'];
}

function get_votes_count()
{
	global $_TABLES;
	$query="SELECT COUNT(`user_id`) as count FROM {$_TABLES['votes']} WHERE 1";
	$result=DB_query($query);

	return DB_fetchArray($result)['count'];
}

function get_most_upvotes()
{
	global $_TABLES;
	$query="SELECT MAX(`approval_counts`) as max FROM {$_TABLES['translations']} WHERE 1";
	$result=DB_query($query);

	return DB_fetchArray($result)['max'];
}

function get_users_translating()
{
	global $_TABLES;

	$query="SELECT COUNT( DISTINCT (`user_id`) ) as count FROM {$_TABLES['translations']} WHERE 1";
	$result=DB_query($query);

	return DB_fetchArray($result)['count'];
}


function get_top_user_by_submisions()
{

	global $_TABLES;

	$query="SELECT DISTINCT `user_id`, COUNT(`user_id`) as count FROM {$_TABLES['translations']} GROUP BY `user_id` ORDER BY `count` DESC";
	$result = DB_query($query);

	$display = '';

	$count=1;
	while($row=DB_fetchArray($result)){
		$result2=DB_query("SELECT `username` FROM {$_TABLES['users']} WHERE `uid`={$row['user_id']}");
		$name=DB_fetchArray($result2)['username'];
		$display .= "<tr> <td>{$count}.</td> <td> $name </td> <td> {$row['count']} </td> </tr>";
		
		$count++;
		if($count>3)
			break;
	}

	return $display;
}


?>