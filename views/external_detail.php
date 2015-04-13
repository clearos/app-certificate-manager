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

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

$options['anchors'] = anchor_custom(
    '/app/certificate_manager/external',
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
