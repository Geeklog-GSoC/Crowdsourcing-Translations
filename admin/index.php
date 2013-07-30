<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | CrowdTranslator Plugin 0.1                                                |
// +---------------------------------------------------------------------------+
// | index.php                                                                 |
// |                                                                           |
// | Plugin administration page                                                |
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

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once '../../../crowdtranslator/lib-translator.php';



$display = '';

// Ensure user even has the rights to access this page
if (! SEC_hasRights('crowdtranslator.admin'))
{
	$display .= COM_siteHeader('menu', $MESSAGE[30])
	. COM_showMessageText($MESSAGE[29], $MESSAGE[30])
	. COM_siteFooter();

    // Log attempt to access.log
	COM_accessLog("User {$_USER['username']} tried to illegally access the CrowdTranslator plugin administration screen.");

	echo $display;
	exit;
}


// MAIN
$display .= COM_siteHeader('menu', $LANG_CROWDTRANSLATOR_1['plugin_name']);
$display .= COM_startBlock($LANG_CROWDTRANSLATOR_1['plugin_name']);
$display .= "<div class='index'>";

$display .= "<div class='translator'>";


	//stats
$display .= COM_startBlock("Quick Stats");
$display .="<table><tbody>";
$display .= "<tr><td>Translations submited: </td> <td>" . get_translated_count(1) . "</td> <td>  </tr>";
$display .= "<tr><td>Total votes: </td> <td>" . get_votes_count() . "</td>   </tr>";
$display .= "<tr><td>Most upvotes:  </td> <td> ". get_most_upvotes(1) ." </td></tr>";
$display .= "<tr><td>Users translating: </td> <td> ". get_users_translating() ." </td> </tr>";
$display .= "<tr><td>Languages being translated: </td> <td> ". get_languages_translated_count() ." </td> </tr>";
$display .= "<tr class='error'><td>Translations with negative vote count: </td> <td> ". get_translations_with_negative_vote_count() ." </td> </tr>";
$display .="</table></tbody>";


$display .= "<div id='user_languages_translated' style='clear:both;'>";
$display .= get_translated_languages();
$display .= "</div>";
$display .= COM_endBlock();

$display .= COM_startBlock("Available badges <a  id='badges_show' href='javascript:void(0)' onclick='get_all_badges(1)'>(show all)</a>");
$display .="<div id='badges_display'>";
$display .= get_user_badges(4, 1);
$display .="</div>";
$display .= COM_endBlock();



	//translations table
$translations=get_translations_table(5);
$display .= COM_startBlock("Submited Translations");
$display .=" <div id='user_translations'> {$translations} </div>";
$display .= COM_endBlock();

//blocked users
$display .= COM_startBlock("Blocked Users");
$display .= get_blocked_users_table();
$display .= COM_endBlock();

$display .= COM_siteFooter();


echo $display;

?>
