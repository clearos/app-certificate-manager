<?php

/**
 * Certificate authority view.
 *
 * @category   ClearOS
 * @package    Certificate_Manager
 * @subpackage Views
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

$this->lang->load('base');
$this->lang->load('certificate_manager');

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open('certificate_manager/edit');
echo form_header(lang('certificate_manager_certificate'));

echo field_input('certificate', $certificate, lang('certificate_manager_certificate'), TRUE);
echo field_input('description', $attributes['app_description'], lang('base_description'), TRUE);
echo field_input('issued', $attributes['issued'], lang('certificate_manager_issued'), TRUE);
echo field_input('expires', $attributes['expires'], lang('certificate_manager_expires'), TRUE);
echo field_input('country', $attributes['country'], lang('certificate_manager_country'), TRUE);
echo field_input('region', $attributes['region'], lang('certificate_manager_region'), TRUE);
echo field_input('city', $attributes['city'], lang('certificate_manager_city'), TRUE);

echo field_button_set(
    array(
        anchor_custom('/app/certificate_manager/certificate/install/' . $certificate, lang('base_install')),
        anchor_custom('/app/certificate_manager/certificate/download/' . $certificate, lang('base_download')),
        anchor_custom('/app/certificate_manager/certificate/delete/' . $certificate, lang('base_delete')),
        anchor_cancel('/app/certificate_manager/certificate')
    )
);

echo form_footer();
echo form_close();
