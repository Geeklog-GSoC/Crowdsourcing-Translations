<?php

/*
 * Saves the user vote or updates existing vote
 * notifies the JS if the page has to be reloaded-this happens if a translation is deleted
 */

require_once '../lib-common.php';
require_once ($_CONF['path_system'] . 'lib-database.php');

$user_id = $_USER['uid'];
$response['refresh'] = false;

//user has to be logged in to vote
if (!empty($user_id) && isset($user_id)) {

    $translation_id = $_REQUEST['translation_id'];
    $sign = $_REQUEST['sign'];

    $result = DB_query("SELECT `sign` FROM `gl_votes` WHERE `translation_id`='{$translation_id}' AND `user_id`='{$user_id}' ");
    $previous_sign = 0;

    /* if the user has not voted this translation before the votes is saved
     * othervise the vote is updated, the sign column is changed
     */
    if (DB_numRows($result) == 0) {
        $result = DB_query("INSERT INTO `gl_votes` (`translation_id`, `user_id`, `sign`) VALUES ('{$translation_id}', '{$user_id}', '{$sign}') ");
    } else {
        $previous_sign = DB_fetchArray($result)['sign'];
        $result = DB_query("UPDATE `gl_votes` SET `sign`='{$sign}'   WHERE `translation_id`='{$translation_id}' AND `user_id`='{$user_id}' ");
    }

    //the approval_counts column of the translation has to be updated
    $result = DB_query("UPDATE `gl_translations` SET `approval_counts`=`approval_counts`+{$sign}-{$previous_sign} WHERE `id`='{$translation_id}' ");

    //if the approval counts reach a certain negative value they should be deleted
    $query = ("SELECT `approval_counts` FROM `gl_translations` WHERE `id`='{$translation_id}' ");
    $result = DB_query($query);

    $approval_counts = DB_fetchArray($result)['approval_counts'];

    if ($approval_counts <= -5) {
        $response['refresh'] = true;
        $query = "DELETE FROM `gl_translations` WHERE `id`='{$translation_id}' ";
        $result = DB_query($query);
        if ($result) {
            $query = "DELETE FROM `gl_votes` WHERE `translation_id`='{$translation_id}' ";
            DB_query($query);
        }
    }


    echo json_encode($response);
}
?>