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
if ($numevents) {
    $_CATEGORIES = (Jojo::getOption('event_enable_categories', 'no') == 'yes') ? true : false ;
    $categories =  ($_CATEGORIES) ? Jojo::selectQuery("SELECT * FROM {eventcategory}") : array();
    $exclude = (Jojo::getOption('event_sidebar_exclude_current', 'no')=='yes') ? true : false;
    /* Create latest item array for sidebar: getItems(x, start, categoryid, sortby, exclude) = list x# of events */
    if ($_CATEGORIES && count($categories) && Jojo::getOption('event_sidebar_categories', 'no')=='yes') {
        $smarty->assign('allevents', Jojo_Plugin_Jojo_event::getItems($numevents, 0, 'all', '', $exclude) );
        foreach ($categories as $c) {
            $smarty->assign('events_' . str_replace('-', '_', $c['ec_url']), Jojo_Plugin_Jojo_event::getItems($numevents, 0, $c['eventcategoryid'],  $c['sortby'], $exclude) );
        }
    } else {
        if (Jojo::getOption('event_sidebar_randomise', 0) > 0) {
            $upcomingevents = Jojo_Plugin_Jojo_event::getItems(Jojo::getOption('event_sidebar_randomise', 0), 0, 'all', '', $exclude);
            shuffle($upcomingevents);
            $upcomingevents = array_slice($upcomingevents, 0, $numevents);
        } else {
             $upcomingevents = Jojo_Plugin_Jojo_event::getItems($numevents, 0, 'all', '', $exclude);
        }
        $smarty->assign('events', $upcomingevents );
    }
    /* Get the prefix for events (can vary for multiple installs) for use in the theme template instead of hard coding it */
    $smarty->assign('eventshome', Jojo_Plugin_Jojo_event::_getPrefix('event', $page->getValue('pg_language')) );
    if ($_CATEGORIES && count($categories) && Jojo::getOption('event_sidebar_categories', 'no')=='yes') {
        foreach ($categories as $c) {
            $category = $c['ec_url'];
            $categoryid = $c['eventcategoryid'];
            $smarty->assign('events_' . str_replace('-', '_', $category) . 'home', Jojo_Plugin_Jojo_event::_getPrefix('event', $page->getValue('pg_language'), $categoryid) );
        }
    }
}