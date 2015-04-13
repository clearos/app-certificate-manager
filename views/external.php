<?php

/**
 * Certificate view.
 *
 * @category   apps
 * @package    certificate-manager
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/certificate_manager/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//  
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\certificate_manager\SSL as SSL;
use clearos\apps\certificate_manager\External_Certificates;

$this->lang->load('certificate_manager');

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('certificate_manager_certificate'),
    lang('certificate_manager_files')
);

$anchors = array(
    anchor_custom('/app/certificate_manager/external/add', lang('base_add'))
);

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

$items = array();

foreach ($certificates as $cert => $files) {
    $buttons = array();
    $buttons[] = anchor_custom(
        '/app/certificate_manager/external/view/' . $cert,
        lang('certificate_manager_detail')
    );

    if (strcmp($cert, '_default_') != 0)
        $buttons[] = anchor_custom('/app/certificate_manager/external/delete/' . $cert, lang('base_delete'));

    // FIXME: review
    // $name = $cert == External_Certificates::CERT_DEF ? lang('certificate_manager_default') : $cert;
    $name = $cert;

    $item['title'] = $name;
    $item['action'] = NULL;
    $item['anchors'] = button_set($buttons);
    $parts = array();
    foreach ($files as $file => $s)
        $parts[] = $file;

    sort($parts);
    $item['details'] = array($name, implode(", ", $parts));
    $items[] = $item;
}

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    lang('certificate_manager_external_certificates'),
    $anchors,
    $headers,
    $items
);
