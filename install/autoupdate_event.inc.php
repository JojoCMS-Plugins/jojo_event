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
$default_td[$table]['td_menutype']      = 'list';
$default_td[$table]['td_categoryfield'] = '';
$default_td[$table]['td_categorytable'] = '';
$default_td[$table]['td_help']          = '';

/* Event ID */
$field = 'eventid';
$default_fd[$table][$field]['fd_order'] = $o++;
$default_fd[$table][$field]['fd_type']  = 'readonly';
$default_fd[$table][$field]['fd_help']  = 'A unique ID, automatically assigned by the system';

/* Event Title */
$field = 'title';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'text';
$default_fd[$table][$field]['fd_required'] = 'yes';
$default_fd[$table][$field]['fd_size']     = '50';
$default_fd[$table][$field]['fd_help']     = 'The event name';

/* Event Location */
$field = 'location';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'text';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size']     = '50';
$default_fd[$table][$field]['fd_help']     = 'The event location';

/* URL */
$field = 'url';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'url';
$default_fd[$table][$field]['fd_name']     = 'More info link';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size']     = '30';
$default_fd[$table][$field]['fd_help']     = 'Web link for more information';

// Start Date Field
$default_fd['event']['startdate'] = array(
        'fd_name' => "Start Date",
        'fd_type' => "unixdate",
        'fd_default' => "NOW()",
        'fd_help' => "The event begins on this date",
        'fd_order' => $o++,
        'fd_mode' => "standard",
    );

// Expiry Date Field
$default_fd['event']['enddate'] = array(
        'fd_name' => "End Date",
        'fd_type' => "unixdate",
        'fd_default' => "NOW()",
        'fd_help' => "The event ends on this date",
        'fd_order' => $o++,
        'fd_mode' => "standard",
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
    );

// Image Field
$default_fd['event']['event_image'] = array(
        'fd_name' => "Image",
        'fd_type' => "fileupload",
        'fd_help' => "An image for the event, if  available",
        'fd_order' => $o++,
        'fd_mode' => "standard",
        'fd_quickedit' => "yes",
    );

