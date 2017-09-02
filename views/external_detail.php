<?php

/**
 * Certificates view.
 *
 * @category    Apps
 * @package     Certificates
 * @subpackage  View
 * @author      Roman Kosnar <kosnar@apeko.cz>
 * @author      ClearFoundation <developer@clearfoundation.com>
 * @copyright   2014 Roman Kosnar / APEKO GROUP s.r.o.
 * @copyright   2015-2017 ClearFoundation
 * @license     http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link        http://www.clearfoundation.com/docs/developer/apps/cetificate_manager/
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('certificate_manager');
$this->lang->load('base');

if ($errmsg)
    echo infobox_warning(lang('base_warning'), $errmsg);

if (!empty($state))
    echo infobox_highlight(lang('certificate_manager_deployed'), lang('certificate_manager_deployed_help'));

///////////////////////////////////////////////////////////////////////////////
// File information
///////////////////////////////////////////////////////////////////////////////

$file_anchors[] = anchor_custom('/app/certificate_manager', lang('base_return_to_summary'));
$file_headers = array(
    lang('base_description'),
    lang('base_filename'),
);

if (empty($state))
    $file_anchors[] = anchor_custom('/app/certificate_manager/external/delete/' . $name, lang('base_delete'), 'low');

// CSR
//----

if ($certificate['req']) {
    $basename = preg_replace('/.*\//', '', $certificate['req']);

    $item['title'] = lang('certificate_manager_certificate_signing_request');
    $item['details'] = array(
        lang('certificate_manager_certificate_signing_request'),
        $basename
    );
    $item['anchors'] = anchor_custom('/app/certificate_manager/certificate/download_external/' . $basename, lang('base_download'));

    $file_items[] = $item;
}

// Key File
//---------

if ($certificate['key']) {
    $basename = preg_replace('/.*\//', '', $certificate['key']);

    $item['title'] = lang('certificate_manager_key_file');
    $item['details'] = array(
        lang('certificate_manager_key_file'),
        $basename
    );
    $item['anchors'] = anchor_custom('/app/certificate_manager/certificate/download_external/' . $basename, lang('base_download'));

    $file_items[] = $item;
}

// Certificate
//------------

if ($certificate['crt']) {
    $basename = preg_replace('/.*\//', '', $certificate['crt']);

    $item['title'] = lang('certificate_manager_certificate_file');
    $item['details'] = array(
        lang('certificate_manager_certificate_file'),
        $basename
    );
    $item['anchors'] = anchor_custom('/app/certificate_manager/certificate/download_external/' . $basename, lang('base_download'));

    $file_items[] = $item;
}

// Intermediate
//-------------

if ($certificate['intermediate']) {
    $basename = preg_replace('/.*\//', '', $certificate['intermediate']);

    $item['title'] = lang('certificate_manager_intermediate_file');
    $item['details'] = array(
        lang('certificate_manager_intermediate_file'),
        $basename
    );
    $item['anchors'] = anchor_custom('/app/certificate_manager/certificate/download_external/' . $basename, lang('base_download'));

    $file_items[] = $item;
}

// Summary table
//--------------

echo summary_table(
    lang('certificate_manager_file_information') . ' - ' . $name,
    $file_anchors,
    $file_headers,
    $file_items,
    []
);

///////////////////////////////////////////////////////////////////////////////
// State
///////////////////////////////////////////////////////////////////////////////

$items = array();
$anchors = array();

$headers = array(
    lang('certificate_manager_app'),
    lang('base_details'),
);

foreach ($state as $certificate) {
    $item['title'] = $certificate['app_description'];
    $item['details'] = array(
        $certificate['app_description'],
        $certificate['app_key'],
    );

    $items[] = $item;
}

$options['no_action'] = TRUE;
$options['empty_table_message'] = lang('certificate_manager_not_in_use');

echo summary_table(
    lang('certificate_manager_deployed'),
    $anchors,
    $headers,
    $items,
    $options
);

///////////////////////////////////////////////////////////////////////////////
// Details
///////////////////////////////////////////////////////////////////////////////

echo box_open(lang('certificate_manager_certificate_details') . ' - ' . $name);
echo box_content_open();
echo "
    <span style='white-space: pre-wrap;'>
    $details
    </span>
";
echo box_content_close();
echo box_close();
