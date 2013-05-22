<?php

/**
 * Accounts initialization check.
 *
 * @category   apps
 * @package    certificate-manager
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/accounts/
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

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Accounts initialization check.
 *
 * @category   apps
 * @package    certificate-manager
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/accounts/
 */

class Certificate_Status extends ClearOS_Controller
{
    /**
     * Default controller.
     *
     * @return view
     */

    function index()
    {
        if (! $this->is_initialized())
            $this->widget();
    }

    /**
     * Returns state of account system
     *
     * Some apps are not able to work when in Active Directory mode.  The
     * driver parameter can be passed to check for this type of incompatibility.
     *
     * @return boolean state of accounts driver
     */

    function is_initialized()
    {
        // Load libraries and grab status information
        //-------------------------------------------

        try {
            $this->load->library('certificate_manager/SSL');

            $system_cert_exists = $this->ssl->exists_system_certificate();
            $ca_exists = $this->ssl->exists_certificate_authority();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return FALSE;
        }

        if ($ca_exists && $system_cert_exists)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Status widget.
     *
     * @return view accounts status view
     */

    function widget()
    {
        // Load dependencies
        //------------------

        $this->lang->load('base');

        // Load views
        //-----------

        $this->page->view_form('certificate_manager/status', $data, lang('base_status'));
    }
}
