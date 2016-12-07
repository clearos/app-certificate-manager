<?php

/**
 * Certificates view.
 *
 * @category    Apps
 * @package     Certificates
 * @subpackage  View
 * @author     Roman Kosnar <kosnar@apeko.cz>
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2014 Roman Kosnar / APEKO GROUP s.r.o.
 * @copyright  2015 ClearFoundation
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

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open('certificate_manager/external/view/' . $name);
echo form_header(lang('certificate_manager_file_information'));

echo field_input('name', $name, lang('certificate_manager_name'), TRUE);
echo field_input('key', $certificate['key'], lang('certificate_manager_key_file'), TRUE);
echo field_input('crt', $certificate['crt'], lang('certificate_manager_certificate_file'), TRUE);
echo field_input('intermediate', $certificate['intermediate'], lang('certificate_manager_intermediate_file'), TRUE);
echo field_dropdown('key_group', $key_group_options, $key_group, lang('certificate_manager_file_group'), FALSE);
echo field_dropdown('key_permissions', $key_permission_options, $key_permissions, lang('certificate_manager_key_file_permissions'), FALSE);

echo field_button_set(
    array(
        form_submit_update('submit'),
        anchor_cancel('/app/certificate_manager')
    )
);

echo form_footer();
echo form_close();
///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

$options['anchors'] = anchor_custom(
    '/app/certificate_manager',
    lang('base_return_to_summary')
);

echo box_open(lang('certificate_manager_certificate_details') . ' - ' . $name, $options);
echo box_content_open();
echo "
    <span style='white-space: pre-wrap;'>
    $details
    </span>
";
echo box_content_close();
echo box_close();
