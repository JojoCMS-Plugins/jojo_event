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
        'JOJO_Plugin_Jojo_event' => 'Events - Listing and View',
        );


$_options[] = array(
    'id' => 'upcomingevents_num',
    'category' => 'Events',
    'label' => 'Number of events',
    'description' => 'The number of upcoming events to show in the sidebar',
    'type' => 'integer',
    'default' => '5',
    'options' => ''
);