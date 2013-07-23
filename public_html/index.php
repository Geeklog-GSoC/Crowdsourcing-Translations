<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | CrowdTranslator Plugin 0.1                                                |
// +---------------------------------------------------------------------------+
// | index.php                                                                 |
// |                                                                           |
// | Public plugin page                                                        |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2013 by the following authors:                              |
// |                                                                           |
// | Authors: Benjamin Talic - b DOT ttalic AT gmail DOT com                   |
// +---------------------------------------------------------------------------+
// | Created with the Geeklog Plugin Toolkit.                                  |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+

/**
* @package CrowdTranslator
*/

require_once '../lib-common.php';
require_once './lib-translator.php';

// take user back to the homepage if the plugin is not active
if (! in_array('crowdtranslator', $_PLUGINS)) {
    echo COM_refresh($_CONF['site_url'] . '/index.php');
    exit;
}

$display = '';


// MAIN
$display .= COM_siteHeader('menu', $LANG_crowdtranslator_1['plugin_name']);
$display .= COM_startBlock("<h4><a href='http://wiki.geeklog.net/index.php?title=Crowdsourcing_Translations'>".$LANG_crowdtranslator_1['plugin_name']."</a></h4>");

$display .= "<div class='index'>";

if ( COM_isAnonUser() ){
    $display .= get_info_text($LANG_crowdtranslator_1['plugin_name']);

    $display .= "<h4> To start translating you have to Login </h4>";

} else {
    $display .= logedin_user_display();
}

$display.='</div>';

$display .= COM_endBlock();
$display .= COM_siteFooter();

echo $display;

function get_info_text($plugin_name)
{

    return " ...is a plugin that allows \"crowdsourcing\" the translation of Geeklog, i.e. once installed, it allows users to contribute translations of Geeklog's user interface texts for other languages.
    This is a being developed by <a href='http://www.linkedin.com/profile/view?id=188717601' >Benjamin Talic </a> 
    under the mentorship of <a href='http://www.linkedin.com/profile/view?id=11473251'> Dirk Haun </a>
    as a project during the Google <a href='https://www.google-melange.com/gsoc/homepage/google/gsoc2013' >Summer of Code 2013</a>.";
}


function logedin_user_display()
{

    global $LANG_crowdtranslator_1, $_USER;

    $display = "<div class='translator'>";

    //plugin info
    $display .=  COM_startBlock("Plugin info  <a  id='info' href='javascript:void(0)' onclick='show(this.id)'> (show)  </a>");
    $display .=  "<div id='info_content' class='hidden'>" . get_info_text($LANG_crowdtranslator_1['plugin_name']) . "</div>";
    $display .= COM_endBlock();

    //stats
    $display .= COM_startBlock("Quick Stats");
    $display .="<table><tbody>";
    $display .= "<tr><td>Translated by you: </td> <td>" . get_translated_count(0) . "</td> <td>Translations submited: </td> <td>" . get_translated_count(1). "</td> <td>  </tr>";
    $display .= "<tr><td>Total approvals: </td> <td>" . get_total_approval_for_user() .    "</td> <td>Total votes: </td> <td>" . get_votes_count() . "</td>   </tr>";
    $display .= "<tr><td>Most upvotes for you:  </td> <td> ". get_most_upvotes(0) ."    </td> <td>Most upvotes:  </td> <td> ". get_most_upvotes(1) ." </td></tr>";
    $display .= "<tr><td>You voted:  </td> <td> ". get_user_votes() ." </td>                </td>  <td>Users translating: </td> <td> ". get_users_translating() ." </td> </tr>";
    $display .="</table></tbody>";


    $display .= "<div id='user_languages_translated' style='clear:both;'>";
    $display .= get_user_translated_languages($_USER['uid']);
    $display .= "</div>";
    $display .= COM_endBlock();

    $display .= COM_startBlock("My Badges <a  id='info' href='javascript:void(0)' onclick='show(this.id)'> (show all)  </a>");
    $display .= get_user_badges(4);
    $display .= COM_endBlock();



    //translations table
    $translations=get_user_translations_table(5);
    $display .= COM_startBlock("Your translations");
    $display .=" <div id='user_translations'> {$translations} </div>";
    $display .= COM_endBlock();

    return $display;

}


?>