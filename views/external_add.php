<?php

/**
 * Certificates view.
 *
 * @category    Apps
 * @package     Certificates
 * @subpackage  View
 * @author      Roman Kosnar <kosnar@apeko.cz>
 * @copyright   2014 Roman Kosnar / APEKO GROUP s.r.o.
 * @copyright   2015 ClearFoundation
 * @license     http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link        http://www.clearfoundation.com/docs/developer/apps/cetificate_manager/
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('certificate_manager');

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open_multipart('certificate_manager/external/add');
echo form_header(lang('certificate_manager_add_certificate'));

echo field_input('name', $name, lang('certificate_manager_name'));
echo field_file('cert_file', $certFile, lang('certificate_manager_certificate_file'));
echo field_file('key_file', $keyFile, lang('certificate_manager_key_file'));
echo field_file('ca_file', $caFile, lang('certificate_manager_ca_file'));

echo field_button_set(
    array(
        form_submit_add('submit'),
        anchor_cancel('/app/certificate_manager')
    )
);

echo form_footer();
echo form_close();
