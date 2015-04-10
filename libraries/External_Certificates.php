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

use \clearos\apps\base\Shell;
use \clearos\apps\base\Daemon;

clearos_load_library('base/Shell');
clearos_load_library('base/Daemon');
clearos_load_language('certificate_manager');

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
    const CERT_PLACE  = '/etc/clearos/certificate_manager.d';
    const LIST_CERTS = 'ls -1 /etc/clearos/certificate_manager.d | grep -E "\.(key|crt|ca)"';
    const GET_CERT = 'ls -1 /etc/clearos/certificate_manager.d | grep -E "%s\.(key|crt|ca)"';
    const DETAIL_CERT = 'openssl x509 -in /etc/clearos/certificate_manager.d/%s.crt -text -noout';
    const DROP_CERT = 'rm -f /etc/clearos/certificate_manager.d/%s\.{crt,key,ca}';

    const CHECK_CRT = 'openssl x509 -noout -modulus -in ';
    const CHECK_KEY = 'openssl rsa -noout -modulus -in ';
    const CHECK_CA = 'openssl verify -ignore_critical -CAfile ';
    const CHECK_RES = '%^Modulus=[A-F0-9]+$%';

    const CERT_DEF = '_default_';

    const CERT_KEY = 'key';
    const CERT_CRT = 'crt';
    const CERT_CA = 'ca';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    private $certs = null;

    protected $configuration = NULL;
    protected $is_loaded = FALSE;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * SSL constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Returns array of available certificates
     */
    public static function get_certs() {
        $out = array();
        foreach (External_Certificates::load_certs() as $n => $line) {
            if(preg_match('%^(.+)\\.([^\\.]+)$%', $line, $match)) {
                $cert = $match[1];
                if(is_null($out[$cert])) {
                    $out[$cert] = array();
                }
                $out[$cert][$match[2]] = 1;
            }
        }
        ksort($out);
        return $out;
    }

    /**
     * Returns array of available certificates
     */
    public static function get_certs_names() {
        $out = array();
        foreach (External_Certificates::load_certs() as $n => $line) {
            if(preg_match('%^(.+)\\.([^\\.]+)$%', $line, $match)) {
                $cert = $match[1];
                $out[$cert] = $cert;
            }
        }
        ksort($out);
        return array_merge($out, array(External_Certificates::CERT_DEF => lang('certificate_manager_default')));
    }

    private static function load_certs() {
        $env = new Shell();
        $env->execute(External_Certificates::LIST_CERTS, null, true);
        return $env->get_output();
    }

    /**
     * Returns certificate details
     * @param string $cert_name
     * @return string
     */
    public static function get_cert_details($cert) {
        if(External_Certificates::check_cert_name($cert)) {
            $env = new Shell();
            $env->execute(sprintf(External_Certificates::DETAIL_CERT, $cert), null, true);
            $lines = $env->get_output();
            return implode("\n", $lines);
        }
    }

    /**
     * Removes certificate with name $cert
     * @param string $cert_name
     * @return string
     */
    public static function remove_cert($cert) {
        if(External_Certificates::check_cert_name($cert)) {
            // could not remove default ClearOS certificate
            if($cert == External_Certificates::CERT_DEF) {
                $err[] = lang('certificate_manager_fail_cert_def');
                return $err;
            }
            // check if certificate is not used
            exec(External_Certificates::CERT_CHECK, $out);
            $crtRegex = "%".External_Certificates::CERT_PLACE."/".$cert.".".External_Certificates::CERT_CRT."%";
            $nameRegex = "%ServerName[ \t]+([^ \t]+)%";
            $name = 'default';
            foreach ($out as $n => $line) {
                $line=trim($line);
                if($line[0] == '#')
                    continue;
                if(preg_match($nameRegex, $line, $match)) {
                    $name = $match[1];
                } else if(preg_match($crtRegex, $line)) {
                    $err[] = sprintf(lang('certificate_manager_fail_cert_use'), $cert, $name);
                    return $err;
                }
            }
            // it is possible to safetly remove certificate
            $env = new Shell();
            $env->execute(sprintf(External_Certificates::DROP_CERT, $cert), null, true);
            $lines = $env->get_output();
            return implode("\n", $lines);
        }
    }

    /**
     * Returns array (ca -> certificate.ca, key -> certificate.key, crt -> certificate.crt)
     * where certificate is absolute file path
     * @return map
     */
    public static function get_cert($cert) {
        if(External_Certificates::check_cert_name($cert)) {
            $env = new Shell();
            $env->execute(sprintf(External_Certificates::GET_CERT, $cert), null, true);
            $lines = $env->get_output();
            $out = array();
            foreach ($lines as $n => $line) {
                if(preg_match('%^(.+\\.([^\\.]+))$%', $line, $match)) {
                    $out[$match[2]] = $match[1];
                }
            }
            return $out;
        }
    }

    public static function get_cert_CA($cert) {
        return External_Certificates::get_cert_part($cert, External_Certificates::CERT_CA);
    }

    public static function get_cert_CRT($cert) {
        return External_Certificates::get_cert_part($cert, External_Certificates::CERT_CRT);
    }

    public static function get_cert_KEY($cert) {
        return External_Certificates::get_cert_part($cert, External_Certificates::CERT_KEY);
    }

    private static function get_cert_part($cert, $part) {
        if($cert != null && is_array($cert)) {
            return $cert[$part];
        }
    }

    public static function update($input) {
        $name = $input->post('name');
        $env = new Shell();
        $env->execute("cp -f ".$_FILES['cert_file']['tmp_name']." ".External_Certificates::CERT_PLACE."/$name.crt", null, true);
        $env->execute("cp -f ".$_FILES['key_file']['tmp_name']." ".External_Certificates::CERT_PLACE."/$name.key", null, true);
        if($_POST['ca_file']) {
            $env->execute("cp -f ".$_FILES['ca_file']['tmp_name']." ".External_Certificates::CERT_PLACE."/$name.ca", null, true);
        }
    }

    private static function check_cert_name($name) {
        return strlen($name) > 3 && preg_match("%^[a-zA-Z0-9\\-_]+(\\.[a-zA-Z0-9\\-_])*$%", $name);
    }

    public function validate_cert_name($name) {
        clearos_profile(__METHOD__, __LINE__);
        if(External_Certificates::check_cert_name($name)) {
            if(is_null($this->certs)) {
                $this->certs = External_Certificates::get_certs();
            }
            foreach ($this->certs as $cert => $k) {
                if($cert == $name) {
                    return lang('certificate_manager_fail_cert_name');
                }
            }
        } else {
            return lang('certificate_manager_fail_name_invalid');
        }
    }

    private function check_cert($cmd, $cert, $type) {
        $out = exec($cmd.$cert." 2>&1 ");
        if(!preg_match(External_Certificates::CHECK_RES, $out)) {
            return lang('certificate_manager_fail_file'.$type);
        }
        if(!is_null($_POST['_cert_data_'])) {
            if($_POST['_cert_data_'] != $out) {
                return lang('certificate_manager_fail_cert_match');
            }
        } else {
            $_POST['_cert_data_'] = $out;
        }
    }

    public function validate_crt_file($cert_file) {
        clearos_profile(check_cert, __LINE__);
        return $this->check_cert(External_Certificates::CHECK_CRT, $_FILES[$cert_file]['tmp_name'], 'CRT');
    }

    public function validate_key_file($key_file) {
        clearos_profile(__METHOD__, __LINE__);
        return $this->check_cert(External_Certificates::CHECK_KEY, $_FILES[$key_file]['tmp_name'], 'KEY');
    }

    public function validate_ca_file($ca_file) {
        clearos_profile(__METHOD__, __LINE__);
        exec(External_Certificates::CHECK_CA.$_FILES[$ca_file]['tmp_name'].' '.$_FILES['cert_file']['tmp_name'], $out);
        foreach ($out as $n => $line) {
            if(preg_match("/^error (.*)$/", $line, $match)) {
                return lang('certificate_manager_fail_file_CA').': '.$match[1];
            }
        }
    }
}
?>
