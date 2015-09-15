<?php

/**
 * External certificates library.
 *
 * @category   apps
 * @package    certificate-manager
 * @subpackage libraries
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

use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\certificate_manager\External_Certificates as External_Certificates;

clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Shell');
clearos_load_library('certificate_manager/External_Certificates');

// Exceptions
//-----------

use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * External certificates library.
 *
 * @category   apps
 * @package    certificate-manager
 * @subpackage libraries
 * @author     Roman Kosnar <kosnar@apeko.cz>
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2014 Roman Kosnar / APEKO GROUP s.r.o.
 * @copyright  2015 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 */

class External_Certificates
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const TYPE_CERTIFICATE = 'certificate';
    const TYPE_KEY = 'key';
    const TYPE_CA = 'ca';

    const COMMAND_OPENSSL = '/usr/bin/openssl';
    const PATH_CERTIFICATES = '/etc/clearos/certificate_manager.d';

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * External certificates constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Adds an external certificate.
     *
     * @param string $name basename of certificate
     * @param string $cert path to certificate file
     * @param string $key  path to key file
     * @param string $ca   path to $ca file
     *
     * @return void
     * @throws Engine_Exception
     */

    public function add($name, $cert, $key, $ca)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_name($cert));

        if (!empty($cert)) {
            $file = new File($cert);
            $file->copy_to(self::PATH_CERTIFICATES . '/' . $name . '.crt');
        }

        if (!empty($key)) {
            $file = new File($key);
            $file->copy_to(self::PATH_CERTIFICATES . '/' . $name . '.key');
        }

        if (!empty($ca)) {
            $file = new File($ca);
            $file->copy_to(self::PATH_CERTIFICATES . '/' . $name . '.ca');
        }
    }

    /**
     * Removes certificate.
     *
     * @param string $name basename of certificate
     *
     * @return string
     * @throws Engine_Exception
     */

    public function delete($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_name($name));

        // check if certificate is not used
        // FIXME: need a callback here

        $extensions = array('ca', 'crt', 'key');

        foreach ($extensions as $extension) {
            $file = new File(self::PATH_CERTIFICATES . '/' . $name . '.' . $extension);

            if ($file->exists())
                $file->delete();
        }
    }

    /**
     * Returns certificate information.
     *
     * @param string $cert certificate name
     *
     * @return array certificate information
     */

    public function get_cert($cert)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_name($cert));

        $folder = new Folder(self::PATH_CERTIFICATES);

        $certs = array();
        $all_files = $folder->get_listing();

        foreach ($all_files as $file) {
            $match = array();
            if (preg_match("%^($cert\\.([^\\.]+))$%", $file, $match))
                $certs[$match[2]] = $match[1];
        }

        return $certs;
    }

    /**
     * Returns list of available certificates.
     *
     * @return array list of available certs
     * @throws Engine_Exception
     */

    public function get_certs()
    {
        clearos_profile(__METHOD__, __LINE__);

        $certs = array();

        foreach ($this->_load_certificates() as $line) {
            $match = array();
            if (preg_match('%^(.+)\\.([^\\.]+)$%', $line, $match)) {
                $cert = $match[1];
                if (!array_key_exists($cert, $certs))
                    $certs[$cert] = array();

                $certs[$cert][$match[2]] = 1;
            }
        }

        ksort($certs);

        return $certs;
    }

    /**
     * Returns certificate details.
     *
     * @param string $cert certficate name
     *
     * @return string certificate details
     * @throws Engine_Exception
     */

    public function get_cert_details($cert)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_name($cert));
    
        $shell = new Shell();

        $cert = escapeshellarg($cert);

        $shell->execute(
            self::COMMAND_OPENSSL,
            "x509 -in '/etc/clearos/certificate_manager.d/$cert.crt' -text -noout",
            TRUE
        );

        $lines = $shell->get_output();

        return implode("\n", $lines);
    }

    /**
     * Returns list of available certificate names.
     *
     * @return array list of available certificate names
     * @throws Engine_Exception
     */

    public function get_cert_names()
    {
        clearos_profile(__METHOD__, __LINE__);

        $certs = array();

        foreach ($this->_load_certificates() as $line) {
            if (preg_match('%^(.+)\\.([^\\.]+)$%', $line, $match)) {
                $cert = $match[1];
                $certs[$cert] = $cert;
            }
        }

        ksort($certs);

        return $certs;
    }

    /**
     * Returns list of available server certificates.
     *
     * @return array list of available server certificates
     * @throws Engine_Exception
     */

    public function get_server_certificates()
    {
        clearos_profile(__METHOD__, __LINE__);

        $certs = array();

        foreach ($this->_load_certificates() as $line) {
            $match = array();
            if (preg_match('%^(.+)\\.([^\\.]+)$%', $line, $match)) {
                $cert = $match[1];
                if (!array_key_exists($cert, $certs))
                    $certs[$cert] = array();

                $filename = self::PATH_CERTIFICATES . '/' . $cert . '.' . $match['2'];

                if ($match[2] === 'ca')
                    $certs[$cert]['ca-filename'] = $filename;
                elseif ($match[2] === 'crt')
                    $certs[$cert]['certificate-filename'] = $filename;
                elseif ($match[2] === 'key')
                    $certs[$cert]['key-filename'] = $filename;
            }
        }

        ksort($certs);

        return $certs;
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validation routine for certificate name.
     *
     * @param string $name certificate name
     *
     * @return string error message if certificate name is invalid
     */

    public function validate_name($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->_check_cert_name($name))
            return lang('certificate_manager_name_invalid');

        $certs = $this->get_certs();

        foreach ($certs as $cert => $k) {
            if ($cert == $name)
                return;
        }

        return lang('certificate_manager_name_invalid');
    }

    /**
     * Validation routine for certificate file.
     *
     * @param string $certificate_file certificate file
     *
     * @return string error message if certificate file is invalid
     */

    public function validate_certificate_file($certificate_file)
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->_check_file(self::TYPE_CERTIFICATE, $certificate_file);
    }

    /**
     * Validation routine for key file.
     *
     * @param string $key_file key file
     *
     * @return string error message if key file is invalid
     */

    public function validate_key_file($key_file)
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->_check_file(self::TYPE_KEY, $key_file);
    }

    /**
     * Validation routine for certificate file.
     *
     * @param string $ca_file          CA file
     * @param string $certificate_file certificate file
     *
     * @return string error message if CA file is invalid
     */

    public function validate_ca_file($ca_file, $certificate_file)
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->_check_file(self::TYPE_CA, $ca_file, $certificate_file);
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Verifies given file and type.
     *
     * @param string $type         type of file
     * @param string $filename     file location
     * @param string $aux_filename auxiliary file for signature check
     *
     * @return string error message if file is invalid
     */

    private function _check_file($type, $filename, $aux_filename = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        $shell = new Shell();
        $options['validate_exit_code'] = FALSE;
        $filename = escapeshellarg($filename);

        if (!empty($aux_filename))
            $aux_filename = escapeshellarg($aux_filename);

        if ($type === self::TYPE_CERTIFICATE)
            $params = 'x509 -noout -modulus -in ' . $filename;
        elseif ($type === self::TYPE_KEY)
            $params = 'rsa -noout -modulus -in ' . $filename;
        elseif ($type === self::TYPE_CA)
            $params = 'verify -ignore_critical -CAfile ' . $filename . ' ' . $aux_filename;

        $exit_code =  $shell->execute(self::COMMAND_OPENSSL, $params, TRUE, $options);
        $lines = $shell->get_output();

        if ($type === self::TYPE_CA) {
            if ($exit_code != 0)
                return lang('certificate_manager_invalid_file_detected');
        } elseif (!preg_match('/^Modulus=[A-F0-9]+$/', $lines[0])) {
            return lang('certificate_manager_invalid_file_detected');
        }
    }

    /**
     * Verifies certificate nickname.
     *
     * @param string $name certificate nickname
     *
     * @return string error message if certificate nickname is invalid
     */

    private function _check_cert_name($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        return strlen($name) > 3 && preg_match("%^[a-zA-Z0-9\\-_]+(\\.[a-zA-Z0-9\\-_])*$%", $name);
    }

    /**
     * Loads certificate information
     *
     * @return array list of certificates
     */

    private function _load_certificates()
    {
        clearos_profile(__METHOD__, __LINE__);

        $folder = new Folder(self::PATH_CERTIFICATES);

        $files = array();
        $all_files = $folder->get_listing();

        foreach ($all_files as $file) {
            if (preg_match('/\.(ca|crt|key)$/', $file))
                $files[] = $file;
        }

        return $files;
    }
}
