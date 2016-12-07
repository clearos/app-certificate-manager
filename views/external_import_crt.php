<?php

/**
 * Import CRT and Intermediate certificates view.
 *
 * @category    Apps
 * @package     Certificates
 * @subpackage  View
 * @author      benjamin@egloo.ca <benjamin@egloo.ca>
 * @copyright   2016 ClearFoundation
 * @license     http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link        http://www.clearfoundation.com/docs/developer/apps/cetificate_manager/
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('certificate_manager');

if ($errmsg)
    echo infobox_warning(lang('base_warning'), $errmsg);

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open_multipart('certificate_manager/external/import_crt/' . $name);
echo form_header(lang('certificate_manager_import_signed_crt'));

echo fieldset_header(lang('base_required'));
echo field_input('name', $name, lang('certificate_manager_name'), TRUE);
echo field_file('cert_file', $cert_file, lang('certificate_manager_certificate_file'));
echo fieldset_header(lang('certificate_manager_usually_required'));
echo field_file('intermediate_file', $key_file, lang('certificate_manager_intermediate_file'));

echo field_button_set(
    array(
        form_submit_custom('submit', lang('base_upload')),
        anchor_cancel('/app/certificate_manager')
    )
);

echo form_footer();
echo form_close();
