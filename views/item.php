<?php

/**
 * Initialize view.
 *
 * @category   apps
 * @package    certificate-manager
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/cetificate_manager/
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

$this->lang->load('base');
$this->lang->load('certificate_manager');

///////////////////////////////////////////////////////////////////////////////
// Form handler
///////////////////////////////////////////////////////////////////////////////

if ($form_type === 'add') {
    $read_only = FALSE;
    $form = 'certificate_manager/certificate/add/' . $type;

    if ($type === 'ca') {
        $buttons = array(
            form_submit_custom('submit-form', lang('certificate_manager_create_certificate'))
        );
    } else {
        $buttons = array(
            form_submit_add('submit-form'),
            anchor_cancel('/app/certificate_manager/certificate')
        );
    }
} else {
    $read_only = TRUE;
    $form = 'certificate_manager/certificate';
    $buttons = array(
        anchor_custom('/app/certificate_manager/certificate/install/' . $certificate, lang('base_install')),
        anchor_custom('/app/certificate_manager/certificate/download/' . $certificate, lang('base_download')),
        anchor_custom('/app/certificate_manager/certificate/delete/' . $certificate, lang('base_delete')),
        anchor_cancel('/app/certificate_manager/certificate')
    );

}

if ($type === 'ca') {
    $title = lang('certificate_manager_certificate_authority');
    $type_text = lang('certificate_manager_certificate_authority');
} else {
    $title = lang('certificate_manager_certificate');
    $type_text = lang('certificate_manager_server_certificate');
}

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open($form, array('id' => 'certificate_form'));
echo form_header($title);

echo field_view(lang('certificate_manager_certificate_type'), $type_text);

if ($form_type === 'add')
    echo field_input('hostname', $hostname, lang('certificate_manager_internet_hostname'), $read_only);

echo field_input('organization', $organization, lang('organization_organization'), $read_only);
echo field_input('unit', $unit, lang('organization_unit'), $read_only);

if ($form_type === 'add')
    echo field_input('city', $city, lang('organization_city'), $read_only);

echo field_input('region', $region, lang('organization_region'), $read_only);
echo field_dropdown('country', $countries, $country, lang('organization_country'), $read_only);

echo field_button_set($buttons);

echo form_footer();
echo form_close();
