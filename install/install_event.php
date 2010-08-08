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

$updatefield = (boolean)(Jojo::tableExists('event') && !Jojo::fieldExists('event', 'website'));

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
  `snippet` text NOT NULL,
  `description` text NOT NULL,
  `description_code` text NULL,
  `website` varchar(255) default NULL,
  `event_image` varchar(255) NOT NULL,
  `url` varchar(255) default NULL,
  `category` int(11) default NULL,  
  `language` varchar(100) NOT NULL default 'en',
  `seotitle` varchar(255) NOT NULL default '',
  `dateadded` int(11) default '0',
  `tags` varchar(10) default NULL,
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

if ($updatefield) {
 $events = Jojo::selectQuery("SELECT eventid, url FROM {event}");
     foreach ($events as $e) {
        Jojo::updateQuery("UPDATE {event} SET website = ?, url='' WHERE eventid = ?", array($e['url'], $e['eventid']));
     }
     Jojo::structureQuery("ALTER TABLE  {eventcategory} CHANGE  `ec_pageid`  `pageid` INT( 11 ) NOT NULL DEFAULT  '0'");
}

$table = 'eventcategory';
$query = "
    CREATE TABLE {eventcategory} (
      `eventcategoryid` int(11) NOT NULL auto_increment,
      `ec_url` varchar(255) NOT NULL default '',
      `sortby` enum('title asc','startdate asc','enddate asc') NOT NULL default 'startdate asc',
      `pageid` int(11) NOT NULL default '0',
      `rsslink` tinyint(1) default '1',
      `thumbnail` varchar(255) NOT NULL default '',
      PRIMARY KEY  (`eventcategoryid`),
      KEY `id` (`pageid`)
    ) TYPE=MyISAM ;";

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

if (isset($result['different'])) Jojo::printTableDifference($table, $result['different']);