<?php

/**
 * Certificate manager browser controller.
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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \Exception as Exception;
use \clearos\apps\certificate_manager\Certificate_Not_Found_Exception as Certificate_Not_Found_Exception;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Certificate manager browser controller.
 *
 * @category   apps
 * @package    certificate-manager
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/certificate_manager/
 */

class Browser extends ClearOS_Controller
{
    /**
     * Browser controller.
     *
     * @return view
     */

    function index()
    {
        $this->warning();
    }

    /**
     * Quick check to see if server is responding.
     *
     * @return string offset time
     */

    function check()
    {
        // Load dependencies
        //------------------

        $this->load->library('certificate_manager/SSL');

        // Gather info on certificate change
        //----------------------------------

        try {
            $attributes = $this->ssl->get_certificate_attributes('sys-0-cert.pem');

            $cert_time = strtotime($attributes['issued']);
            $diff_time = time() - $cert_time;

            // If the timestamp on sys-0-cert.pem is < 10 seconds wait, restart is in progress.
            // The ajax call will fail once webconfig is stopped and will only start to work
            // when everything is back up and running.
            
            if ($diff_time < 10)
                $data['wait'] = TRUE;
            else
                $data['wait'] = FALSE;

            $data['error_code'] = 0;
        } catch (Certificate_Not_Found_Exception $e) {
            $data['wait'] = TRUE;
            $data['error_code'] = 0;
        } catch (Exception $e) {
            // Send a green light if something goes wrong
            $data['wait'] = FALSE;
            $data['error_code'] = clearos_exception_code($e);
            $data['error_message'] = clearos_exception_message($e);
        }

        $this->output->set_header("Content-Type: application/json");
        $this->output->set_output(json_encode($data));
    }

    /**
     * Warning view.
     *
     * @return view
     */

    function warning()
    {
        // TODO: use user agent detection to provide appropriate screenshot
        $data['image'] = 'warning_firefox.png';

        if ($this->session->userdata('wizard')) {
            $form_type = 'wizard';
            $options = array();
        } else {
            $form_type = 'normal';
            $options['type'] = My_Page::TYPE_SPLASH;
        }


        $this->page->view_form('certificate_manager/warning', $data, lang('base_warning'), $options);
    }
}
