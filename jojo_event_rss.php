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
 * @author  Michael Cochrane <mikec@jojocms.org>
 * @author  Melanie Schulz <mel@gardyneholt.co.nz>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 * @package jojo_event
 */

class Jojo_Plugin_Jojo_event_rss extends Jojo_Plugin
{

    function _getContent()
    {
        $url = $this->page['pg_url'];
        $categoryurl = preg_replace('%^(.*?)/rss/?$%im', '$1', $url); //strip /rss/ off the end of the URL
        $_CATEGORIES = (Jojo::getOption('event_enable_categories', 'no') == 'yes') ? true : false ;
        $categorydata =  ($_CATEGORIES) ? Jojo::selectRow("SELECT eventcategoryid FROM {eventcategory} WHERE ec_url = '$categoryurl'") : '';
        $categoryid = ($_CATEGORIES && count($categorydata)) ? $categorydata['eventcategoryid'] : '';
        //echo $url.' - '.$categoryurl.' - '.$categoryid ;exit;
        $full = (Jojo::getOption('event_full_rss_description') == 'yes') ? true : false;
        $rss  = "<?xml version=\"1.0\" ?".">\n";
        $rss .= "<rss version=\"2.0\">\n";
        $rss .= "<channel>\n";
        $rss .= "<title>" . htmlentities(_SITETITLE) . "</title>\n";
        $rss .= "<description>" . htmlentities(Jojo::getOption('sitedesc', Jojo::getOption('sitetitle'))) . "</description>\n";
        $rss .= "<link>"._SITEURL . "</link>\n";
        $rss .= "<copyright>" . htmlentities(_SITETITLE) . " " . date('Y', strtotime('now')) . "</copyright>\n";

        $limit = Jojo::getOption('event_rss_num_events');
        if (empty($limit)) $limit = 15;
        if ($_CATEGORIES && !empty($categoryid)) {
            $events = Jojo::selectQuery("SELECT * FROM {event} WHERE `enddate`>" . time() . " AND (category = '$categoryid') ORDER BY startdate ASC LIMIT $limit");
        } else {
            $events = Jojo::selectQuery("SELECT * FROM {event} WHERE `enddate`>" . time() . "ORDER BY startdate ASC LIMIT $limit");
        }
        $n = count($events);
        for ($i = 0; $i < $n; $i++) {
            $events[$i]['description'] = Jojo::relative2absolute($events[$i]['description'], _SITEURL);
            /* chop the event up to the first [[snip]] */
            if ($full) {
                $events[$i]['description'] = str_ireplace('[[snip]]','',$events[$i]['description']);
            } else {
                $arr = Jojo::iExplode('[[snip]]', $events[$i]['description']);
                if (count($arr) === 1) {
                    $events[$i]['description'] = substr($events[$i]['description'], 0, Jojo::getOption('event_rss_truncate', 800)) . ' ...';
                } else {
                    $events[$i]['description'] = $arr[0];
                }
            }
            $source = _SITEURL . "/" . Jojo_Plugin_Jojo_event::getUrl($events[$i]['eventid'], $events[$i]['title'], $events[$i]['language'], $events[$i]['category']);
            if (Jojo::getOption('event_feed_source_link') == 'yes') $events[$i]['description'] .= '<p>Source: <a href="'.$source.'">'.$events[$i]['title'].'</a></p>';
            $rss .= "<item>\n";
            $rss .= "<title>" . htmlentities($events[$i]['title'], ENT_QUOTES, 'UTF-8') . "</title>\n";
            $rss .= "<description>" . str_replace('&middot;', '', $this->rssEscape($events[$i]['description'])) . "</description>\n";
            $rss .= "<link>". $source . "</link>\n";
            $rss .= "<pubDate>" . strftime('%a, %e %B %G', (!empty($events[$i]['dateadded']) ? $events[$i]['dateadded'] : time() )) . "</pubDate>\n";
            $rss .= "</item>\n";
        }
        $rss .= "</channel>\n";
        $rss .= "</rss>\n";

        header('Content-type: application/xml');
        echo $rss;
        exit;
    }

    function getCorrectUrl()
    {
        /* Act like a file, not a folder */
        //$url = rtrim(parent::getCorrectUrl(), '/');
        $url = parent::getCorrectUrl();
        return $url;
    }

    function rssEscape($data) {
        return str_replace('<', '&lt;', str_replace('>', '&gt;', str_replace('"', '&quot;', str_replace('&', '&amp;', $data))));
    }
}