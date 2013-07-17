<?php

/* the script will return all available languages for translation
*it will make a list of both languages from the language folder
and previously user created languages
*/ 

require_once '/../../public_html/lib-common.php';
require_once ($_CONF['path_system'] . 'lib-database.php');


//get all the languages shiped with geeklog
$lang=MBYTE_languageList ($LANG_CHARSET);

//get languages previously added by users
$result = DB_query ("SELECT DISTINCT language_full_name, language_file FROM {$_TABLES['translations']}" );

//merge previously created languages with languages from database
while ($language=DB_fetchArray ($result)){
	if( !array_key_exists($language['language_file'], $language) )
		$lang[$language['language_file']]=$language['language_full_name'];
}

//return to javascript via JSON
echo json_encode($lang);


?>