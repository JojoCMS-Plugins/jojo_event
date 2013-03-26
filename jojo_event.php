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

class Jojo_Plugin_Jojo_event extends Jojo_Plugin
{

    /* Gets $num items sorted by startdate (asc) for use on homepages and sidebars */
    static function getItems($num=false, $start = 0, $categoryid='all', $sortby='startdate asc', $exclude=false, $include=false) {
        global $page;
        $now = time();
        $language = _MULTILANGUAGE ? (!empty($page->page['pg_language']) ? $page->page['pg_language'] : Jojo::getOption('multilanguage-default', 'en')) : '';
        if (is_array($categoryid)) {
             $categoryquery = " AND category IN ('" . implode("','", $categoryid) . "')";
        } else {
            $categoryquery = is_numeric($categoryid) ? " AND category = '$categoryid'" : '';
        }
        /* if calling page is an event, Get current event, exclude from the list and up the limit by one */
        $exclude = ($exclude && Jojo::getOption('event_sidebar_exclude_current', 'no')=='yes' && $page->page['pg_link']=='jojo_plugin_jojo_event' && Jojo::getFormData('id')) ? Jojo::getFormData('id') : '';
        if ($num && $exclude) $num++;
        $query  = "SELECT e.*, date_format( from_unixtime( e.startdate ) , '%M %Y' ) as month, c.*, p.pageid, pg_menutitle, pg_title, pg_url, pg_status, pg_language";
        $query .= " FROM {event} e";
        $query .= " LEFT JOIN {eventcategory} c ON (e.category=c.eventcategoryid) LEFT JOIN {page} p ON (c.pageid=p.pageid)";
        $query .= " WHERE 1" . $categoryquery;
        $query .= " AND enddate>$now";
        $query .= (_MULTILANGUAGE && $categoryid == 'all' && $include != 'alllanguages') ? " AND (pg_language = '$language')" : '';
        $query .= $sortby ? " ORDER BY $sortby" : '';
        $query .= $num ? " LIMIT $start, $num" : '';
        $items = Jojo::selectQuery($query);
        $items = self::cleanItems($items, $exclude);
        if (!$num)  $items = self::sortItems($items, $sortby);
        return $items;
    }

    static function getItemsById($ids = false, $sortby='startdate', $exclude=false) {
        $query  = "SELECT e.*, c.*, p.pageid, pg_menutitle, pg_title, pg_url, pg_status, pg_language";
        $query .= " FROM {event} e";
        $query .= " LEFT JOIN {eventcategory} c ON (e.category=c.eventcategoryid) LEFT JOIN {page} p ON (c.pageid=p.pageid)";
        $query .=  is_array($ids) ? " WHERE eventid IN ('". implode("',' ", $ids) . "')" : " WHERE eventid=$ids";
        $items = Jojo::selectQuery($query);
        $items = self::cleanItems($items, $exclude);
        $items = is_array($ids) ? self::sortItems($items, $sortby) : $items[0];
        return $items;
    }

    /* clean items for output */
    static function cleanItems($items, $exclude=false) {
        global $_USERGROUPS;
        $now    = time();
        $pagePermissions = new JOJO_Permissions();
        foreach ($items as $k=>&$i){
            $pagePermissions->getPermissions('page', $i['pageid']);
            if (!$pagePermissions->hasPerm($_USERGROUPS, 'view') || $i['enddate']<$now || (!empty($i['eventid']) && $i['eventid']==$exclude) || $i['pg_status']=='inactive') {
                unset($items[$k]);
                continue;
            }
            $i['id']           = $i['eventid'];
            $i['title']        = htmlspecialchars($i['title'], ENT_COMPAT, 'UTF-8', false);
            $i['seotitle']        = isset($i['seotitle']) ? htmlspecialchars($i['seotitle'], ENT_COMPAT, 'UTF-8', false): $i['title'];
            // Snip for the index description
            $splitcontent = Jojo::iExplode('[[snip]]', $i['description']);
            $i['bodyplain'] = array_shift($splitcontent);
            /* Strip all tags and template include code ie [[ ]] */
            $i['bodyplain'] = preg_replace('/\[\[.*?\]\]/', '',  trim(strip_tags($i['bodyplain'])));
            $i['locationmaplink']    = htmlspecialchars($i['locationmaplink'], ENT_COMPAT, 'UTF-8', false);
            $i['fstartdate'] = strftime( Jojo::getOption('upcomingevents_dateformat', '%d %b'), $i['startdate']);
            $i['fenddate'] = strftime( Jojo::getOption('upcomingevents_dateformat', '%d %b'), $i['enddate']);
            $i['image'] = !empty($i['event_image']) ? 'events/' . $i['event_image'] : '';
            $i['imagedata'] = !empty($i['event_image']) ? getimagesize(_DOWNLOADDIR . '/events/' . $i['event_image']) : '';
            $i['url']          = self::getUrl($i['eventid'], '', $i['title'], $i['language'], $i['category']);
            $i['date']       = $i['dateadded'];
            $i['pagetitle'] = !empty($i['pg_menutitle']) ? htmlspecialchars($i['pg_menutitle'], ENT_COMPAT, 'UTF-8', false) : htmlspecialchars($i['pg_title'], ENT_COMPAT, 'UTF-8', false);
            $i['pageurl']   = (_MULTILANGUAGE ? Jojo::getMultiLanguageString ($i['pg_language'], true) : '') . (!empty($i['pg_url']) ? $i['pg_url'] : $i['pageid'] . '/' .  Jojo::cleanURL($i['pg_title'])) . '/';
            $i['plugin']     = 'jojo_event';
            unset($items[$k]['description_code']);
        }
        return $items;
    }

    /* sort items for output */
    static function sortItems($items, $sortby=false) {
        if ($sortby) {
            $order = "startdate";
            $reverse = false;
            switch ($sortby) {
              case "startdate asc":
                $order="startdate";
                break;
              case "enddate asc":
                $order="enddate";
                break;
              case "title asc":
                $order="title";
                break;
            }
            usort($items, array('Jojo_Plugin_Jojo_event', $order . 'sort'));
            $items = $reverse ? array_reverse($items) : $items;
        }
        return $items;
    }

    private static function startdatesort($a, $b)
    {
         if ($a['startdate']) {
            return strnatcasecmp($a['startdate'],$b['startdate']);
         }
    }

    private static function enddatesort($a, $b)
    {
         if ($a['enddate']) {
            return strnatcasecmp($a['enddate'],$b['enddate']);
         }
    }

    private static function titlesort($a, $b)
    {
         if ($a['title']) {
            return strnatcasecmp($a['title'],$b['title']);
         }
    }

    static function getUrl($id=false, $url=false, $title=false, $language=false, $category=false )
    {
        if (_MULTILANGUAGE) {
            $language = !empty($language) ? $language : Jojo::getOption('multilanguage-default', 'en');
            $multilangstring = Jojo::getMultiLanguageString($language, false);
        }
        /* URL specified */
        if (!empty($url)) {
            $fullurl = (_MULTILANGUAGE ? $multilangstring : '') . self::_getPrefix('', $category) . '/' . $url . '/';
            return $fullurl;
         }
        /* ID + title specified */
        if ($id && !empty($title)) {
            $fullurl = (_MULTILANGUAGE ? $multilangstring : '') . self::_getPrefix('', $category) . '/' . $id . '/' .  Jojo::cleanURL($title) . '/';
          return $fullurl;
        }
        /* use the ID to find either the URL or title */
        if ($id) {
            $item = Jojo::selectRow("SELECT title, language, category FROM {event} WHERE eventid = ?", array($id));
             if ($item) {
                return self::getUrl($id, '', $item['title'], $item['language'], $item['category']);
            }
         }
        /* No item matching the ID supplied or no ID supplied */
        return false;
    }

    function _getContent()
    {
        global $smarty;
        $content = array();

        if (_MULTILANGUAGE) {
            $language = !empty($this->page['pg_language']) ? $this->page['pg_language'] : Jojo::getOption('multilanguage-default', 'en');
            $multilangstring = Jojo::getMultiLanguageString($language, false);
            $smarty->assign('multilangstring', $multilangstring);
        }
        /* Are we looking at an event or the index? */
        $id = Jojo::getFormData('id',        0);
        $url       = Jojo::getFormData('url',      '');
        $action    = Jojo::getFormData('action',   '');
        $pageid = $this->page['pageid'];
        $categorydata =  Jojo::selectRow("SELECT * FROM {eventcategory} WHERE pageid = ?", $pageid);
        $categorydata['type'] = isset($categorydata['type']) ? $categorydata['type'] : 'normal';
        if ($categorydata['type']=='index') {
            $categoryid = 'all';
        } elseif ($categorydata['type']=='parent') {
            $childcategories = Jojo::selectQuery("SELECT eventcategoryid FROM {page} p  LEFT JOIN {eventcategory} c ON (c.pageid=p.pageid) WHERE pg_parent = ? AND pg_link = 'jojo_plugin_jojo_event'", $pageid);
            foreach ($childcategories as $c) {
                $categoryid[] = $c['eventcategoryid'];
            }
            $categoryid[] = $categorydata['eventcategoryid'];
        } else {
            $categoryid = $categorydata['eventcategoryid'];
        }
        $sortby = $categorydata ? $categorydata['sortby'] : '';

        $events = self::getItems('', '', $categoryid, $sortby);

        if ($action == 'rss') {
            $rssfields = array(
                'pagetitle' => $this->page['pg_title'],
                'pageurl' => _SITEURL . '/' . (_MULTILANGUAGE ? $multilangstring : '') . $this->page['pg_url'] . '/',
                'title' => 'title',
                'body' => 'description',
                'url' => 'url',
                'date' => 'date',
                'datetype' => 'unix'
            );
            $events = array_slice($events, 0, Jojo::getOption('rss_num_items', 15));
            Jojo::getFeed($events, $rssfields);
        }

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

            /* calculate the next and previous events */
            if (Jojo::getOption('event_next_prev') == 'yes') {
                if (!empty($nextevent)) {
                    $smarty->assign('nextevent', $nextevent);
                }
                if (!empty($prevevent)) {
                    $smarty->assign('prevevent', $prevevent);
                }
            }

            /* if tags class is available */
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
                /* get related items if tags plugin installed and option enabled */
                if ($numrelated=Jojo::getOption('event_num_related')) {
                    $related = Jojo_Plugin_Jojo_Tags::getRelated('jojo_event', $id, $numrelated, 'jojo_event');
                    $smarty->assign('related', $related);
                }
            }

            /* Add breadcrumb */
            $breadcrumbs                      = $this->_getBreadCrumbs();
            $breadcrumb                       = array();
            $breadcrumb['name']               = $event['title'];
            $breadcrumb['rollover']           = $event['snippet'];
            $breadcrumb['url']                = $event['url'];
            $breadcrumbs[count($breadcrumbs)] = $breadcrumb;

            /* Assign event content to Smarty */
            $smarty->assign('event', $event);

            /* Prepare fields for display */
            $content['title']            = $event['title'];
            $content['seotitle']         = $event['seotitle'];
            $content['breadcrumbs']      = $breadcrumbs;
            $meta_description_template = Jojo::getOption('event_meta_description', '[title] - [body]...');
            $metabody = (strlen($event['bodyplain']) >400) ?  substr($mbody=wordwrap($event['bodyplain'], 400, '$$'), 0, strpos($mbody,'$$')) : $event['bodyplain'];
            $metafilters = array(
                    '[title]',
                    '[site]',
                    '[body]'
                    );
            $metafilterreplace = array(
                    $event['title'],
                    _SITETITLE,
                    $metabody
                    );
            $content['meta_description'] = str_replace($metafilters, $metafilterreplace, $meta_description_template);

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
                /* get number of events for pagination */
                $eventsperpage = Jojo::getOption('eventsperpage', 40);
                $start = ($eventsperpage * ($pagenum-1));
                $numevents = count($events);
                $numpages = ceil($numevents / $eventsperpage);
                /* calculate pagination */
                if ($numpages == 1) {
                    $pagination = '';
                } elseif ($numpages == 2 && $pagenum == 2) {
                    $pagination = sprintf('<a href="%s/p1/">previous...</a>', (_MULTILANGUAGE ? $multilangstring : '') . self::_getPrefix('event', $categoryid) );
                } elseif ($numpages == 2 && $pagenum == 1) {
                    $pagination = sprintf('<a href="%s/p2/">more...</a>', (_MULTILANGUAGE ? $multilangstring : '') . self::_getPrefix('event', $categoryid) );
                } else {
                    $pagination = '<ul>';
                    for ($p=1;$p<=$numpages;$p++) {
                        $url = (_MULTILANGUAGE ? $multilangstring : '') . self::_getPrefix('event', $categoryid) . '/';
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
                $smarty->assign('pagination', $pagination);
                $smarty->assign('pagenum', $pagenum);

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

    static function getPluginPages($for=false, $language=false)
    {
        $items =  Jojo::selectQuery("SELECT c.*, p.pageid, pg_title, pg_url, pg_language, pg_livedate, pg_expirydate, pg_status, pg_sitemapnav, pg_xmlsitemapnav  FROM {eventcategory} c LEFT JOIN {page} p ON (c.pageid=p.pageid) ORDER BY pg_language, pg_parent");
        $now    = time();
        global $_USERGROUPS;
        $pagePermissions = new JOJO_Permissions();
        foreach ($items as $k=>&$i){
            $pagePermissions->getPermissions('page', $i['pageid']);
            if (!$pagePermissions->hasPerm($_USERGROUPS, 'view') || $i['pg_livedate']>$now || (!empty($i['pg_expirydate']) && $i['pg_expirydate']<$now) || $i['pg_status']=='inactive' || ($language && $i['pg_language']!=$language) ) {
                unset($items[$k]);
                continue;
            }
            if ($for && $for =='sitemap' && $i['pg_sitemapnav']=='no') {
                unset($items[$k]);
                continue;
            } elseif ($for && $for =='xmlsitemap' && $i['pg_xmlsitemapnav']=='no') {
                unset($items[$k]);
                continue;
            }
        }
        return $items;
    }

    static function _getPrefix($for='event', $categoryid=false) {
        $cacheKey = $for;
        $cacheKey .= ($categoryid) ? $categoryid : 'false';

        /* Have we got a cached result? */
        static $_cache;
        if (isset($_cache[$cacheKey])) {
            return $_cache[$cacheKey];
        }

        /* Cache some stuff */
        $res = Jojo::selectRow("SELECT p.pageid, pg_title, pg_url FROM {page} p LEFT JOIN {eventcategory} c ON (c.pageid=p.pageid) WHERE `eventcategoryid` = '$categoryid'");
        if ($res) {
            $_cache[$cacheKey] = !empty($res['pg_url']) ? $res['pg_url'] : $res['pageid'] . '/' . $res['pg_title'];
        } else {
            $_cache[$cacheKey] = '';
        }
        return $_cache[$cacheKey];
    }

    static function getPrefixById($id=false) {
        if ($id) {
            $data = Jojo::selectRow("SELECT category FROM {event} WHERE eventid = ?", array($id));
            if ($data) {
                $prefix = self::_getPrefix('', $data['category']);
                return $prefix;
            }
        }
        return false;
    }

    function getCorrectUrl()
    {
        global $page;
        $language  = $page->page['pg_language'];
        $id = Jojo::getFormData('id',     0);
        $url       = Jojo::getFormData('url',    '');
        $action    = Jojo::getFormData('action', '');
        $pagenum   = Jojo::getFormData('pagenum', 1);

        $data = Jojo::selectRow("SELECT eventcategoryid FROM {eventcategory} WHERE pageid=?", $page->page['pageid']);
        $categoryid = !empty($data['eventcategoryid']) ? $data['eventcategoryid'] : '';

        if ($pagenum[0] == 'p') {
            $pagenum = substr($pagenum, 1);
        }

        $correcturl = self::getUrl($id, $url, null, $language, $categoryid);
        if ($correcturl) {
            return _SITEURL . '/' . $correcturl;
        }

        /* index with pagination */
        if ($pagenum > 1) return parent::getCorrectUrl() . 'p' . $pagenum . '/';

        if ($action == 'rss') return parent::getCorrectUrl() . 'rss/';

        /* event index - default */
        return parent::getCorrectUrl();
    }

    static public function isUrl($uri)
    {
        $prefix = false;
        $getvars = array();
        /* Check the suffix matches and extract the prefix */
       if ($uribits = Jojo_Plugin::isPluginUrl($uri)) {
            $prefix = $uribits['prefix'];
            $getvars = $uribits['getvars'];
        } else {
            return false;
        }
        /* Check the prefix matches */
        if ($res = self::checkPrefix($prefix)) {
            /* If full uri matches a prefix it's an index page so ignore it and let the page plugin handle it */
            if (self::checkPrefix(trim($uri, '/'))) return false;
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
        static $_prefixes, $categories;
        if (!isset($categories)) {
            /* Initialise cache */
            $categories = array(false);
            $categories = array_merge($categories, Jojo::selectAssoc("SELECT eventcategoryid, eventcategoryid as eventcategoryid2 FROM {eventcategory}"));
            $_prefixes = array();
        }
        /* Check if it's in the cache */
        if (isset($_prefixes[$prefix])) {
            return $_prefixes[$prefix];
        }
        /* Check everything */
        foreach($categories as $category) {
            $testPrefix = self::_getPrefix('event', $category);
            $_prefixes[$testPrefix] = true;
            if ($testPrefix == $prefix) {
                /* The prefix is good */
                return true;
            }
        }
        /* Didn't match */
        $_prefixes[$testPrefix] = false;
        return false;
    }


    // Sync the category data over to the page table
    static function admin_action_after_save_eventcategory() {
        if (!Jojo::getFormData('fm_pageid', 0)) {
            // no pageid set for this category (either it's a new category or maybe the original page was deleted)
            self::sync_category_to_page();
       }
    }

    // Sync the category data over from the page table
    static function admin_action_after_save_page() {
        if (strtolower(Jojo::getFormData('fm_pg_link',    ''))=='jojo_plugin_jojo_event') {
           self::sync_page_to_category();
       }
    }

    static function sync_category_to_page() {
        // Get the category id (if an existing category being saved where the page has been deleted)
        $catid = Jojo::getFormData('fm_eventcategoryid', 0);
        if (!$catid) {
        // no id because this is a new category - shouldn't really be done this way, new categories should be added by adding a new page
            $cats = Jojo::selectQuery("SELECT eventcategoryid FROM {eventcategory} ORDER BY eventcategoryid");
            // grab the highest id (assumes this is the newest one just created)
            $cat = array_pop($cats);
            $catid = $cat['eventcategoryid'];
        }
        // add a new hidden page for this category and make up a title
            $newpageid = Jojo::insertQuery(
            "INSERT INTO {page} SET pg_title = ?, pg_link = ?, pg_url = ?, pg_parent = ?, pg_status = ?",
            array(
                'Orphaned events',  // Title
                'jojo_plugin_jojo_event',  // Link
                'orphaned-events',  // URL
                0,  // Parent - don't do anything smart, just put it at the top level for now
                'hidden' // hide new page so it doesn't show up on the live site until it's been given a proper title and url
            )
        );
        // If we successfully added the page, update the category with the new pageid
        if ($newpageid) {
            jojo::updateQuery(
                "UPDATE {eventcategory} SET pageid = ? WHERE eventcategoryid = ?",
                array(
                    $newpageid,
                    $catid
                )
            );
       }
    return true;
    }

    static function sync_page_to_category() {
        // Get the list of categories and the page id if available
        $categories = jojo::selectAssoc("SELECT pageid AS id, pageid FROM {eventcategory}");
        $pageid = Jojo::getFormData('fm_pageid', 0);
        // if it's a new page it won't have an id in the form data, so get it from the title
        if (!$pageid) {
           $title = Jojo::getFormData('fm_pg_title', 0);
           $page =  Jojo::selectRow("SELECT pageid, pg_url FROM {page} WHERE pg_title= ? AND pg_link = ? AND pg_language = ?", array($title, Jojo::getFormData('fm_pg_link', ''), Jojo::getFormData('fm_pg_language', '')));
           $pageid = $page['pageid'];
        }
        // no category for this page id
        if (!count($categories) || !isset($categories[$pageid])) {
            jojo::insertQuery("INSERT INTO {eventcategory} (pageid) VALUES ('$pageid')");
        }
        return true;
    }

    public static function sitemap($sitemap)
    {
        global $page;
        /* See if we have any event sections to display and find all of them */
        $indexes =  self::getPluginPages('sitemap');
        if (!count($indexes)) {
            return $sitemap;
        }

        if (Jojo::getOption('event_inplacesitemap', 'separate') == 'separate') {
            /* Remove any existing links to the events section from the page listing on the sitemap */
            foreach($sitemap as $j => $section) {
                $sitemap[$j]['tree'] = self::_sitemapRemoveSelf($section['tree']);
            }
            $_INPLACE = false;
        } else {
            $_INPLACE = true;
        }

        $limit = 15;
        $eventsperpage = Jojo::getOption('eventsperpage', 40);
         /* Make sitemap trees for each events instance found */
        foreach($indexes as $k => $i){
            /* Set language */
            $language = (_MULTILANGUAGE && !empty($i['pg_language'])) ? $i['pg_language'] : '';
            $multilangstring = Jojo::getMultiLanguageString($language, false);
            /* Set category */
            $categoryid = $i['eventcategoryid'];
            $sortby = $i['sortby'];

            /* Create tree and add index and feed links at the top */
            $eventtree = new hktree();
            $indexurl = (_MULTILANGUAGE ? $multilangstring : '' ) . self::_getPrefix('event', $categoryid) . '/';
            if ($_INPLACE) {
                $parent = 0;
            } else {
               $eventtree->addNode('index', 0, $i['pg_title'], $indexurl);
               $parent = 'index';
            }

            $events = self::getItems('', '', $categoryid, $sortby);
            $n = count($events);

            /* Trim items down to first page and add to tree*/
            $events = array_slice($events, 0, $eventsperpage);
            foreach ($events as $a) {
                $eventtree->addNode($a['id'], $parent, $a['title'], $a['url']);
            }

            /* Get number of pages for pagination */
            $numpages = ceil($n / $eventsperpage);
            /* calculate pagination */
            if ($numpages > 1) {
                for ($p=2; $p <= $numpages; $p++) {
                    $url = $indexurl .'p' . $p .'/';
                    $nodetitle = $i['pg_title'] . '  - page '. $p;
                    $eventtree->addNode('p' . $p, $parent, $nodetitle, $url);
                }
            }
            /* Add RSS link for the plugin page */
           $eventtree->addNode('rss', $parent, $i['pg_title'] . ' RSS Feed', $indexurl . 'rss/');

            /* Check for child pages of the plugin page */
            foreach (Jojo::selectQuery("SELECT pageid, pg_title, pg_url FROM {page} WHERE pg_parent = '" . $i['pageid'] . "' AND pg_sitemapnav = 'yes'") as $c) {
                    if ($c['pg_url']) {
                        $eventtree->addNode($c['pageid'], $parent, $c['pg_title'], (_MULTILANGUAGE ? $multilangstring : '') . $c['pg_url'] . '/');
                    } else {
                        $eventtree->addNode($c['pageid'], $parent, $c['pg_title'], (_MULTILANGUAGE ? $multilangstring : '') . $c['pageid']  . '/' .  Jojo::cleanURL($c['pg_title']) . '/');
                    }
            }

            /* Add to the sitemap array */
            if ($_INPLACE) {
                /* Add inplace */
                $url = (_MULTILANGUAGE ? $multilangstring : '') . self::_getPrefix('event', $categoryid) . '/';
                $sitemap['pages']['tree'] = self::_sitemapAddInplace($sitemap['pages']['tree'], $eventtree->asArray(), $url);
            } else {
                if (_MULTILANGUAGE) {
                    $mldata = Jojo::getMultiLanguageData();
                    $lclanguage = $mldata['longcodes'][$language];
                }
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
                $sitemap[$k]['children'] = self::_sitemapAddInplace($t['children'], $toadd, $url);
            }
        }
        return $sitemap;
    }

    static function _sitemapRemoveSelf($tree)
    {
        static $urls;

        if (!is_array($urls)) {
            $urls = array();
            $indexes =  self::getPluginPages('sitemap');
            if (count($indexes)==0) {
               return $tree;
            }
            foreach($indexes as $key => $i){
                $language = (_MULTILANGUAGE && !empty($i['pg_language'])) ? $i['pg_language'] : '';
                $multilangstring = Jojo::getMultiLanguageString($language, false);
                $urls[] = (_MULTILANGUAGE ? $multilangstring : '')  . self::_getPrefix('event', $i['eventcategoryid']) . '/';
                $urls[] = (_MULTILANGUAGE ? $multilangstring : '')  . self::_getPrefix('event', $i['eventcategoryid']) . '/rss/';
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
        /* Get events from database */
        $events = self::getItems('', '', 'all', '', '', 'alllanguages');
        $now = time();
        $indexes =  self::getPluginPages('xmlsitemap');
        $ids=array();
        foreach ($indexes as $i) {
            $ids[$i['eventcategoryid']] = true;
        }
        /* Add events to sitemap */
        foreach($events as $k => $a) {
            // strip out events from expired pages
            if (!isset($ids[$a['category']])) {
                unset($events[$k]);
                continue;
            }
            $url = _SITEURL . '/'. $a['url'];
            $lastmod = '';
            $priority = 0.6;
            $changefreq = '';
            $sitemap[$url] = array($url, $lastmod, $changefreq, $priority);
        }
        /* Return sitemap */
        return $sitemap;
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
     * RSS Icon filter
     * Places the RSS feed icon in the head of the document, sitewide
     */
    static function rssicon($data)
    {
        global $page;
        $link = Jojo::getOption('rss_external_url');
        if ($link) {
            $data['Events'] =  $link;
        }
        /* add RSS feeds for each page */
        $categories =  self::getPluginPages('', (_MULTILANGUAGE ? $page->page['pg_language'] : ''));
        foreach ($categories as $c) {
            $prefix =  self::_getPrefix('', $c['eventcategoryid']) . '/rss/';
            if ($prefix && $c['rsslink']==1) {
                $data[$c['pg_title']] = _SITEURL . '/' .  (_MULTILANGUAGE ? Jojo::getMultiLanguageString($c['pg_language'], false) : '') . $prefix;
            }
        }
        return $data;
    }

    /**
     * Site Search
     */
    static function search($results, $keywords, $language, $booleankeyword_str=false)
    {
        $searchfields = array(
            'plugin' => 'jojo_event',
            'table' => 'event',
            'idfield' => 'eventid',
            'languagefield' => 'language',
            'primaryfields' => 'title',
            'secondaryfields' => 'title, location, description',
        );
        $rawresults =  Jojo_Plugin_Jojo_search::searchPlugin($searchfields, $keywords, $language, $booleankeyword_str);
        $data = $rawresults ? self::getItemsById(array_keys($rawresults)) : '';
        if ($data) {
            foreach ($data as $result) {
                $result['relevance'] = $rawresults[$result['id']]['relevance'];
                $result['body'] = $result['bodyplain'];
                $result['type'] = $result['pagetitle'];
                $result['tags'] = isset($rawresults[$result['id']]['tags']) ? $rawresults[$result['id']]['tags'] : '';
                $results[] = $result;
            }
        }
        /* Return results */
        return $results;
    }

    /**
     * Newsletter content
     */
    static function newslettercontent($contentarray, $newletterid=false)
    {
        /* Get all the events for this newsletter */
        if ($newletterid) {
            $itemids = Jojo::selectQuery('SELECT e.eventid FROM {event} e, {newsletter_event} n WHERE e.eventid = n.eventid AND n.newsletterid = ? ORDER BY n.order, e.startdate DESC', $newletterid);
            if ($itemids) {
                foreach ($itemids as $i) {
                    $ids[] = $i['eventid'];
                }
                $items = self::getItemsById($ids, '', 'showhidden');
                $css = Jojo::getOption('newslettercss', '');
                $newscss = array();
                if ($css) {
                    $styles = explode("\n", $css);
                    foreach ($styles as $k => $s) {
                        $style = explode('=', $s);
                        $newscss[$k]['tag'] = $style[0];
                        $newscss[$k]['style'] = $style[1];
                    }
                }
                $contentarray['events'] = array();
                foreach($items as &$a) {
                    $a['title'] = mb_convert_encoding($a['title'], 'HTML-ENTITIES', 'UTF-8');
                    $a['bodyplain'] = mb_convert_encoding($a['bodyplain'], 'HTML-ENTITIES', 'UTF-8');
                    $a['body'] = mb_convert_encoding(Jojo::inlineStyle($a['description'], $newscss), 'HTML-ENTITIES', 'UTF-8');
                    $a['imageurl'] = rawurlencode($a['image']);
                    foreach ($ids as $k => $i) {
                        if ($i==$a['eventid']) {
                            $contentarray['events'][$k] = $a;
                        }
                    }
                }
                ksort($contentarray['events']);
            }
        }
        /* Return results */
        return $contentarray;
    }

/*
* Tags
*/
    static function getTagSnippets($ids)
    {
        $snippets = self::getItemsById($ids);
        return $snippets;
    }
}
