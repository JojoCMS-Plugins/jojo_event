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
 * @author  Harvey Kane <code@ragepank.com>
 * @author  Michael Cochrane <code@gardyneholt.co.nz>
 * @author  Melanie Schulz <mel@gardyneholt.co.nz>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

class JOJO_Plugin_Jojo_event extends JOJO_Plugin
{

    function _getContent()
    {
        global $smarty;

        $content = array();

        $events = JOJO::selectQuery("SELECT {event}.*, date_format( from_unixtime( startdate ) , '%M %Y' ) as month FROM {event} WHERE enddate >= ? ORDER BY startdate", strtotime('TODAY'));
        if (!empty($events)) {
            foreach ($events as $i => $e){
                $events[$i]['startdateshort'] = strftime( '%d %b', $e['startdate']);
                $events[$i]['enddateshort'] = strftime( '%d %b', $e['enddate']);
                $events[$i]['title'] = htmlspecialchars($e['title'], ENT_COMPAT, 'UTF-8', false);
            }
        } else {
            $smarty->assign('noevent', Jojo::getOption('noevent_description', 'There are currently no upcoming events, please check back later.'));
        }
        $smarty->assign('events', $events);
        $smarty->assign('content', $this->page['pg_body']);
        $content['content'] = $smarty->fetch('jojo_event.tpl');
        $content['javascript'] = $smarty->fetch('jojo_event_js.tpl');

        return $content;
    }

}