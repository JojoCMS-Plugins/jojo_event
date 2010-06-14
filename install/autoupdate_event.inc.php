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
$o = 1;

$default_td[$table]['td_displayfield']  = 'title';
$default_td[$table]['td_rolloverfield'] = '';
$default_td[$table]['td_orderbyfields'] = 'startdate, title';
$default_td[$table]['td_topsubmit']     = 'yes';
$default_td[$table]['td_deleteoption']  = 'yes';
$default_td[$table]['td_menutype']      = 'tree';
$default_td[$table]['td_categoryfield'] = 'category';
$default_td[$table]['td_categorytable'] = 'eventcategory';
$default_td[$table]['td_help']          = '';

/* Event ID */
$field = 'eventid';
$default_fd[$table][$field]['fd_order'] = $o++;
$default_fd[$table][$field]['fd_type']  = 'readonly';
$default_fd[$table][$field]['fd_help']  = 'A unique ID, automatically assigned by the system';
$default_fd[$table][$field]['fd_tabname'] = "Content";

// Category Field
$default_fd[$table]['category'] = array(
        'fd_name' => "Category",
        'fd_type' => "dblist",
        'fd_options' => "eventcategory",
        'fd_default' => "0",
        'fd_size' => "20",
        'fd_help' => "If applicable, the category the Event belongs to",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );

/* Event Title */
$field = 'title';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'text';
$default_fd[$table][$field]['fd_required'] = 'yes';
$default_fd[$table][$field]['fd_size']     = '50';
$default_fd[$table][$field]['fd_help']     = 'The event name';
$default_fd[$table][$field]['fd_tabname'] = "Content";

// SEO Title Field
$default_fd[$table]['seotitle'] = array(
        'fd_name' => "SEO Title",
        'fd_type' => "text",
        'fd_options' => "seotitle",
        'fd_size' => "60",
        'fd_help' => "Title of the Event - it may be worth including your search phrase at the beginning of the title to improve rankings for that phrase.",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "standard",
    );

/* Event Location Name */
$field = 'location';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_name']     = 'Location Name';
$default_fd[$table][$field]['fd_type']     = 'text';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size']     = '50';
$default_fd[$table][$field]['fd_help']     = 'The event location name';
$default_fd[$table][$field]['fd_tabname'] = "Content";

/* Event Location Map link */
$field = 'locationaddress';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_name']     = 'Location Address';
$default_fd[$table][$field]['fd_type']     = 'text';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size']     = '50';
$default_fd[$table][$field]['fd_help']     = 'The event location address';
$default_fd[$table][$field]['fd_tabname'] = "Content";

/* Location URL */
$field = 'locationurl';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'url';
$default_fd[$table][$field]['fd_name']     = 'Location Weblink';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size']     = '30';
$default_fd[$table][$field]['fd_help']     = 'Web link for more information about the location';
$default_fd[$table][$field]['fd_tabname'] = "Content";

/* Event Location Map link */
$field = 'locationmaplink';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'url';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size']     = '50';
$default_fd[$table][$field]['fd_help']     = 'The event location map link';
$default_fd[$table][$field]['fd_tabname'] = "Content";

/* URL */
$field = 'url';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'url';
$default_fd[$table][$field]['fd_name']     = 'More info link';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size']     = '30';
$default_fd[$table][$field]['fd_help']     = 'Web link for more information';
$default_fd[$table][$field]['fd_tabname'] = "Content";

/* Email contact */
$field = 'contactemail';
$default_fd[$table][$field]['fd_name']     = 'Contact email link';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'email';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size']     = '50';
$default_fd[$table][$field]['fd_help']     = 'Email address (for bookings etc)';
$default_fd[$table][$field]['fd_tabname'] = "Content";


// Start Date Field
$default_fd['event']['startdate'] = array(
        'fd_name' => "Start Date",
        'fd_type' => "unixdate",
        'fd_default' => "NOW()",
        'fd_help' => "The event begins on this date",
        'fd_order' => $o++,
        'fd_mode' => "standard",
        'fd_tabname' => "Content",
    );

// Expiry Date Field
$default_fd['event']['enddate'] = array(
        'fd_name' => "End Date",
        'fd_type' => "unixdate",
        'fd_default' => "NOW()",
        'fd_help' => "The event ends on this date",
        'fd_order' => $o++,
        'fd_mode' => "standard",
        'fd_tabname' => "Content",
    );

/* Event Location */
$field = 'starttime';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_name']     = 'Start Time';
$default_fd[$table][$field]['fd_type']     = 'text';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size']     = '20';
$default_fd[$table][$field]['fd_help']     = 'The time the event begins';
$default_fd[$table][$field]['fd_tabname'] = "Content";


// Short Description Field
$default_fd[$table]['snippet'] = array(
        'fd_name' => "Snippet Description",
        'fd_type' => "textarea",
        'fd_rows' => 8,
        'fd_cols' => 55,
        'fd_help' => "A short description of the event. Used for rollover text on links, which enhances usability",
        'fd_order' => $o++,
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );

// Description Code
$default_fd['event']['description_code'] = array(
        'fd_name' => "Description",
        'fd_type' => "texteditor",
        'fd_options' => "description",
        'fd_rows' => "10",
        'fd_cols' => "50",
        'fd_help' => "A Description for the event.",
        'fd_order' => $o++,
        'fd_mode' => "advanced",
        'fd_quickedit' => "yes",
        'fd_tabname' => "Content",
    );

// Description Field
$default_fd['event']['description'] = array(
        'fd_name' => "Description",
        'fd_type' => "hidden",
        'fd_rows' => "10",
        'fd_cols' => "50",
        'fd_help' => "A Description for the event.",
        'fd_order' => $o++,
        'fd_mode' => "advanced",
        'fd_quickedit' => "yes",
        'fd_tabname' => "Content",
    );

// Image Field
$default_fd['event']['event_image'] = array(
        'fd_name' => "Image",
        'fd_type' => "fileupload",
        'fd_help' => "An image for the event, if  available",
        'fd_order' => $o++,
        'fd_mode' => "standard",
        'fd_tabname' => "Content",
        'fd_quickedit' => "yes",
    );

// Language Field
$default_fd[$table]['language'] = array(
        'fd_name' => "Language/Country",
        'fd_type' => "dblist",
        'fd_options' => "language",
        'fd_default' => "en",
        'fd_size' => "20",
        'fd_help' => "The language or country section the event should appear in",
        'fd_order' => $o++,
        'fd_mode' => "advanced",
        'fd_tabname' => "Content",
    );

//Timestamp
$default_fd[$table]['dateadded'] = array(
        'fd_order' => $o++,
        'fd_required' => 'no',
        'fd_type' => "unixdate",
        'fd_default' => 'NOW()',
        'fd_help' => '',
        'fd_tabname' => "Content",
    );
    
    
/* Tags Tab */
if (class_exists('Jojo_Plugin_Jojo_Tags')) {
// Tags Field
$default_fd[$table]['tags'] = array(
        'fd_name' => "Tags",
        'fd_type' => "tag",
        'fd_options' => "jojo_event",
        'fd_showlabel' => "no",
        'fd_help' => "A list of words describing the event",
        'fd_order' => "1",
        'fd_tabname' => "Tags",
    );
} else {
$default_fd[$table]['tags'] = array(
        'fd_name' => "Tags",
        'fd_type' => "hidden",
        'fd_options' => "jojo_event",
        'fd_showlabel' => "no",
        'fd_help' => "",
        'fd_order' => "1",
        'fd_tabname' => "Content",
    );

}

/* Category */

// ID Field
$default_fd['eventcategory']['eventcategoryid'] = array(
        'fd_name' => "Categoryid",
        'fd_type' => "readonly",
        'fd_help' => "A unique ID, automatically assigned by the system",
        'fd_order' => "1",
        'fd_tabname' => "Content",
        'fd_mode' => "advanced",
    );

// URL Field
$default_fd['eventcategory']['ec_url'] = array(
        'fd_name' => "URL",
        'fd_type' => "internalurl",
        'fd_required' => "yes",
        'fd_size' => "60",
        'fd_help' => "URL for the Event Category. This will be used for the base URL for all events in this category. The Page url for this category's home page MUST match the category URL.",
        'fd_order' => "2",
        'fd_tabname' => "Content",
    );

// Sortby Field
$default_fd['eventcategory']['sortby'] = array(
        'fd_name' => "Sortby",
        'fd_type' => "radio",
        'fd_options' => "title asc:Title\nstartdate asc:Event Start Date\nenddate asc:Event End Date",
        'fd_default' => "startdate asc",
        'fd_order' => "3",
        'fd_tabname' => "Content",
    );

// PageID Field
$default_fd['eventcategory']['ec_pageid'] = array(
        'fd_name' => "PageID",
        'fd_type' => "hidden",
        'fd_required' => "no",
        'fd_help' => "The page id for the page used for this category.",
        'fd_order' => "4",
        'fd_tabname' => "Content",
    );
