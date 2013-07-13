<?php

/**
 * 	Will return the numerical percentage of translation for the current language
 */
require_once '../lib-common.php';
require_once ($_CONF['path_system'] . 'lib-database.php');

$language = $_COOKIE['selected_language'];

$result = DB_query("SELECT COUNT(`id`) as count FROM `gl_originals` ");
$number_of_original_elements = DB_fetchArray($result)['count'];

$result = DB_query("SELECT COUNT(DISTINCT `language_array`,`array_key`) as count FROM `gl_translations` WHERE `language_full_name`='{$language}'");

$number_of_translated_elements = DB_fetchArray($result)['count'];
$translated = ($number_of_translated_elements / $number_of_original_elements) * 100;
?>