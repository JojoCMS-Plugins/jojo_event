<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2007-2008 Harvey Kane <code@ragepank.com>
 * Copyright 2007-2008 Michael Holt <code@gardyneholt.co.nz>
 * Copyright 2007 Melanie Schulz <mel@gardyneholt.co.nz>
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Harvey Kane <code@ragepank.com>
 * @author  Michael Cochrane <code@gardyneholt.co.nz>
 * @author  Melanie Schulz <mel@gardyneholt.co.nz>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

$table = 'event';
$query = "
        CREATE TABLE {event} (
  `eventid` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `startdate` bigint(20) NOT NULL,
  `enddate` bigint(20) default NULL,
  `starttime` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `locationaddress` varchar(255) NOT NULL,
  `locationurl` varchar(255) NOT NULL,
  `locationmaplink` varchar(255) NOT NULL,
  `contactemail` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `description_code` text NULL,
  `event_image` varchar(255) NOT NULL,
  `url` varchar(255) default NULL,
  PRIMARY KEY  (`eventid`),
  KEY `date` (`startdate`),
      FULLTEXT KEY `title` (`title`),
      FULLTEXT KEY `body` (`title`, `description`, `location`)
         );";

/* Check table structure */
$result = Jojo::checkTable($table, $query);

/* Output result */
if (isset($result['created'])) {
    echo sprintf("jojo_event: Table <b>%s</b> Does not exist - created empty table.<br />", $table);
}

if (isset($result['added'])) {
    foreach ($result['added'] as $col => $v) {
        echo sprintf("jojo_event: Table <b>%s</b> column <b>%s</b> Does not exist - added.<br />", $table, $col);
    }
}

if (isset($result['different'])) Jojo::printTableDifference($table,$result['different']);


/* For legacy text - make a copy of all HTML content into the article bbbody field, adding the editor tag to mark it as html */
$bbhead = "[editor:html]\n";
$num = Jojo::updateQuery("UPDATE {event} SET description_code=CONCAT(?, '<p>', description, '</p>') WHERE description_code IS NULL AND description!=''", array($bbhead));
if ($num) echo "Copy event content to texteditor field - $num $table records affected.";
