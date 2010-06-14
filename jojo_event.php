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

    /* Gets $num items sorted by startdate (asc) for use on homepages and sidebars */
    static function getItems($num, $start = 0, $categoryid='all', $sortby=false, $exclude=false, $usemultilanguage=true) {
        global $page;
        if (_MULTILANGUAGE) $language = !empty($page->page['pg_language']) ? $page->page['pg_language'] : Jojo::getOption('multilanguage-default', 'en');
        $_CATEGORIES      = (Jojo::getOption('event_enable_categories', 'no') == 'yes') ? true : false;
        /* if calling page is an event, Get current id, and exclude from the list  */
        $excludethisid = ($exclude && Jojo::getOption('event_sidebar_exclude_current', 'no')=='yes' && $page->page['pg_link']=='jojo_plugin_jojo_event' && Jojo::getFormData('id')) ? Jojo::getFormData('id') : '';

        $now    = strtotime('TODAY');
        $query  = "SELECT ev.*, date_format( from_unixtime( ev.startdate ) , '%M %Y' ) as month ";
        $query .= $_CATEGORIES ? ", c.ec_url, p.pg_menutitle, p.pg_title" : '';
        $query .= " FROM {event} ev";
        $query .= $_CATEGORIES ? " LEFT JOIN {eventcategory} c ON (ev.category=c.eventcategoryid) LEFT JOIN {page} p ON (c.ec_url=p.pg_url)" : '';
        $query .= " WHERE enddate>$now";
        $query .= (_MULTILANGUAGE && $usemultilanguage) ? " AND (language = '$language')" : '';
        $query .= ($_CATEGORIES && _MULTILANGUAGE && $usemultilanguage) ? " AND (pg_language = '$language')" : '';
        $query .= ($_CATEGORIES && $categoryid && $categoryid!='all') ? " AND (category = '$categoryid')" : '';
        $query .= $excludethisid ? " AND (eventid != '$excludethisid')" : '';
        $query .= " ORDER BY " . ($sortby ? $sortby : "startdate ASC");
        $query .= $num ? " LIMIT $start,$num" : '';
        $events = Jojo::selectQuery($query);
        foreach ($events as &$a){
            $a['id']           = $a['eventid'];
            $a['title']        = htmlspecialchars($a['title'], ENT_COMPAT, 'UTF-8', false);
            $a['seotitle']        = isset($a['seotitle']) ? htmlspecialchars($a['seotitle'], ENT_COMPAT, 'UTF-8', false): $a['title'];
            $a['cleandescription']    = strip_tags($a['description']);
            $a['itemurl']          = self::getUrl($a['eventid'], $a['title'], $a['language'], ($_CATEGORIES ? $a['category'] : '') );
            $a['category']     = ($_CATEGORIES && !empty($a['pg_menutitle'])) ? $a['pg_menutitle'] : $a['pg_title'];
            $a['categoryurl']  = ($_CATEGORIES && !empty($a['ec_url'])) ? (_MULTILANGUAGE ? Jojo::getMultiLanguageString ($language, true) : '') . $a['ec_url'] . '/' : '';
            $a['fstartdate'] = strftime( Jojo::getOption('upcomingevents_dateformat', '%d %b'), $a['startdate']);
            $a['fenddate'] = strftime( Jojo::getOption('upcomingevents_dateformat', '%d %b'), $a['enddate']);
        }
        return $events;
    }

    static function getUrl($id=false, $title=false, $language=false, $categoryid=false)
    {
        if (_MULTILANGUAGE) {
            $language = !empty($language) ? $language : Jojo::getOption('multilanguage-default', 'en');
            $mldata = Jojo::getMultiLanguageData();
            $lclanguage = $mldata['longcodes'][$language];
        }

        /* ID + title specified */
        if ($id && !empty($title)) {
            $fullurl = (_MULTILANGUAGE ? Jojo::getMultiLanguageString($language, false) : '') . self::_getPrefix('event', $language, $categoryid) . '/' . $id . '/' .  Jojo::cleanURL($title) . '/';
            return $fullurl;
        }
        /* use the ID to find either the URL or title */
        if ($id) {
            $item = Jojo::selectRow("SELECT title, language, category FROM {event} WHERE eventid = ?", $id);
            if (count($item)) {
                return self::getUrl($id, $item['title'], $item['language'], $item['category']);
            }
         }
        /* No event matching the ID supplied or no ID supplied */
        return false;
    }

    function _getContent()
    {
        global $smarty;
        $content = array();
        $language = !empty($this->page['pg_language']) ? $this->page['pg_language'] : Jojo::getOption('multilanguage-default', 'en');
        $mldata = Jojo::getMultiLanguageData();
        $lclanguage = $mldata['longcodes'][$language];

        /* Are we looking at an item or the index? */
        $id = Jojo::getFormData('id',        0);
        $category  = Jojo::getFormData('category', '');
        $findby = ($category) ? $category : $this->page['pg_url'];
        /* Get category url and id if needed */
        $pg_url = $this->page['pg_url'];
        $_CATEGORIES = (Jojo::getOption('event_enable_categories', 'no') == 'yes') ? true : false ;
        $categorydata =  ($_CATEGORIES) ? Jojo::selectRow("SELECT * FROM {eventcategory} WHERE ec_url = ?", $findby) : '';
        $categoryid = ($_CATEGORIES && count($categorydata)) ? $categorydata['eventcategoryid'] : 0;
        $sortby = ($_CATEGORIES && count($categorydata)) ? $categorydata['sortby'] : '';

        $events = self::getItems('', '', $categoryid, $sortby);
        if ($id) {

            /* find the current, next and previous profiles */
            $event = '';
            $prevevent = array();
            $nextevent = array();
            $next = false;
            foreach ($events as $a) {
                if ($id==$a['eventid']) {
                    $event = $a;
                    $next = true;
                } elseif ($next==true) {
                    $nextevent = $a;
                     break;
                } else {
                    $prevevent = $a;
                }
            }

            /* If the event can't be found, return a 404 */
            if (!$event) {
                include(_BASEPLUGINDIR . '/jojo_core/404.php');
                exit;
            }

            /* Get the specific event */
            $event['fullurl'] = self::getUrl($id, $event['title'], $event['language'], $event['category']);

            /* calculate the next and previous events */
            if (Jojo::getOption('event_next_prev') == 'yes') {
                if (!empty($nextevent)) {
                    $nextevent['url'] = self::getUrl($nextevent['eventid'], $nextevent['title'], $nextevent['language'], $nextevent['category']);
                    $smarty->assign('nextevent', $nextevent);
                }

                if (!empty($prevevent)) {
                    $prevevent['url'] = self::getUrl($prevevent['eventid'], $prevevent['title'], $prevevent['language'], $prevevent['category']);
                    $smarty->assign('prevevent', $prevevent);
                }
            }

            /* Ensure the tags class is available */
            if (class_exists('Jojo_Plugin_Jojo_Tags')) {
                /* Split up tags for display */
                $tags = Jojo_Plugin_Jojo_Tags::getTags('jojo_event', $eventid);
                $smarty->assign('tags', $tags);

                /* generate tag cloud of tags belonging to this event */
                $event_tag_cloud_minimum = Jojo::getOption('event_tag_cloud_minimum');
                if (!empty($event_tag_cloud_minimum) && ($event_tag_cloud_minimum < count($tags))) {
                    $itemcloud = Jojo_Plugin_Jojo_Tags::getTagCloud('', $tags);
                    $smarty->assign('itemcloud', $itemcloud);
                }
            }

            /* Calculate whether the event has expired or not */
            $now = strtotime('today');
            if ($now > $event['enddate'] && $event['enddate'] > 0) {
                $this->expired = true;
            }

            /* Add breadcrumb */
            $breadcrumbs                      = $this->_getBreadCrumbs();
            $breadcrumb                       = array();
            $breadcrumb['name']               = $event['title'];
            $breadcrumb['rollover']           = $event['snippet'];
            $breadcrumb['url']                = $event['fullurl'];
            $breadcrumbs[count($breadcrumbs)] = $breadcrumb;

            /* Assign event content to Smarty */
            $smarty->assign('event', $event);
 
            /* get related items if tags plugin installed and option enabled */
            $numrelated = Jojo::getOption('event_num_related');
            if ($numrelated && class_exists('Jojo_Plugin_Jojo_Tags')) {
                $related = Jojo_Plugin_Jojo_Tags::getRelated('jojo_event', $id, $numrelated, 'jojo_event');
                $smarty->assign('related', $related);
            }

            /* Prepare fields for display */
            $content['title']            = $event['title'];
            $content['seotitle']         = $event['seotitle'];
            $content['breadcrumbs']      = $breadcrumbs;
            $meta_description_template = Jojo::getOption('event_meta_description', '[event], an event on [site] - Read all about [event] here.');
            $content['meta_description'] = str_replace(array('[event]', '[site]'), array($event['title'], _SITETITLE), $meta_description_template);
            $content['metadescription']  = $content['meta_description'];

        } else {
            /* index section */
            if (empty($events)) {
                $smarty->assign('noevent', Jojo::getOption('noevent_description', 'There are currently no upcoming events, please check back later.'));
            } else {
                $pagenum = Jojo::getFormData('pagenum', 1);
                if ($pagenum[0] == 'p') {
                    $pagenum = substr($pagenum, 1);
                }
                $smarty->assign('event','');
                $eventsperpage = Jojo::getOption('eventsperpage', 40);
                $start = ($eventsperpage * ($pagenum-1));
    
                /* get number of events for pagination */
                $now = strtotime('now');
                $numevents = count($events);
                $numpages = ceil($numevents / $eventsperpage);
                /* calculate pagination */
                if ($numpages == 1) {
                    $pagination = '';
                } elseif ($numpages == 2 && $pagenum == 2) {
                    $pagination = sprintf('<a href="%s/p1/">previous...</a>', (_MULTILANGUAGE ? Jojo::getMultiLanguageString ($language, false) : '') . self::_getPrefix('event', (_MULTILANGUAGE ? $language : ''), (!empty($categoryid) ? $categoryid : '')) );
                } elseif ($numpages == 2 && $pagenum == 1) {
                    $pagination = sprintf('<a href="%s/p2/">more...</a>', (_MULTILANGUAGE ? Jojo::getMultiLanguageString ($language, false) : '') . self::_getPrefix('event', (_MULTILANGUAGE ? $language : ''), ($_CATEGORIES ? $categoryid : '')) );
                } else {
                    $pagination = '<ul>';
                    for ($p=1;$p<=$numpages;$p++) {
                        $url = (_MULTILANGUAGE ? Jojo::getMultiLanguageString ($language, false) : '') . self::_getPrefix('event', (_MULTILANGUAGE ? $language : ''), (!empty($categoryid) ? $categoryid : '')) . '/';
                        if ($p > 1) {
                            $url .= 'p' . $p . '/';
                        }
                        if ($p == $pagenum) {
                            $pagination .= '<li>&gt; Page '.$p.'</li>'. "\n";
                        } else {
                            $pagination .= '<li>&gt; <a href="'.$url.'">Page '.$p.'</a></li>'. "\n";
                        }
                    }
                    $pagination .= '</ul>';
                }
                $smarty->assign('pagination',$pagination);
                $smarty->assign('pagenum',$pagenum);
                if (_MULTILANGUAGE) {
                    $smarty->assign('multilangstring', Jojo::getMultiLanguageString($language));
                }
                /* get event content and assign to Smarty */
                $events = array_slice($events, $start, $eventsperpage);
                $smarty->assign('events', $events);
            }
            /* clear the meta description to avoid duplicate content issues */
            $content['metadescription'] = '';

            $smarty->assign('content', $this->page['pg_body']);
        }

        $content['content'] = $smarty->fetch('jojo_event.tpl');
        $content['javascript'] = $smarty->fetch('jojo_event_js.tpl');
        return $content;
    }

    public static function sitemap($sitemap)
    {
        /* See if we have any event sections to display and find all of them */
        $eventindexes = Jojo::selectQuery("SELECT * FROM {page} WHERE pg_link = 'jojo_plugin_jojo_event' AND pg_sitemapnav = 'yes'");
        if (!count($eventindexes)) {
            return $sitemap;
        }

        if (Jojo::getOption('event_inplacesitemap', 'separate') == 'separate') {
            /* Remove any existing links to this section from the page listing on the sitemap */
            foreach($sitemap as $j => $section) {
                $sitemap[$j]['tree'] = self::_sitemapRemoveSelf($section['tree']);
            }
            $_INPLACE = false;
        } else {
            $_INPLACE = true;
        }

        $now = strtotime('now');
        $limit = 15;
        $eventsperpage = Jojo::getOption('eventsperpage', 40);
        $limit = ($eventsperpage >= 15) ? 15 : $eventsperpage ;
         /* Make sitemap trees for each events instance found */
        foreach($eventindexes as $k => $i){
            /* Get language and language longcode if needed */
            if (_MULTILANGUAGE) {
                $language = !empty($i['pg_language']) ? $i['pg_language'] : Jojo::getOption('multilanguage-default', 'en');
                $mldata = Jojo::getMultiLanguageData();
                $lclanguage = $mldata['longcodes'][$language];
            }
            /* Get category url and id if needed */
            $pg_url = $i['pg_url'];
            $_CATEGORIES = (Jojo::getOption('event_enable_categories', 'no') == 'yes') ? true : false ;
            $categorydata =  ($_CATEGORIES) ? Jojo::selectRow("SELECT eventcategoryid FROM {eventcategory} WHERE `ec_url` = ?", array($pg_url)) : '';
            $categoryid = ($_CATEGORIES && count($categorydata)) ? $categorydata['eventcategoryid'] : '';

            /* Create tree and add index and feed links at the top */
            $eventtree = new hktree();
            $indexurl = (_MULTILANGUAGE) ? Jojo::getMultiLanguageString($language, false) . self::_getPrefix('event', $language, $categoryid) . '/' : self::_getPrefix('event', '', $categoryid) . '/' ;
            if ($_INPLACE) {
                $parent = 0;
            } else {
               $eventtree->addNode('index', 0, $i['pg_title'], $indexurl);
               $parent = 'index';
            }

            /* Get the event content from the database */
            $query =  "SELECT * FROM {event} WHERE enddate>$now";
            $query .= (_MULTILANGUAGE) ? " AND (language = '$language')" : '';
            $query .= ($_CATEGORIES) ? " AND (category = '$categoryid')" : '';
            $query .= " ORDER BY startdate ASC LIMIT $limit";

            $events = Jojo::selectQuery($query);
            $n = count($events);
            foreach ($events as $item) {
                $eventtree->addNode($item['eventid'], $parent, $item['title'], self::getUrl($item['eventid'], $item['title'], $item['language'], $item['category']));
            }

            /* Get number of events for pagination */
            $countquery =  "SELECT COUNT(*) AS numevents FROM {event} WHERE enddate>$now";
            $countquery .= (_MULTILANGUAGE) ? " AND (language = '$language')" : '';
            $countquery .= ($_CATEGORIES) ? " AND (category = '$categoryid')" : '';
            $eventscount = Jojo::selectQuery($countquery);
            $numevents = $eventscount[0]['numevents'];
            $numpages = ceil($numevents / $eventsperpage);

            /* calculate pagination */
            if ($numpages == 1) {
                if ($limit < $numevents) {
                    $eventtree->addNode('p1', $parent, 'More ' . $i['pg_title'] , $indexurl );
                }
            } else {
                for ($p=1; $p <= $numpages; $p++) {
                    if (($limit < $eventsperpage) && ($p == 1)) {
                        $eventtree->addNode('p1', $parent, '...More' , $indexurl );
                    } elseif ($p != 1) {
                        $url = $indexurl .'p' . $p .'/';
                        $nodetitle = $i['pg_title'] . ' Index - p'. $p;
                        $eventtree->addNode('p' . $p, $parent, $nodetitle, $url);
                    }
                }
            }

            /* Check for child pages of the plugin page */
            foreach (Jojo::selectQuery("SELECT * FROM {page} WHERE pg_parent = '" . $i['pageid'] . "' AND pg_sitemapnav = 'yes'") as $c) {
                /* Check whether an RSS Feed page exists and is to be shown on the sitemap, and if so, add it to the sitemap array */
                if ($c['pg_link']=='jojo_plugin_jojo_event_rss') {
                    $rssurl = ((_MULTILANGUAGE) ? Jojo::getMultiLanguageString($language, false) : '') . self::_getPrefix('rss', ((_MULTILANGUAGE) ? $language : ''), $categoryid) . '/';
                    $eventtree->addNode('index-rss', $parent, $c['pg_title'], $rssurl);
                } else {
                    $eventtree->addNode($c['pageid'], $parent, $c['pg_title'], $c['pg_url'] . '/');
                }
            }

            /* Add to the sitemap array */
            if ($_INPLACE) {
                /* Add inplace */
                $url = ((_MULTILANGUAGE) ? Jojo::getMultiLanguageString ( $language, false ) : '') . self::_getPrefix('event', ((_MULTILANGUAGE) ? $language : ''), $categoryid) . '/';
                $sitemap['pages']['tree'] = self::_sitemapAddInplace($sitemap['pages']['tree'], $eventtree->asArray(), $url);
            } else {
                /* Add to the end */
                $sitemap["events$k"] = array(
                    'title' => $i['pg_title'] . ( _MULTILANGUAGE ? ' (' . ucfirst($lclanguage) . ')' : ''),
                    'tree' => $eventtree->asArray(),
                    'order' => 3 + $k,
                    'header' => '',
                    'footer' => '',
                    );
            }
        }
        return $sitemap;
    }

    static function _sitemapAddInplace($sitemap, $toadd, $url)
    {
        foreach ($sitemap as $k => $t) {
            if ($t['url'] == $url) {
                $sitemap[$k]['children'] = $toadd;
            } elseif (isset($sitemap[$k]['children'])) {
                $sitemap[$k]['children'] = Jojo_Plugin_Jojo_event::_sitemapAddInplace($t['children'], $toadd, $url);
            }
        }
        return $sitemap;
    }

    static function _sitemapRemoveSelf($tree)
    {
        static $urls;
        $_CATEGORIES = (Jojo::getOption('event_enable_categories', 'no') == 'yes') ? true : false ;

        if (!is_array($urls)) {
            $urls = array();
            $eventindexes = Jojo::selectQuery("SELECT p.*" . ($_CATEGORIES ? ", c.eventcategoryid" : '') . " FROM {page} p " . ($_CATEGORIES ? "LEFT JOIN {eventcategory} c ON (pg_url=ec_url) " : '') . "WHERE pg_link = 'jojo_plugin_jojo_event' AND pg_sitemapnav = 'yes'");
            if (count($eventindexes)==0) {
               return $tree;
            }

            foreach($eventindexes as $key => $i){
                $language = !empty($i['pg_language']) ? $i['pg_language'] : Jojo::getOption('multilanguage-default', 'en');
                $mldata = Jojo::getMultiLanguageData();
                $lclanguage = $mldata['longcodes'][$language];
                $urls[] = ((_MULTILANGUAGE) ? $lclanguage . '/' : '') . self::_getPrefix('event', ((_MULTILANGUAGE) ? $language : ''), ($_CATEGORIES ? $i['eventcategoryid'] : '')) . '/';
                $urls[] = ((_MULTILANGUAGE) ? $lclanguage . '/' : '') . self::_getPrefix('rss', ((_MULTILANGUAGE) ? $language : ''), ($_CATEGORIES ? $i['eventcategoryid'] : '')) . '/';
            }
        }

        foreach ($tree as $k =>$t) {
            if (in_array($t['url'], $urls)) {
                unset($tree[$k]);
            } else {
                $tree[$k]['children'] = self::_sitemapRemoveSelf($t['children']);
            }
        }
        return $tree;
    }

    /**
    /**
     * XML Sitemap filter
     *
     * Receives existing sitemap and adds event pages
     */
    static function xmlsitemap($sitemap)
    {
        /* See if we have any event sections to display and find all of them */
        $eventindexes = Jojo::selectQuery("SELECT * FROM {page} WHERE pg_link = 'jojo_plugin_jojo_event' AND pg_xmlsitemapnav = 'yes'");
        if (!count($eventindexes)) {
            return $sitemap;
        }
        $now = strtotime('now');
         /* Add sitemap entries for each event page instance found */
        foreach($eventindexes as $k => $i){
            /* Get language and language longcode if needed */
            if (_MULTILANGUAGE) {
                $language = !empty($i['pg_language']) ? $i['pg_language'] : Jojo::getOption('multilanguage-default', 'en');
                $mldata = Jojo::getMultiLanguageData();
                $lclanguage = $mldata['longcodes'][$language];
            }
            /* Get category url and id if needed */
            $pg_url = $i['pg_url'];
            $_CATEGORIES = (Jojo::getOption('event_enable_categories', 'no') == 'yes') ? true : false ;
            $categorydata =  ($_CATEGORIES) ? Jojo::selectRow("SELECT eventcategoryid FROM {eventcategory} WHERE `ec_url` = '$pg_url'") : '';
            $categoryid = ($_CATEGORIES && count($categorydata)) ? $categorydata['eventcategoryid'] : '';

            /* Get the event content from the database */
            $query =  "SELECT * FROM {event} WHERE enddate>$now";
            $query .= (_MULTILANGUAGE) ? " AND (language = '$language')" : '';
            $query .= ($_CATEGORIES) ? " AND (category = '$categoryid')" : '';
            $events = Jojo::selectQuery($query);
    
            /* Add events to sitemap */
            foreach($events as $item) {
                $url = _SITEURL . '/'. self::getUrl($item['eventid'], $item['title'], $item['language'], $item['category']);
                $lastmod = '';
                $priority = 0.6;
                $changefreq = '';
                $sitemap[$url] = array($url, $lastmod, $changefreq, $priority);
            }
        }

        /* Return sitemap */
        return $sitemap;
    }

    /**
     * Site Search
     *
     */
    static function search($results, $keywords, $language, $booleankeyword_str=false)
    {
        global $_USERGROUPS;
        $_CATEGORIES = (Jojo::getOption('event_enable_categories', 'no') == 'yes') ? true : false ;
        $_TAGS = class_exists('Jojo_Plugin_Jojo_Tags') ? true : false ;
        $pagePermissions = new JOJO_Permissions();
        $boolean = ($booleankeyword_str) ? true : false;
        $keywords_str = ($boolean) ? $booleankeyword_str :  implode(' ', $keywords);
        if ($boolean && stripos($booleankeyword_str, '+') === 0  ) {
            $like = '1';
            foreach ($keywords as $keyword) {
                $like .= sprintf(" AND (description LIKE '%%%s%%' OR title LIKE '%%%s%%')", Jojo::clean($keyword), Jojo::clean($keyword));
            }
        } elseif ($boolean && stripos($booleankeyword_str, '"') === 0) {
            $like = "(description LIKE '%%%". implode(' ', $keywords). "%%' OR title LIKE '%%%". implode(' ', $keywords) . "%%')";
        } else {
            $like = '(0';
            foreach ($keywords as $keyword) {
                $like .= sprintf(" OR description LIKE '%%%s%%' OR title LIKE '%%%s%%'", Jojo::clean($keyword), Jojo::clean($keyword));
            }
            $like .= ')';
        }
        $tagid = ($_TAGS) ? Jojo_Plugin_Jojo_Tags::_getTagId(implode(' ', $keywords)): '';

        $query = "SELECT eventid, title, location, description, event_image, language, enddate, startdate, category, ((MATCH(title) AGAINST (?" . ($boolean ? ' IN BOOLEAN MODE' : '') . ") * 0.2) + MATCH(title, location, description) AGAINST (?" . ($boolean ? ' IN BOOLEAN MODE' : '') . ")) AS relevance";
        $query .= ", p.pg_url, p.pg_title";
        $query .= " FROM {event} AS event ";
        $query .= $_CATEGORIES ? " LEFT JOIN {eventcategory} c ON (event.category=c.eventcategoryid) LEFT JOIN {page} p ON (p.pg_link='jojo_plugin_jojo_event' AND c.ec_url=p.pg_url)" : "LEFT JOIN {page} p ON (p.pg_link='jojo_plugin_jojo_event' AND p.pg_language=language)";
        $query .= ($language) ? " LEFT JOIN {language} AS language ON (event.language = languageid)" : '';
        $query .= $tagid ? " LEFT JOIN {tag_item} AS tag ON (tag.itemid = event.eventid AND tag.plugin='jojo_event' AND tag.tagid = $tagid)" : '';
        $query .= " WHERE ($like";
        $query .= $tagid ? " OR (tag.itemid = event.eventid AND tag.plugin='jojo_event' AND tag.tagid = $tagid))" : ')';
        $query .= ($language) ? " AND language = '$language' AND language.active = 'yes' " : '';
        $query .= " AND enddate>" . time();
        $query .= " AND p.pg_link='jojo_plugin_jojo_event'";
        $query .= " ORDER BY relevance DESC LIMIT 100";

        $data = Jojo::selectQuery($query, array($keywords_str, $keywords_str));

        if (_MULTILANGUAGE) {
            global $page;
            $mldata = Jojo::getMultiLanguageData();
            $homes = $mldata['homes'];
        } else {
            $homes = array(1);
        }

        foreach ($data as $d) {
            $pagePermissions->getPermissions('event', $d['eventid']);
            if (!$pagePermissions->hasPerm($_USERGROUPS, 'view')) {
                continue;
            }
            $result = array();
            $result['relevance'] = $d['relevance'];
            $result['title'] = $d['title'];
            $result['body'] = $d['description'];
            $result['image'] = 'events/' . $d['event_image'];
            $result['url'] = self::getUrl($d['eventid'], $d['title'], $d['language'], $d['category']);
            $result['absoluteurl'] = _SITEURL. '/' . $result['url'];
            $result['id'] = $d['eventid'];
            $result['plugin'] = 'jojo_event';
            $result['type'] = $d['pg_title'] ? $d['pg_title'] : 'Events';

            if ($_TAGS) {
                $result['tags'] = Jojo_Plugin_Jojo_Tags::getTags('jojo_event', $d['eventid']);
                if ($result['tags'] && array_search(implode(' ', $keywords), $result['tags']) !== false) $result['relevance'] = $result['relevance'] + 1 ;
            }
            $results[] = $result;
        }

        /* Return results */
        return $results;
    }

   /**
     * RSS Icon filter
     * Places the RSS feed icon in the head of the document, sitewide
     */
    static function rssicon($data)
    {
        $link = Jojo::getOption('event_external_rss');
        $data['Events'] = !empty($link) ? $link : _SITEURL . '/' . self::_getPrefix('rss');

        /* add category RSS feed */
        $pg_url = _SITEURI;
        $_CATEGORIES = (Jojo::getOption('event_enable_categories', 'no') == 'yes') ? true : false ;
        $categorydata =  ($_CATEGORIES) ? Jojo::selectRow("SELECT eventcategoryid FROM {eventcategory} WHERE ec_url = '$pg_url'") : '';
        $categoryid = ($_CATEGORIES && count($categorydata)) ? $categorydata['eventcategoryid'] : '';

        if ( $_CATEGORIES && !empty($categoryid)) {
            $data['Events - '.$pg_url] = _SITEURL . '/' . self::_getPrefix('rss', false, $categoryid);
        }
        return $data;
    }

    /**
     * Remove Snip
     * Removes any [[snip]] tags leftover in the content before outputting
     */
    static function removesnip($data)
    {
        $data = str_ireplace('[[snip]]','',$data);
        return $data;
    }


    /**
     * Get the url prefix for a particular part of this plugin
     */
    static function _getPrefix($for='event', $language=false, $categoryid=false) {
        $cacheKey = $for;
        $cacheKey .= ($language) ? $language : 'false';
        $cacheKey .= ($categoryid) ? $categoryid : 'false';

        /* Have we got a cached result? */
        static $_cache;
        if (isset($_cache[$cacheKey])) {
            return $_cache[$cacheKey];
        }

        if (!in_array($for, array('event', 'rss'))) {
            return '';
        }
        /* Cache some stuff */
        $language = $language ? $language : Jojo::getOption('multilanguage-default', 'en');
        $_CATEGORIES = (Jojo::getOption('event_enable_categories', 'no') == 'yes') ? true : false ;
        $categorydata =  ($_CATEGORIES && $categoryid) ? Jojo::selectRow("SELECT `ec_url` FROM {eventcategory} WHERE `eventcategoryid` = '$categoryid';") : '';
        $category = ($_CATEGORIES && isset($categorydata['ec_url'])) ? $categorydata['ec_url'] : '';
        $query = "SELECT pageid, pg_title, pg_url FROM {page} WHERE pg_link = ?";
        $query .= (_MULTILANGUAGE) ? " AND pg_language = '$language'" : '';
        $query .= $category ? " AND pg_url LIKE '%$category'": '';

        if ($for == 'event') {
            $values = array('jojo_plugin_jojo_event');
        } elseif ($for == 'rss') {
            $query = "SELECT pageid, pg_title, pg_url FROM {page} WHERE pg_link = ?";
            $query .= (_MULTILANGUAGE) ? " AND pg_language = '$language'" : '';
            $query .= (!empty($category)) ? " AND pg_url LIKE '$category%'": '';
            $values = array('jojo_plugin_jojo_event_rss');
        }

        $res = Jojo::selectRow($query, $values);
        if ($res) {
            $_cache[$cacheKey] = !empty($res['pg_url']) ? $res['pg_url'] : $res['pageid'] . '/' . $res['pg_title'];
        } else {
            $_cache[$cacheKey] = '';
        }
        return $_cache[$cacheKey];
    }

    function getCorrectUrl()
    {
        global $page;
        $language  = $page->page['pg_language'];
        $pg_url    = $page->page['pg_url'];
        $eventid = Jojo::getFormData('id',     0);
        $pagenum   = Jojo::getFormData('pagenum', 1);

		$category  = Jojo::getFormData('category', '');

        $data = array('category' => '');
        if (Jojo::getOption('event_enable_categories', 'no') == 'yes') {
            $data = Jojo::selectRow("SELECT eventcategoryid FROM {eventcategory} WHERE ec_url=?", $category);
        }
        $categoryid = !empty($data['eventcategoryid']) ? $data['eventcategoryid'] : '';

        if ($pagenum[0] == 'p') {
            $pagenum = substr($pagenum, 1);
        }

        $correcturl = self::getUrl($eventid);
        if ($correcturl) {
            return _SITEURL . '/' . $correcturl;
        }

        /* event index with pagination */
        if ($pagenum > 1) return parent::getCorrectUrl() . 'p' . $pagenum . '/';

        /* event index - default */
        return parent::getCorrectUrl();
    }

    static public function isUrl($uri)
    {
        $prefix = false;
        $getvars = array();
        /* Check the suffix matches and extra the prefix */
        if (preg_match('#^(.+)/([0-9]+)/([^/]+)$#', $uri, $matches)) {
            /* "$prefix/[id:integer]/[string]" eg "events/123/name-of-event/" */
            $prefix = $matches[1];
            $getvars = array(
                        'id' => $matches[2],
                        'category' => $prefix
                        );
        } elseif (preg_match('#^(.+)/([0-9]+)$#', $uri, $matches)) {
            /* "$prefix/[id:integer]" eg "events/123/" */
            $prefix = $matches[1];
            $getvars = array(
                        'id' => $matches[2],
                        'category' => $prefix
                        );
        } elseif (preg_match('#^(.+)/p([0-9]+)$#', $uri, $matches)) {
            /* "$prefix/p[pagenum:([0-9]+)]" eg "events/p2/" for pagination of events */
            $prefix = $matches[1];
            $getvars = array(
                        'pagenum' => $matches[2],
                        'category' => $prefix
                        );
        } elseif (preg_match('#^(.+)/((?!rss)([a-z0-9-_]+))$#', $uri, $matches)) {
            /* "$prefix/[url:((?!rss)string)]" eg "events/name-of-event/" ignoring "events/rss" */
            $prefix = $matches[1];
            $getvars = array(
                        'url' => $matches[2],
                        'category' => $prefix
                        );
            $row = Jojo::selectRow("SELECT eventcategoryid FROM {eventcategory} WHERE ec_url LIKE ?", $uri);
            if ($row) return false;
        } else {
            /* Didn't match */
            return false;
        }

        /* Check the prefix matches */
        if ($res = self::checkPrefix($prefix)) {
            /* The prefix is good, pass through uri parts */
            foreach($getvars as $k => $v) {
                $_GET[$k] = $v;
            }

            return true;
        }
        return false;
    }

    /**
     * Check if a prefix is an event prefix
     */
    static public function checkPrefix($prefix)
    {
        static $_prefixes, $languages, $categories;
        if (!isset($languages)) {
            /* Initialise cache */
            if (Jojo::tableExists('lang_country')) {
                $languages = Jojo::selectAssoc("SELECT lc_code, lc_code as lc_code2 FROM {lang_country}");
            } else {
                $languages = Jojo::selectAssoc("SELECT languageid, languageid as languageid2 FROM {language} WHERE active = 'yes'");
            }
            $categories = array(false);
            if (Jojo::getOption('event_enable_categories', 'no') == 'yes') {
                $categories = array_merge($categories, Jojo::selectAssoc("SELECT eventcategoryid, eventcategoryid as eventcategoryid2 FROM {eventcategory}"));
            }
            $_prefixes = array();
        }
        /* Check if it's in the cache */
        if (isset($_prefixes[$prefix])) {
            return $_prefixes[$prefix];
        }
        /* Check everything */
        foreach ($languages as $language) {
            $language = $language ? $language : Jojo::getOption('multilanguage-default', 'en');
            foreach($categories as $category) {
                $testPrefix = self::_getPrefix('event', $language, $category);
                $_prefixes[$testPrefix] = true;
                if ($testPrefix == $prefix) {
                    /* The prefix is good */
                    return true;
                }
            }
        }

        /* Didn't match */
        $_prefixes[$testPrefix] = false;
        return false;
    }
/*
* Tags
*/

    static function saveTags($record, $tags = array())
    {
        /* Ensure the tags class is available */
        if (!class_exists('Jojo_Plugin_Jojo_Tags')) {
            return false;
        }

        /* Delete existing tags for this item */
        Jojo_Plugin_Jojo_Tags::deleteTags('jojo_event', $record['eventid']);

        /* Save all the new tags */
        foreach($tags as $tag) {
            Jojo_Plugin_Jojo_Tags::saveTag($tag, 'jojo_event', $record['eventid']);
        }
    }

    static function getTagSnippets($ids)
    {
        /* Convert array of ids to a string */
        $ids = "'" . implode($ids, "', '") . "'";

        /* Get the events */
        $events = Jojo::selectQuery("SELECT *
                                       FROM {event}
                                       WHERE
                                            eventid IN ($ids)
                                         AND
                                           enddate > ?
                                       ORDER BY
                                         startdate ASC",
                                      array(time())
                                      );

        /* Create the snippets */
        $snippets = array();
        foreach ($events as $i => $a) {
            $image = !empty($a['event_image']) ? 'events/' . $a['event_image'] : '';
            $snippets[] = array(
                    'id'    => $a['eventid'],
                    'image' => $image,
                    'title' => htmlspecialchars($a['title'], ENT_COMPAT, 'UTF-8', false),
                    'text'  => strip_tags($a['description']),
                    'url'   => Jojo::urlPrefix(false) . self::getUrl($a['eventid'], $a['title'], $a['language'], $a['category'])
                );
        }

        /* Return the snippets */
        return $snippets;
    }
}