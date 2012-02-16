<?php

/**
 * Initialize view.
 *
 * @category   ClearOS
 * @package    Certificate_Manager
 * @subpackage Views
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
$this->lang->load('organization');

///////////////////////////////////////////////////////////////////////////////
// Form handler
///////////////////////////////////////////////////////////////////////////////

$form_mode = 'edit';

if ($form_mode === 'edit') {
    $read_only = FALSE;
    $buttons = array(
        form_submit_update('submit'),
        anchor_cancel('/app/organization')
    );
} else {
    $read_only = TRUE;
    $buttons = array(
        anchor_edit('/app/organization/edit')
    );
}

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open('certificate_manager/initialize');
echo form_header(lang('certificate_manager_certificate'));

echo field_input('hostname', $hostname, lang('certificate_manager_internet_hostname'), $read_only);
echo field_input('organization', $organization, lang('organization_organization'), $read_only);
echo field_input('unit', $unit, lang('organization_unit'), $read_only);
echo field_input('city', $city, lang('organization_city'), $read_only);
echo field_input('region', $region, lang('organization_region'), $read_only);
echo field_dropdown('country', $countries, $country, lang('organization_country'), $read_only);

echo field_button_set($buttons);

echo form_footer();
echo form_close();
