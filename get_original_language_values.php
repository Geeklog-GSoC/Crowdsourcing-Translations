<?php

/*
* Script will take extracted array data find the original array values from the database
* where all variables and html tags have been replaced with <tag> and return it to javascript
* javascript will use it to create the translation <form> and assemble the string again
* before it is saved to the database
*/


global $_TABLES, $_CONF;
require_once '/../../public_html/lib-common.php';
require_once ($_CONF['path_system'] . 'lib-database.php');
require_once "./language_markup.php";



$myArray = json_decode($_REQUEST['objects']);

$req_array=array();

foreach ($myArray as $key => $value) {
	array_push($req_array, $value);
}

foreach ($req_array as $key => $value) {
	$result=DB_query("SELECT  `string`, `tags` FROM `gl_crowdtranslator_original` WHERE `language_array`='{$value->array_name}' AND `array_index`='{$value->array_index}' ");
	
	while($row=DB_fetchArray ($result) ){
		$value->string=$row['string'];
		$value->tags=$row['tags'];
	}
}

echo json_encode($req_array);
?>