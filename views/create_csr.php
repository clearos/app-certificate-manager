<?php

/**
 * Create CSR/Key pair view.
 *
 * @category    Apps
 * @package     Certificates
 * @subpackage  View
 * @author      Benjamin Chambers <benjamin@egloo.ca>
 * @copyright   2016 ClearFoundation
 * @license     http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link        http://www.clearfoundation.com/docs/developer/apps/cetificate_manager/
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('certificate_manager');
$this->lang->load('organization');

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

$read_only = FALSE;

echo form_open('certificate_manager/external/create_csr', array('id' => 'create_csr_form'));
echo form_header(lang("certificate_manager_create_csr"));

$password_field_options = array();
if (!$password_protection)
    $password_field_options = array('hide_field' => TRUE);
    
echo field_input('name', $name, lang('certificate_manager_name'), $read_only);
echo field_input('cn', $cn, lang('certificate_manager_common_name'), $read_only);
echo field_dropdown('key_size', $key_sizes, $key_size, lang('certificate_manager_key_size'), $read_only);
echo field_input('organization', $organization, lang('organization_organization'), $read_only);
echo field_input('unit', $unit, lang('organization_unit'), $read_only);
echo field_input('city', $city, lang('organization_city'), $read_only);
echo field_input('region', $region, lang('organization_region'), $read_only);
echo field_dropdown('country', $countries, $country, lang('organization_country'), $read_only);
echo field_input('email', $email, lang('certificate_manager_email'), $read_only);
echo field_toggle_enable_disable('password_protection', $password_protection, lang('certificate_manager_password_protection'), $read_only);
echo field_password('password', $password, lang('base_password'), $read_only, $password_field_options);
echo field_password('verify', $verify, lang('base_verify'), $read_only, $password_field_options);

echo field_button_set(
    array(
        form_submit_custom('submit', lang('base_create')),
        anchor_cancel('/app/certificate_manager')
    )
);

echo form_footer();
echo form_close();
