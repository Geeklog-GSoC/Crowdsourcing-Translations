<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | crowdtranslator Plugin 0.1                                                |
// +---------------------------------------------------------------------------+
// | english.php                                                               |
// |                                                                           |
// | English language file                                                     |
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
* @package crowdtranslator
*/

/**
* Import Geeklog plugin messages for reuse
*
* @global array $LANG32
*/
global $LANG32;

// +---------------------------------------------------------------------------+
// | Array Format:                                                             |
// | $LANGXX[YY]:  $LANG - variable name                                       |
// |               XX    - specific array name                                 |
// |               YY    - phrase id or number                                 |
// +---------------------------------------------------------------------------+

$LANG_crowdtranslator_1 = array(
    'plugin_name' => 'Crowd Translator',
    'hello' => 'Hello, world!' // this is an example only - feel free to remove
);

// Messages for the plugin upgrade
$PLG_crowdtranslator_MESSAGE3002 = $LANG32[9]; // "requires a newer version of Geeklog"

// Localization of the Admin Configuration UI
$LANG_configsections['crowdtranslator'] = array(
    'label' => 'crowdtranslator',
    'title' => 'crowdtranslator Configuration'
);

$LANG_confignames['crowdtranslator'] = array(
    'samplesetting1' => 'Sample Setting #1',
    'samplesetting2' => 'Sample Setting #2',
);

$LANG_configsubgroups['crowdtranslator'] = array(
    'sg_main' => 'Main Settings'
);

$LANG_tab['crowdtranslator'] = array(
    'tab_main' => 'crowdtranslator Main Settings'
);

$LANG_fs['crowdtranslator'] = array(
    'fs_main' => 'crowdtranslator Main Settings'
);

$LANG_configselects['crowdtranslator'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => true, 'False' => false)
);
?>
