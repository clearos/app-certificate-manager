<?php

/**
 * Certificate manager controller.
 *
 * @category   Apps
 * @package    Certificate_Manager
 * @subpackage Controllers
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
 * @category   Apps
 * @package    Certificate_Manager
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/certificate_manager/
 */

class Certificate extends ClearOS_Controller
{
    /**
     * CA controller
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->lang->load('certificate_manager');
        $this->load->library('certificate_manager/SSL');

        // Load view data
        //---------------

        try {
            $data['certificates'] = $this->ssl->get_certificates();
        } catch (Engine_Engine_Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $this->page->view_form('certificate_manager/summary', $data, lang('certificate_manager_certificates'));
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

    function edit($certificate)
    {
        // Load dependencies
        //------------------

        $this->lang->load('certificate_manager');
        $this->load->library('certificate_manager/SSL');

        // Handle form submit
        //-------------------

        if ($this->input->post('submit')) {
            try {
/*
                $this->pptpd->set_remote_ip($this->input->post('remote_ip'));
                $this->pptpd->set_local_ip($this->input->post('local_ip'));
                $this->pptpd->set_domain($this->input->post('domain'));
                $this->pptpd->set_wins_server($this->input->post('wins'));
                $this->pptpd->set_dns_server($this->input->post('dns'));
                $this->pptpd->reset(TRUE);
*/

                $this->page->set_status_updated();
            } catch (Engine_Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load view data
        //---------------

        try {
            $data['certificate'] = $certificate;
            $data['attributes'] = $this->ssl->get_certificate_attributes($certificate);
        } catch (Engine_Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $this->page->view_form('certificate_manager/certificate', $data, lang('certificate_manager_certificate'));
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
}
