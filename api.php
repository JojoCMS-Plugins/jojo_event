<?php
/**
 *
 * Copyright 2007 Michael Cochrane <code@gardyneholt.co.nz>
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Michael Cochrane <code@gardyneholt.co.nz>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

$_provides['pluginClasses'] = array(
        'Jojo_Plugin_Jojo_event' => 'Events - Listing and View',
        );

/* Register URI handlers */
Jojo::registerURI(null, 'jojo_plugin_jojo_event', 'isUrl');

/* Sitemap filter */
Jojo::addFilter('jojo_sitemap', 'sitemap', 'jojo_event');

/* XML Sitemap filter */
Jojo::addFilter('jojo_xml_sitemap', 'xmlsitemap', 'jojo_event');

/* Search Filter */
if (class_exists('Jojo_Plugin_Jojo_search')) {
    Jojo::addFilter('jojo_search', 'search', 'jojo_event');
}
/*  RSS icon filter */
Jojo::addFilter('rssicon', 'rssicon', 'jojo_event');

/* Content Filter */
Jojo::addFilter('content', 'removesnip', 'jojo_event');

/* capture the button press in the admin section */
Jojo::addHook('admin_action_after_save_page', 'admin_action_after_save_page', 'jojo_event');
Jojo::addHook('admin_action_after_save_eventcategory', 'admin_action_after_save_eventcategory', 'jojo_event');


$_options[] = array(
    'id' => 'upcomingevents_dateformat',
    'category' => 'Events',
    'label' => 'Date Format',
    'description' => 'Date display format',
    'type' => 'text',
    'default' => '%d %b',
    'options' => '',
    'plugin'      => 'jojo_event'
);

$_options[] = array(
    'id'          => 'noevent_description',
    'category'    => 'Events',
    'label'       => 'No events text',
    'description' => 'Text to show when no upcoming events listed',
    'type'        => 'textarea',
    'default'     => 'There are currently no upcoming events, please check back later.',
    'options'     => '',
    'plugin'      => 'jojo_event'
);

$_options[] = array(
    'id'          => 'event_meta_description',
    'category'    => 'Events',
    'label'       => 'Dynamic meta description',
    'description' => 'A dynamically built meta description template which will assist with SEO. Variables to use are [title], [site], [body].',
    'type'        => 'textarea',
    'default'     => '[title] - [body]...',
    'options'     => '',
    'plugin'      => 'jojo_event'
);

$_options[] = array(
    'id'          => 'eventsperpage',
    'category'    => 'Events',
    'label'       => 'Events per page on index',
    'description' => 'The number of events to show on the Events index page before paginating',
    'type'        => 'integer',
    'default'     => '40',
    'options'     => '',
    'plugin'      => 'jojo_event'
);

$_options[] = array(
    'id'          => 'event_next_prev',
    'category'    => 'Events',
    'label'       => 'Show Next / Previous links',
    'description' => 'Show a link to the next and previous event at the top of each event page',
    'type'        => 'radio',
    'default'     => 'yes',
    'options'     => 'yes,no',
    'plugin'      => 'jojo_event'
);

$_options[] = array(
    'id'          => 'event_num_related',
    'category'    => 'Events',
    'label'       => 'Show Related Events',
    'description' => 'The number of related events to show at the bottom of each event (0 means do not show)',
    'type'        => 'integer',
    'default'     => '5',
    'options'     => '',
    'plugin'      => 'jojo_event'
);

$_options[] = array(
    'id'          => 'event_num_sidebar_events',
    'category'    => 'Events',
    'label'       => 'Number of event teasers to show in the sidebar',
    'description' => 'The number of events to be displayed as snippets in a teaser box on other pages - set to 0 to disable',
    'type'        => 'integer',
    'default'     => '0',
    'options'     => '',
    'plugin'      => 'jojo_event'
);

$_options[] = array(
    'id'          => 'event_sidebar_randomise',
    'category'    => 'Events',
    'label'       => 'Randmomise selection of teasers out of',
    'description' => 'Pick the sidebar events from a larger group, shuffle them, and then slice them back to the original number so that sidebar content is more dynamic  - set to 0 to disable',
    'type'        => 'integer',
    'default'     => '0',
    'options'     => '',
    'plugin'      => 'jojo_event'
);

$_options[] = array(
    'id'          => 'event_sidebar_categories',
    'category'    => 'Events',
    'label'       => 'Event teasers by category',
    'description' => 'Generate sidebar list from all events and also create a list from each category',
    'type'        => 'radio',
    'default'     => 'no',
    'options'     => 'yes,no',
    'plugin'      => 'jojo_event'
);

$_options[] = array(
    'id'          => 'event_sidebar_exclude_current',
    'category'    => 'Events',
    'label'       => 'Exclude current event from list',
    'description' => 'Exclude the event from the sidebar list when on that events page',
    'type'        => 'radio',
    'default'     => 'no',
    'options'     => 'yes,no',
    'plugin'      => 'jojo_event'
);

$_options[] = array(
    'id'          => 'event_inplacesitemap',
    'category'    => 'Events',
    'label'       => 'Events sitemap location',
    'description' => 'Show artciles as a separate list on the site map, or in-place on the page list',
    'type'        => 'radio',
    'default'     => 'inplace',
    'options'     => 'separate,inplace',
    'plugin'      => 'jojo_event'
);

$_options[] = array(
    'id'          => 'event_enable_categories',
    'category'    => 'Events',
    'label'       => 'Event Categories',
    'description' => 'Allows multiple event collections by category under their own URLs',
    'type'        => 'radio',
    'default'     => 'no',
    'options'     => 'yes,no',
    'plugin'      => 'jojo_event'
);

if (class_exists('Jojo_Plugin_Jojo_Tags')) {
    $_options[] = array(
        'id'          => 'event_tag_cloud_minimum',
        'category'    => 'Events',
        'label'       => 'Minimum tags to form cloud',
        'description' => 'On the event pages, a tag cloud will be formed from tags if this number of tags is met (otherwise a plain text list of tags is shown). Set to zero to always use the plain text list.',
        'type'        => 'integer',
        'default'     => '0',
        'options'     => '',
        'plugin'      => 'jojo_event'
    );
}
