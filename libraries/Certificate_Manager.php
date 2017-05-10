<?php

/**
 * Certificate manager class.
 *
 * @category   apps
 * @package    certificate-manager
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/certificate_manager/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\certificate_manager;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('certificate_manager');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;
use \clearos\apps\certificate_manager\External_Certificates as External_Certificates;
use \clearos\apps\certificate_manager\SSL as SSL;

clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('certificate_manager/External_Certificates');
clearos_load_library('certificate_manager/SSL');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;

clearos_load_library('base/Engine_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Certificate manager class.
 *
 * @category   apps
 * @package    certificate-manager
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2006-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/certificate_manager/
 */

class Certificate_Manager extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const DEFAULT_CERT = 'sys-0-cert.pem';
    const FILE_STATE = '/var/clearos/certificate_manager/state';

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Certificate manager constructor.
     */

    public function __construct() 
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Returns a list of available certificates.
     *
     * @return array certificates
     */

    public function get_certificates()
    {
        clearos_profile(__METHOD__, __LINE__);

        $list = array();

        // External
        //---------

        $external_certificates = new External_Certificates();
        $list = $external_certificates->get_server_certificates();

        // Self-signed 
        //------------

        $ssl = new SSL();
        $self_signed = $ssl->get_certificates(SSL::CERT_TYPE_SERVER);

        foreach ($self_signed as $basename => $details) {
            // The hard-coded sys-0-key can be cleaned up if we ever support
            // multiple system certificates. 
            $list[$basename]['certificate-filename'] = SSL::PATH_SSL . '/' . $basename;
            $list[$basename]['ca-filename'] = SSL::FILE_CA_CRT;
            $list[$basename]['key-filename'] = SSL::PATH_SSL_PRIVATE . '/sys-0-key.pem';

        }

        return $list;
    }

    /**
     * Returns a list of available certificates.
     *
     * @return array list of available certificate
     */

    public function get_list()
    {
        clearos_profile(__METHOD__, __LINE__);

        $list = array();

        // External
        //---------

        $external_certificates = new External_Certificates();
        $external = $external_certificates->get_server_certificates();

        foreach ($external as $basename => $details) {
            $nickname = ($basename === '_default_') ? lang('base_default') : $basename;
            $list[$basename] = lang('certificate_manager_external') . ' - ' . $nickname;
        }

        // Self-signed 
        //------------

        $ssl = new SSL();
        $self_signed = $ssl->get_certificates(SSL::CERT_TYPE_SERVER);

        foreach ($self_signed as $basename => $details)
            $list[$basename] = lang('certificate_manager_self_signed') . ' - ' . $details['cert_description'];

        return $list;
    }

    /**
     * Returns certificate use from state file.
     *
     * @return void
     * @throws Engine_Exception
     */

    public function get_state($cert = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_STATE);

        if (!$file->exists())
            return [];

        $line = $file->get_contents_as_array();

        $raw_state = json_decode($line[0]);

        $state = [];

        foreach ($raw_state as $app_name => $payload) {
            foreach ($payload->certs as $nickname => $certificate) {
                $item['app_name'] = $app_name;
                $item['description'] = $payload->description;
                $item['nickname'] = $nickname;
                $state[$certificate][] = $item;
            }
/*
*/
        }

        if ($cert)
            $retval = $state[$cert];
        else
            $retval = $state;

        return $retval;
    }

    /**
     * Registers certificate use to state file.
     *
     * @param array $certs certificate list
     * @param string $app_name app basename
     * @param string $description app description
     *
     * @return void
     * @throws Engine_Exception
     */

    public function register($certs, $app_name, $description)
    {
        clearos_profile(__METHOD__, __LINE__);

        $current = $this->get_state();

        $current[$app_name]['description'] = $description;
        $current[$app_name]['certs'] = $certs;

        $file = new File(self::FILE_STATE);

        if ($file->exists())
            $file->delete();

        $file->create('root', 'root', '0644');
        $file->add_lines(json_encode($current));
    }
}
