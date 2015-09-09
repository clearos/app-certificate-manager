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

use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\certificate_manager\External_Certificates as External_Certificates;

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

    const CERT_CHECK = 'grep -h -e SSLCertificateFile -e ServerName /etc/httpd/conf.d/*';
    const DROP_CERT = 'rm -f /etc/clearos/certificate_manager.d/%s\.{crt,key,ca}';

    const CHECK_CRT = 'openssl x509 -noout -modulus -in ';
    const CHECK_KEY = 'openssl rsa -noout -modulus -in ';
    const CHECK_CA = 'openssl verify -ignore_critical -CAfile ';
    const CHECK_RES = '%^Modulus=[A-F0-9]+$%';

    const CERT_KEY = 'key';
    const CERT_CRT = 'crt';
    const CERT_CA = 'ca';

    const COMMAND_OPENSSL = '/usr/bin/openssl';
    const PATH_CERTIFICATES = '/etc/clearos/certificate_manager.d';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    private $certs = NULL;

    protected $configuration = NULL;
    protected $is_loaded = FALSE;

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
     * Returns certificate information.
     *
     * Format: array (ca -> certificate.ca, key -> certificate.key, crt -> certificate.crt)
     *
     * @return array certificate information
     */

    public function get_cert($cert)
    {
        clearos_profile(__METHOD__, __LINE__);

//      Validation_Exception::is_valid($this->validate_cert($cert));

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

        foreach ($this->_load_certs() as $line) {
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
     * @param string $cert_name
     * @return string certificate details
     * @throws Engine_Exception
     */

    public function get_cert_details($cert)
    {
        clearos_profile(__METHOD__, __LINE__);

  //      Validation_Exception::is_valid($this->validate_cert($cert));
    
        $shell = new Shell();

        $cert = escapeshellarg($cert);

        $shell->execute
            (self::COMMAND_OPENSSL,
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

        foreach ($this->_load_certs() as $line) {
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

        foreach ($this->_load_certs() as $line) {
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

    /**
     * Removes certificate.
     *
     * @param string $cert certificate name
     * @return string
     */

    public function remove_cert($cert)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Validation_Exception::is_valid($this->validate_cert($cert));

        // check if certificate is not used
        exec(External_Certificates::CERT_CHECK, $out);
        $crtRegex = "%".External_Certificates::PATH_CERTIFICATES."/".$cert.".".External_Certificates::CERT_CRT."%";
        $nameRegex = "%ServerName[ \t]+([^ \t]+)%";
        $name = 'default';
        foreach ($out as $n => $line) {
            $line=trim($line);
            if ($line[0] == '#')
                continue;
            if (preg_match($nameRegex, $line, $match)) {
                $name = $match[1];
            } else if (preg_match($crtRegex, $line)) {
                $err[] = sprintf(lang('certificate_manager_fail_cert_use'), $cert, $name);
                return $err;
            }
        }

        // it is possible to safetly remove certificate
        $env = new Shell();
        $env->execute(sprintf(External_Certificates::DROP_CERT, $cert), NULL, TRUE);
        $lines = $env->get_output();
        return implode("\n", $lines);
    }

    public static function get_cert_CA($cert)
    {
        clearos_profile(__METHOD__, __LINE__);

        return External_Certificates::_get_cert_part($cert, External_Certificates::CERT_CA);
    }

    public static function get_cert_CRT($cert)
    {
        clearos_profile(__METHOD__, __LINE__);

        return External_Certificates::_get_cert_part($cert, External_Certificates::CERT_CRT);
    }

    public static function get_cert_KEY($cert)
    {
        clearos_profile(__METHOD__, __LINE__);

        return External_Certificates::_get_cert_part($cert, External_Certificates::CERT_KEY);
    }

    public static function update($input)
    {
        clearos_profile(__METHOD__, __LINE__);

        $name = $input->post('name');
        $env = new Shell();
        $env->execute("cp -f ".$_FILES['cert_file']['tmp_name']." ".External_Certificates::PATH_CERTIFICATES."/$name.crt", NULL, TRUE);
        $env->execute("cp -f ".$_FILES['key_file']['tmp_name']." ".External_Certificates::PATH_CERTIFICATES."/$name.key", NULL, TRUE);
        if ($_POST['ca_file']) {
            $env->execute("cp -f ".$_FILES['ca_file']['tmp_name']." ".External_Certificates::PATH_CERTIFICATES."/$name.ca", NULL, TRUE);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    public function validate_cert_name($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (External_Certificates::_check_cert_name($name)) {
            if (is_null($this->certs)) {
                $this->certs = External_Certificates::get_certs();
            }
            foreach ($this->certs as $cert => $k) {
                if ($cert == $name) {
                    return lang('certificate_manager_fail_cert_name');
                }
            }
        } else {
            return lang('certificate_manager_fail_name_invalid');
        }
    }

    /**
     * Validation routine for country.
     *
     * @param string $country country
     *
     * @return string error message if country is invalid
     */

    public function validate_crt_file($cert_file)
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->_check_cert(External_Certificates::CHECK_CRT, $_FILES[$cert_file]['tmp_name'], 'CRT');
    }

    public function validate_key_file($key_file)
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->_check_cert(External_Certificates::CHECK_KEY, $_FILES[$key_file]['tmp_name'], 'KEY');
    }

    public function validate_ca_file($ca_file)
    {
        clearos_profile(__METHOD__, __LINE__);

        exec(External_Certificates::CHECK_CA.$_FILES[$ca_file]['tmp_name'].' '.$_FILES['cert_file']['tmp_name'], $out);
        foreach ($out as $n => $line) {
            if (preg_match("/^error (.*)$/", $line, $match)) {
                return lang('certificate_manager_fail_file_CA').': '.$match[1];
            }
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    private function _check_cert($cmd, $cert, $type)
    {
        clearos_profile(__METHOD__, __LINE__);

        $out = exec($cmd.$cert." 2>&1 ");
        if (!preg_match(External_Certificates::CHECK_RES, $out)) {
            return lang('certificate_manager_fail_file'.$type);
        }
        if (!is_null($_POST['_cert_data_'])) {
            if ($_POST['_cert_data_'] != $out) {
                return lang('certificate_manager_fail_cert_match');
            }
        } else {
            $_POST['_cert_data_'] = $out;
        }
    }

    private static function _check_cert_name($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        return strlen($name) > 3 && preg_match("%^[a-zA-Z0-9\\-_]+(\\.[a-zA-Z0-9\\-_])*$%", $name);
    }

    private static function _get_cert_part($cert, $part)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($cert != NULL && is_array($cert))
            return $cert[$part];
    }

    private function _load_certs()
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
