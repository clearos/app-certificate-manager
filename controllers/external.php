<?php

/**
 * External certificate manager controller.
 *
 * @category   apps
 * @package    certificate-manager
 * @subpackage controllers
 * @author     Roman Kosnar <kosnar@apeko.cz>
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2014 Roman Kosnar / APEKO GROUP s.r.o.
 * @copyright  2015 ClearFoundation
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
 * External certificate manager controller.
 *
 * @category   apps
 * @package    certificate-manager
 * @subpackage controllers
 * @author     Roman Kosnar <kosnar@apeko.cz>
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2014 Roman Kosnar / APEKO GROUP s.r.o.
 * @copyright  2015 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/certificate_manager/
 */

class External extends ClearOS_Controller
{
    /**
     * Certificate_Manager server summary view.
     *
     * @return view
     */

    function index()
    {
        // Load libraries
        //---------------

        $this->lang->load('certificate_manager');
        $this->load->library('External_Certificates');

        // Load view data
        //---------------

        try {
            $data['certificates'] = $this->external_certificates->get_certs();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $this->page->view_form('certificate_manager/external', $data, lang('certificate_manager_external_certificates'));
    }

    /**
     * Detail view.
     *
     * @param string $cert certificate name
     *
     * @return view
     */

    function view($cert)
    {
        // Load libraries
        //---------------

        $this->lang->load('certificate_manager');
        $this->load->library('External_Certificates');

        // Load view data
        //---------------

        try {
            $data['name'] = $cert;
            $data['details'] = $this->external_certificates->get_cert_details($cert);
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $options['type'] = MY_Page::TYPE_WIDE_CONFIGURATION;

        $this->page->view_form('external_detail', $data, lang('certificate_manager_app_name'), $options);
    }

    /**
     * Add view.
     *
     * @return view
     */

    function add()
    {
        // Load libraries
        //---------------

        $this->lang->load('certificate_manager');
        $this->load->library('External_Certificates');

        // Set validation rules
        //---------------------

        $file = $_FILES['cert_file'];
        if ($file && $file['name'])
            $_POST['cert_file'] = 'cert_file';

        $file = $_FILES['key_file'];
        if ($file && $file['name'])
            $_POST['key_file'] = 'key_file';

        $file = $_FILES['ca_file'];
        if ($file && $file['name'])
            $_POST['ca_file'] = 'ca_file';

        $this->form_validation->set_policy('name', 'certificate_manager/External_Certificates', 'validate_name', TRUE);
        $this->form_validation->set_policy('cert_file', 'certificate_manager/External_Certificates', 'validate_certificate_file', TRUE);
        $this->form_validation->set_policy('key_file', 'certificate_manager/External_Certificates', 'validate_key_file', TRUE);
        $this->form_validation->set_policy('ca_file', 'certificate_manager/External_Certificates', 'validate_ca_file', FALSE);
        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if (($this->input->post('submit') && $form_ok)) {
            try {
                $this->external_certificates->add(
                    $this->input->post('name'),
                    $_FILES['cert_file']['tmp_name'],
                    $_FILES['key_file']['tmp_name'],
                    $_FILES['ca_file']['tmp_name']
                );

                $this->page->set_status_added();

                redirect('/certificate_manager/external');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load the view
        //--------------

        $this->page->view_form('external_add', $data, lang('certificate_manager_app_name'));
    }

    /**
     * Delete view.
     *
     * @param string $name certificate basename
     *
     * @return view
     */

    function delete($name = NULL)
    {
        $confirm_uri = '/app/certificate_manager/external/destroy/' . $name;
        $cancel_uri = '/app/certificate_manager/external';
        $items = array($name);

        $this->page->view_confirm_delete($confirm_uri, $cancel_uri, $items);
    }

    /**
     * Remove view.
     *
     * @param string $name certificate basename
     *
     * @return view
     */

    function destroy($name)
    {
        // Load libraries
        //---------------

        $this->load->library('External_Certificates');

        // Handle form submit
        //-------------------

        try {
            $this->external_certificates->delete($name);
            $this->page->set_status_deleted();

            redirect('/certificate_manager');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }
}
