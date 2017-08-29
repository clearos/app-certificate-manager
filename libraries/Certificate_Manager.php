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
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\certificate_manager\External_Certificates as External_Certificates;
use \clearos\apps\certificate_manager\SSL as SSL;

clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
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
    const PATH_STATE = '/var/clearos/certificate_manager/state';
    const API_VERSION = 1;

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

        // Let's Encrypt
        //--------------

        if (clearos_load_library('lets_encrypt/Lets_Encrypt')) {
            $lets = new  \clearos\apps\lets_encrypt\Lets_Encrypt();
            $lets_list = $lets->get_certificate_files();

            $list = array_merge($list, $lets_list);
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

        // Let's Encrypt
        //--------------

        if (clearos_load_library('lets_encrypt/Lets_Encrypt')) {
            $lets = new  \clearos\apps\lets_encrypt\Lets_Encrypt();
            $lets_list = $lets->get_certificates();

            foreach ($lets_list as $basename => $details)
                $list[$basename] = lang('certificate_manager_lets_encrypt') . ' - ' . $basename;
        }

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
     * Returns certificate registration use from state file.
     *
     * @param string $app_name app name
     * @param string $app_key app key (e.g. virtual host)
     *
     * @return void
     * @throws Engine_Exception
     */

    public function get_registered_certificate($app_name, $app_key)
    {
        clearos_profile(__METHOD__, __LINE__);

        $state = $this->get_state();

        foreach ($state as $cert => $list) {
            foreach ($list as $details) {
                if (($details['app_name'] == $app_name) && ($details['app_key'] == $app_key))
                    return $cert;
            }
        }

        return '';
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

        $folder = new Folder(self::PATH_STATE);
        $listing = $folder->get_listing();

        foreach ($listing as $config) {
            if (!preg_match('/\.conf$/', $config))
                continue;

            $file = new File(self::PATH_STATE . '/' . $config);
            $line = $file->get_contents_as_array();

            $app_state = json_decode($line[0]);
            $app_name = preg_replace('/\.conf$/', '', $config);

            foreach ($app_state->certs as $app_key => $certificate) {
                $item['app_name'] = $app_name;
                $item['app_description'] = $app_state->app_description;
                $item['app_key'] = $app_key;
                $state[$certificate][] = $item;
            }
        }

        if ($cert)
            $retval = $state[$cert];
        else
            $retval = $state;

        return $retval;
    }

    /**
     * Registers version.
     *
     * The base webconfig app needs to know the capabilities of this
     * class in order to keep things sane (otherwise, we'll have to
     * add a circular dependency:
     * - app-base required app-certificate-manager
     * - app-certificate-manager required app-base
     *
     * @return string version
     * @throws Engine_Exception
     */

    public function get_api_version()
    {
        clearos_profile(__METHOD__, __LINE__);

        return self::API_VERSION;
    }

    /**
     * Registers a bunch of certificates at once.
     *
     * @param array $certs certificate list
     * @param string $app_name app basename
     * @param string $app_description app description
     *
     * @return void
     * @throws Engine_Exception
     */

    public function register($certs, $app_name, $app_description)
    {
        clearos_profile(__METHOD__, __LINE__);

        $current['app_description'] = $app_description;
        $current['certs'] = $certs;

        $file = new File(self::PATH_STATE . '/' . $app_name . '.conf');

        if ($file->exists())
            $file->delete();

        $file->create('root', 'root', '0644');
        $file->add_lines(json_encode($current));
    }
}
