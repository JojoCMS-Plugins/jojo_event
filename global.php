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


/* Create events array for sidebar*/
        $numevents = Jojo::getOption('upcomingevents_num', 5);
        $upcomingevents = JOJO::selectQuery("SELECT {event}.*, date_format( from_unixtime( startdate ) , '%M %Y' ) as month FROM {event} WHERE ( enddate >= ? ) ORDER BY startdate LIMIT $numevents", strtotime('TODAY'));
        foreach ($upcomingevents as $i => $e){
            $upcomingevents[$i]['startdate'] = strftime( '%d %b', $e['startdate']);
            $upcomingevents[$i]['enddate'] = strftime( '%d %b', $e['enddate']);
            $upcomingevents[$i]['description'] = strip_tags($e['description']);
            $upcomingevents[$i]['title'] = htmlspecialchars($e['title'], ENT_COMPAT, 'UTF-8', false);
        }
        $smarty->assign('upcomingevents', $upcomingevents);

