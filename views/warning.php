<?php

/**
 * Warning view.
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

if ($form_type === 'wizard')
    $continue = "";
else
    $continue = "<p align='center'>" . anchor_custom('/app/certificate_manager', lang('base_continue')) . "</p>";

///////////////////////////////////////////////////////////////////////////////
// Infobox
///////////////////////////////////////////////////////////////////////////////

echo "<div id='webconfig_restarting' style='display:none;'>";
echo infobox_warning(
    lang('base_warning'),
    "<div class='theme-loading-normal'>" . lang('certificate_manager_web_interface_is_restarting') . "</div>"
);
echo "</div>";

echo infobox_highlight(
    lang('certificate_manager_web_browser_warning'), 
    lang('certificate_manager_web_browser_warning_description') .
    $continue .
    "<p><img src='" . clearos_app_htdocs('certificate_manager') . "/$image' alt=''></p>"
);
