<?php

/**
 * Certificates view.
 *
 * @category	Apps
 * @package		Certificates
 * @subpackage	View
 * @author		Roman Kosnar <kosnar@apeko.cz>
 * @copyright	2014 Roman Kosnar / APEKO GROUP s.r.o.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/cetificate_manager/
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

use clearos\apps\certificate_manager\CertManager;

$this->lang->load('Certificates');

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

if($errs)
	echo infobox_warning(lang('base_error'), implode("<br>", $errs));

echo form_open_multipart('certificate_manager/addCert');
echo form_header(lang('certificate_manager_addCert'));

echo field_input	('name',		$name,		lang('certificate_manager_name'),			false);
echo field_file		('certFile',	$certFile,	lang('certificate_manager_cert_file'),	false);
echo field_file		('keyFile',		$keyFile,	lang('certificate_manager_key_file'),		false);
echo field_file		('caFile',		$caFile,	lang('certificate_manager_ca_file'),		false);

echo field_button_set(array(
			form_submit_add('submit'),
			anchor_cancel('/app/certificate_manager')
	));

echo form_footer();
echo form_close();

?>
<style>
	.theme-field-file {
		float: none;
	}
</style>