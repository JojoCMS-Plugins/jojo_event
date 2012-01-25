<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2007 Harvey Kane <code@ragepank.com>
 * Copyright 2007 Michael Holt <code@gardyneholt.co.nz>
 * Copyright 2007 Melanie Schulz <mel@gardyneholt.co.nz>
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Michael Cochrane <code@gardyneholt.co.nz>
 * @author  Tom Dale <tom@gardyneholt.co.nz>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

$numevents = Jojo::getOption('event_num_sidebar_events', 3);
$get = $numevents +20;
if ($numevents) {
    $exclude = (Jojo::getOption('event_sidebar_exclude_current', 'no')=='yes') ? true : false;
    /* Create latest item array for sidebar: getItems(x, start, categoryid, sortby, exclude) = list x# of events */
    if (Jojo::getOption('event_sidebar_categories', 'no')=='yes') {
        $events =  Jojo_Plugin_Jojo_event::getItems($get, 0, 'all', '', $exclude);
        $events = array_slice($events, 0, $numevents);
        $smarty->assign('allevents', $events);
        $categories =  Jojo::selectQuery("SELECT * FROM {eventcategory}");
        foreach ($categories as $c) {
            $catevents = Jojo_Plugin_Jojo_event::getItems($get, 0, $c['eventcategoryid'],  $c['sortby'], $exclude);
            if ($catevents) {
                $catevents = array_slice($catevents, 0, $numevents);
                $smarty->assign('events_' . str_replace('-', '_', $catevents[0]['pg_url']),  $catevents);
            }
        }
    } else {
        if (Jojo::getOption('event_sidebar_randomise', 0) > 0) {
            $upcomingevents = Jojo_Plugin_Jojo_event::getItems($get, 0, 'all', 'startdate asc', $exclude);
            shuffle($upcomingevents);
            $upcomingevents = array_slice($upcomingevents, 0, $numevents);
        } else {
             $upcomingevents = Jojo_Plugin_Jojo_event::getItems($get, 0, 'all', 'startdate asc', $exclude);
            $upcomingevents = array_slice($upcomingevents, 0, $numevents);
        }
        $smarty->assign('events', $upcomingevents );
    }
}