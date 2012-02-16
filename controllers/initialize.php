<?php

/**
 * Certificate authority initialize controller.
 *
 * @category   Apps
 * @package    Certificate_Manager
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \Exception as Exception;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Certificate authority initialize controller.
 *
 * @category   Apps
 * @package    Certificate_Manager
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/certificate_manager/
 */

class Initialize extends ClearOS_Controller
{
    /**
     * Initalize controller.
     *
     * @return view
     */

    function index()
    {
        $this->view();
    }

    /**
     * Organization edit view.
     *
     * @return view
     */

    function edit()
    {
        $this->_view_edit('edit');
    }

    /**
     * Organization view view.
     *
     * @return view
     */

    function view()
    {
        $this->_view_edit('view');
    }

    /**
     * Organization view/edit form.
     *
     * @param string $form_mode form mode
     *
     * @return view
     */

    function _view_edit($form_mode)
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
        $this->form_validation->set_policy('unit', 'certificate_manager/SSL', 'validate_unit');
        $this->form_validation->set_policy('city', 'certificate_manager/SSL', 'validate_city', TRUE);
        $this->form_validation->set_policy('region', 'certificate_manager/SSL', 'validate_region', TRUE);
        $this->form_validation->set_policy('country', 'certificate_manager/SSL', 'validate_country', TRUE);
        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if (($this->input->post('submit') && $form_ok)) {
            try {
                $this->ssl->initialize(
                    $this->input->post('hostname'),
                    $this->input->post('hostname'), // FIXME -- domain name
                    $this->input->post('organization'),
                    $this->input->post('unit'),
                    $this->input->post('city'),
                    $this->input->post('region'),
                    $this->input->post('country')
                );

                $this->page->set_status_updated();
                redirect('/certificate_manager');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load view data
        //---------------

        try {
            $data['form_mode'] = $form_mode;
            $data['organization'] = $this->ssl->get_default_organization();
            $data['unit'] = $this->ssl->get_default_unit();
            $data['city'] = $this->ssl->get_default_city();
            $data['region'] = $this->ssl->get_default_region();
            $data['country'] = $this->ssl->get_default_country();

            $data['countries'] = $this->country->get_list();
            $data['countries']['0'] = ' -- ' . lang('base_not_specified') . ' -- ';
            asort($data['countries']);
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $this->page->view_form('certificate_manager/initialize', $data, lang('certificate_manager_initalize_certificate_manager'));
    }
}
