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

class Certificate extends ClearOS_Controller
{
    /**
     * Certificate controller
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

        // Load dependencies
        //------------------

        $this->lang->load('certificate_manager');
        $this->load->library('certificate_manager/SSL');

        // Load view data
        //---------------

        try {
            $data['certificates'] = $this->ssl->get_certificates();
            $ca_exists = $this->ssl->exists_certificate_authority();
            $is_slave = $this->ssl->is_slave();
        } catch (Engine_Engine_Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        if ($ca_exists)
            $this->page->view_form('certificate_manager/summary', $data, lang('certificate_manager_certificates'));
        else if ($is_slave)
            redirect('/certificate_manager/certificate/slave_info');
        else
            redirect('/certificate_manager/certificate/add/ca');
    }

    /**
     * Add view
     *
     * @param string $type type of certificate
     *
     * @return view
     */

    function add($type)
    {
        $this->_item('add', '', $type);
    }

    /**
     * Delete view.
     *
     * @param string $certificate certificate
     *
     * @return view
     */

    function delete($certificate)
    {
        $confirm_uri = '/app/certificate_manager/certificate/destroy/' . $certificate;
        $cancel_uri = '/app/certificate_manager/certificate';
        $items = array($certificate);

        $this->page->view_confirm_delete($confirm_uri, $cancel_uri, $items);
    }

    /**
     * Destroys view.
     *
     * @param string $certificate IP address
     *
     * @return view
     */

    function destroy($certificate = NULL)
    {
        // Load libraries
        //---------------

        $this->lang->load('certificate_manager');
        $this->load->library('certificate_manager/SSL');

        // Handle delete
        //--------------

        try {
            $this->ssl->delete_certificate($certificate);

            $this->page->set_status_deleted();
            redirect('/certificate_manager/certificate');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }

    /**
     * Downloads certificate to requesting client.
     *
     * @return string certificate
     */

    function download($certificate)
    {
        $this->_install_download('download', $certificate);
    }

    /**
     * Shows information when in slave mode.
     *
     * @return view
     */

    function slave_info()
    {
        // Run initialization
        //-------------------
        // TODO: change this to an event at some point -- to discuss

        try {
            $this->load->library('certificate_manager/SSL');
            $this->ssl->configure_master_slave();
        } catch (Engine_Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load view data
        //---------------

        $this->page->view_form('certificate_manager/slave_info', $data, lang('certificate_manager_certificates'));
    }

    /**
     * Installs certificate on requesting client.
     *
     * @return string certificate
     */

    function install($certificate)
    {
        $this->_install_download('install', $certificate);
    }

    /**
     * View view.
     *
     * @return view
     */

    function view($certificate)
    {
        $this->_item('view', $certificate);
    }

    /**
     * Common install/download method.
     *
     * @return string certificate
     */
    
    function _install_download($type, $certificate)
    {
        // Load dependencies
        //------------------

        $this->lang->load('certificate_manager');
        $this->load->library('certificate_manager/SSL');

        // Load view data
        //---------------

        try {
            $attributes = $this->ssl->get_certificate_attributes($certificate);
        } catch (Engine_Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load view
        //----------

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header("Content-Transfer-Encoding: binary");
        header('Content-Length: ' . $attributes['filesize']);

        if ($type === 'download') {
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=" . $certificate . ";");
        } else {
            if (! empty($attributes['pkcs12']))
                header("Content-Type: application/x-pkcs12-signature");
            else if (! empty($attributes['ca']))
                header("Content-Type: application/x-x509-ca-cert");
            else
                header("Content-Type: application/x-x509-user-cert");
        }

        echo $attributes['file_contents'];
    }

    /**
     * Common view/edit form.
     *
     * @param string $form_type   form type
     * @parma string $certificate certificate
     * @param string $type        certificate type
     *
     * @return view
     */

    function _item($form_type, $certificate, $type)
    {
        // Load dependencies
        //------------------

        $this->lang->load('organization');
        $this->lang->load('certificate_manager');
        $this->load->library('base/Country');
        $this->load->library('certificate_manager/SSL');

        // Set validation rules
        //---------------------
         
        $this->form_validation->set_policy('hostname', 'certificate_manager/SSL', 'validate_hostname', TRUE);
        $this->form_validation->set_policy('organization', 'certificate_manager/SSL', 'validate_organization', TRUE);
        $this->form_validation->set_policy('unit', 'certificate_manager/SSL', 'validate_unit', TRUE);
        $this->form_validation->set_policy('city', 'certificate_manager/SSL', 'validate_city', TRUE);
        $this->form_validation->set_policy('region', 'certificate_manager/SSL', 'validate_region', TRUE);
        $this->form_validation->set_policy('country', 'certificate_manager/SSL', 'validate_country', TRUE);
        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if (($this->input->post('hostname') && $form_ok)) {
            try {
                if ($type === 'ca') {
                    $this->ssl->initialize(
                        $this->input->post('hostname'),
                        $this->input->post('hostname'), // TODO -- domain name
                        $this->input->post('organization'),
                        $this->input->post('unit'),
                        $this->input->post('city'),
                        $this->input->post('region'),
                        $this->input->post('country')
                    );

                    $this->page->set_status_added();
                    redirect('/certificate_manager/browser/warning');
                } else {
                    $this->page->set_status_updated();
                    redirect('/certificate_manager/certificate');
                }
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load view data
        //---------------

        try {
            $data['form_type'] = $form_type;
            $data['certificate'] = $certificate;
            $data['wizard_mode'] = ($this->session->userdata('wizard')) ? TRUE : FALSE;

            if ($form_type === 'add') {
                $data['type'] = $type;
                $data['hostname'] = $this->ssl->get_default_hostname();
                $data['organization'] = $this->ssl->get_default_organization();
                $data['unit'] = $this->ssl->get_default_unit();
                $data['city'] = $this->ssl->get_default_city();
                $data['region'] = $this->ssl->get_default_region();
                $data['country'] = $this->ssl->get_default_country();
            } else {
                $attributes = $this->ssl->get_certificate_attributes($certificate);

                $data['type'] = $attributes['type'];
                $data['organization'] = $attributes['org_name'];
                $data['unit'] = $attributes['org_unit'];
                $data['city'] = $attributes['city'];
                $data['region'] = $attributes['region'];
                $data['country'] = $attributes['country'];
            }

            $data['types'] = array(
                'user' => lang('certificate_manager_certificate_authority'),
                'server' => lang('certificate_manager_certificate_authority'),
            );

            $data['countries'] = $this->country->get_list();
            $data['countries']['0'] = ' -- ' . lang('base_not_specified') . ' -- ';
            asort($data['countries']);
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $this->page->view_form('certificate_manager/item', $data, lang('certificate_manager_certificate'));
    }
}
