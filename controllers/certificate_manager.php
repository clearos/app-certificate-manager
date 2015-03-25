<?php

/**
 * Certificate manager controller.
 *
 * @category   apps
 * @package    certificate-manager
 * @subpackage controllers
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
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Certificate manager controller.
 *
 * @category   apps
 * @package    certificate-manager
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/certificate_manager/
 */

use clearos\apps\certificate_manager\CertManager;

class Certificate_Manager extends ClearOS_Controller
{
    /**
     * Certificate_Manager server summary view.
     *
     * @return view
     */

    function index()
    {
        // Show mode status widget if we're not initialized
        //-------------------------------------------------

        $this->load->module('accounts/system_mode');

        if (! $this->system_mode->initialized()) {
            $this->system_mode->widget();
            return;
        }

        // Load libraries
        //---------------

        $this->lang->load('certificate_manager');
		$this->load->library('CertManager');

        // Load views
        //-----------

        $views = array('certificate_manager/certificate');

        if (!($this->session->userdata('wizard')) && clearos_app_installed('user_certificates'))
            $views[] = 'certificate_manager/policy';

        $this->page->view_forms($views, lang('certificate_manager_app_name'));
    }

	function detilCert($cert) {
		$this->lang->load('certificate_manager');
		$this->load->library('CertManager');
		$data['cert'] = $cert;
		$options['type'] = MY_Page::TYPE_WIDE_CONFIGURATION;
		$this->page->view_form('viewCert', $data, lang('certificate_manager_app_name'), $options);
	}

	function addCert() {
		$this->lang->load('certificate_manager');
		$this->load->library('CertManager');

		// prepare validation
		$file = $_FILES['certFile'];
		if($file && $file['name']) $_POST['certFile'] = 'certFile';

		$file = $_FILES['keyFile'];
		if($file && $file['name']) $_POST['keyFile'] = 'keyFile';

		$file = $_FILES['caFile'];
		if($file && $file['name']) $_POST['caFile'] = 'caFile';

		$this->form_validation->set_policy('name',		'certificate_manager/CertManager', 'validate_cert_name',	TRUE);
		$this->form_validation->set_policy('certFile',	'certificate_manager/CertManager', 'validate_crt_file',		TRUE);
		$this->form_validation->set_policy('keyFile',	'certificate_manager/CertManager', 'validate_key_file',		TRUE);
		$this->form_validation->set_policy('caFile',	'certificate_manager/CertManager', 'validate_ca_file',		FALSE);
		$form_ok = $this->form_validation->run();

		if (($this->input->post('submit') && $form_ok)) {
			if(!($err = CertManager::update($this->input))) {
				redirect('/certificate_manager');
				return;
			}
		}
		$data['errs'] = $err;
		$this->page->view_form('addCert', $data, lang('certificate_manager_app_name'));
	}

	function removeCert($cert) {
		$this->lang->load('certificate_manager');
		$this->load->library('CertManager');

		if(!($err = CertManager::certRemove($cert))) {
 			redirect('/certificate_manager');
			return;
		}
		$data['errs'] = $err;
		$this->page->view_form('certificates', $data, lang('certificate_manager_app_name'));
	}
}
