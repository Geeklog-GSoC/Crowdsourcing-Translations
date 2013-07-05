<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | CrowdTranslator Plugin 0.1                                                |
// +---------------------------------------------------------------------------+
// | mysql_install.php                                                         |
// |                                                                           |
// | Installation SQL                                                          |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2013 by the following authors:                              |
// |                                                                           |
// | Authors: Benjamin Talic - b DOT ttalic AT gmail DOT com                   |
// +---------------------------------------------------------------------------+
// | Created with the Geeklog Plugin Toolkit.                                  |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is licensed under the terms of the GNU General Public License|
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                      |
// | See the GNU General Public License for more details.                      |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+

$_SQL[] = "
CREATE TABLE {$_TABLES['crowdtranslator']} (
  id int  AUTO_INCREMENT NOT NULL,
    language_full_name varchar (30) NOT NULL,
    language_file varchar (30) NOT NULL,
    plugin_name varchar(50) NOT NULL,
    site_credentials varchar (50) NOT NULL,
    user_id int NOT NULL,
    timestamp datetime NOT NULL,
    approval_counts int NOT NULL,
    language_array varchar (30) NOT NULL,
    array_key varchar (20) NOT NULL,
    translation varchar (200) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['crowdtranslatororiginal']} (
  id int AUTO_INCREMENT NOT NULL,
    language varchar (30) NOT NULL,
    plugin_name varchar (50) NOT NULL,
    language_array varchar (30) NOT NULL,
    array_index varchar (20) NOT NULL,
    string varchar (200) NOT NULL,
    tags text,
  PRIMARY KEY (id,language,plugin_name,language_array,array_index)
) ENGINE=MyISAM
";

?>
