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

$buttons = array();

if (empty($state))
    $buttons[] = anchor_custom('/app/certificate_manager/external/delete/' . $name, lang('base_delete'));

$buttons[] = anchor_custom('/app/certificate_manager', lang('base_return_to_summary'));

echo form_open('certificate_manager/external/view/' . $name);
echo form_header(lang('certificate_manager_file_information'));

echo field_input('name', $name, lang('certificate_manager_name'), TRUE);
echo field_input('key', $certificate['key'], lang('certificate_manager_key_file'), TRUE);
echo field_input('crt', $certificate['crt'], lang('certificate_manager_certificate_file'), TRUE);
echo field_input('intermediate', $certificate['intermediate'], lang('certificate_manager_intermediate_file'), TRUE);

echo field_button_set($buttons);

echo form_footer();
echo form_close();

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
    $item['title'] = $certificate['description'];
    $item['details'] = array(
        $certificate['description'],
        $certificate['nickname'],
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
