<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | CrowdTranslator Plugin 0.1                                                |
// +---------------------------------------------------------------------------+
// | mssql_install.php                                                         |
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
CREATE TABLE [dbo].[{$_TABLES['translations']}]  (
    [id] [int]  AUTO_INCREMENT NOT NULL,
    [[language]_full_name] [varchar] (30) NOT NULL,
    [[language]_file] [varchar] (30) NOT NULL,
    [plugin_name] [varchar](50) NOT NULL,
    [site_credentials] [varchar] (50) NOT NULL,
    [user_id] [int] NOT NULL,
    [timestamp] [datetime] NOT NULL,
    [approval_counts] [int] NOT NULL,
    [language_array] [varchar] (30) NOT NULL,
    [array_key] [varchar] (20) NOT NULL,
    [array_subindex] [varchar] (20) NOT NULL,
    [translation] [varchar] (200) NOT NULL,
  PRIMARY KEY ([id] [[language]_full_name]  [[language]_array] [array_key] [array_subindex] [site_credentials] )
) 
";

$_SQL[] = "
CREATE TABLE  [dbo].[{$_TABLES['originals']}] (
    [id] [int] AUTO_INCREMENT NOT NULL,
    [language] [varchar] (30) NOT NULL,
    [plugin_name] [varchar] (50) NOT NULL,
    [language_array] [varchar] (30) NOT NULL,
    [array_index] [varchar] (20) NOT NULL,
    [sub_index] [varchar] (20) NOT NULL,
    [string] [varchar] (200) NOT NULL,
    [tags] [text],
  PRIMARY KEY ([id] [language] [plugin_name] [language_array] [array_index] [sub_index])
) 
";

$_SQL[] = "
CREATE TABLE  [dbo].[{$_TABLES['votes']}] (
 [translation]_id [int]  NOT NULL,
 [user_id] [int] NOT NULL,
 [sign] [int] NOT NULL,
  PRIMARY KEY ([translation_id] [user_id])
) 
";

$_SQL[] = "
CREATE TABLE [dbo].[{$_TABLES['gems']}] (
 [gem_id] [int]  NOT NULL,
 [title] [text] NOT NULL,
 [tooltip] [text] NOT NULL,
 [image] [varchar] (50) NOT NULL,
  PRIMARY KEY ([gem_id])
) 
";

$_SQL[] = "
CREATE TABLE [dbo].[{$_TABLES['awarded_gems']}] (
 [gem_id] [int]  NOT NULL,
 [user_id] [int] NOT NULL,
 [award_lvl] [int] NOT NULL,

  PRIMARY KEY ([gem_id] [user_id])
) 
";

$_SQL[] = "
CREATE TABLE [dbo].[{$_TABLES['[language]_map']}] (
 [page_url] [varchar] (255)  NOT NULL,
 [reference] [text] NOT NULL,
 [includes] [text] NOT NULL
) 
";

$_SQL[] = "
CREATE TABLE [dbo].[{$_TABLES['blocked_users']}] (
 [user_id] [int]  NOT NULL,
 [timestamp] [datetime] NOT NULL,
  PRIMARY KEY ([user_id])
) 
";

$_SQL[] = "
[dbo].[{$_TABLES['remote_credentials']}] (
 [site_name] [varchar](50)  NOT NULL,
 [password] [varchar] (255) NOT NULL,
 [salt] [varchar] (255) NOT NULL,
  PRIMARY KEY (site_name)
) 
";


$DEFVALUES[] = "INSERT INTO {$_TABLES['gems']} (`gem_id`, `title`, `tooltip`, `image`) VALUES ('1', 'First Translation', 'Submited first [translation]!', 'badge1.png')";
$DEFVALUES[] = "INSERT INTO {$_TABLES['gems']} (`gem_id`, `title`, `tooltip`, `image`) VALUES ('2', 'Continuous Contribution', 'Adding [translation]s and leveling up', 'badge2.png')";
$DEFVALUES[] = "INSERT INTO {$_TABLES['gems']} (`gem_id`, `title`, `tooltip`, `image`) VALUES ('3', 'Judgement day', 'Casted first vote!', 'badge3.png')";
$DEFVALUES[] = "INSERT INTO {$_TABLES['gems']} (`gem_id`, `title`, `tooltip`, `image`) VALUES ('4', 'Quality assurance', 'Voting up, voting down, cleaning the database!', 'badge4.png')";

?>

