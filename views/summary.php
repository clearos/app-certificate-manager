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
use clearos\apps\certificate_manager\Cert_Manager;

$this->lang->load('certificate_manager');

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('base_description'),
    lang('certificate_manager_certificate'),
);

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

foreach ($certificates as $cert => $details) {
    // Skip user certificates
    if ($details['type'] === SSL::CERT_TYPE_USER)
        continue;

    $item['title'] = $cert;
    $item['action'] = '/app/certificate_manager/certificate/view/' . $cert;
    $item['anchors'] = button_set(
        array(anchor_view('/app/certificate_manager/certificate/view/' . $cert, 'high'))
    );
    $item['details'] = array(
        $details['app_description'],
        $cert,
    );

    $items[] = $item;
}

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    lang('certificate_manager_certificates'),
    array(),
    $headers,
    $items
);
$items = array();

foreach (Cert_Manager::get_certs() as $cert => $files) {
    $buttons = array();
    $buttons[] = anchor_custom('/app/certificate_manager/detail_cert/'.$cert, lang('certificate_manager_detail'));
    if(strcmp($cert, '_default_') != 0) {
        $buttons[] = anchor_custom('/app/certificate_manager/remove_cert/'.$cert, lang('base_delete'));
    }
    $name = $cert == Cert_Manager::CERT_DEF ? lang('certificate_manager_default') : $cert;
    $item['title'] = $name;
    $item['action'] = NULL;
    $item['anchors'] = button_set($buttons);
    $parts = array();
    foreach($files as $file => $s) {
        $parts[] = $file;
    }
    sort($parts);
    $item['details'] = array($name, implode(", ", $parts));
    $items[] = $item;
}

echo summary_table(
        lang('certificate_manager_ssl'),
        array(anchor_custom('/app/certificate_manager/add_cert', lang('base_add'))),
        array(lang('certificate_manager_certificate'), lang('certificate_manager_files')),
        $items
);
