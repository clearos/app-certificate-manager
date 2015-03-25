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

$name = $cert == CertManager::CERT_DEF ? 'Default' : $cert;

?>
<div class="theme-summary-table-container ui-widget">
	<div class="theme-summary-table-header ui-state-active ui-corner-top">
		<div class="theme-summary-table-title"><?php echo lang('certificate_manager_certDetail').$name; ?></div>
		<div class="theme-summary-table-action">
			<div class="theme-button-set ui-buttonset">
				<a class="theme-button-set-first theme-button-set-last theme-anchor theme-anchor-custom theme-anchor-important ui-button ui-widget ui-state-default ui-button-text-only ui-corner-left" href="/app/certificate_manager" >
					<span class="ui-button-text"><?php echo lang('base_back'); ?></span>
				</a>
			</div>
		</div>
	</div>
	<div class="dataTables_wrapper">
		<div class="fg-toolbar ui-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix"></div>
		<pre style="white-space: pre-wrap; margin: 0 10px;"><?php echo CertManager::certDetails($cert); ?></pre>
		<div class="fg-toolbar ui-toolbar ui-widget-header ui-corner-bl ui-corner-br ui-helper-clearfix"></div>
	</div>
</div>