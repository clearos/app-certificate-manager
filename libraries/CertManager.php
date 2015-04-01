<?php

/**
 * Certificates library.
 *
 * @category	Apps
 * @package		Certificates
 * @subpackage	libraries
 * @author		Roman Kosnar <kosnar@apeko.cz>
 * @copyright	2014 Roman Kosnar / APEKO GROUP s.r.o.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 */

namespace clearos\apps\certificate_manager;

use clearos\apps\certificate_manager\CertManager;
use \clearos\apps\base\Shell;
use \clearos\apps\base\Daemon;

clearos_load_library('base/Shell');
clearos_load_library('base/Daemon');
clearos_load_language('Certificates');

class CertManager {

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

	private $certs = null;

	/**
	 * Returns array of available certificates
	 */
	public static function getCerts() {
		$out = array();
		foreach (CertManager::loadCerts() as $n => $line) {
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
	public static function getCertsNames() {
		$out = array();
		foreach (CertManager::loadCerts() as $n => $line) {
			if(preg_match('%^(.+)\\.([^\\.]+)$%', $line, $match)) {
				$cert = $match[1];
				$out[$cert] = $cert;
			}
		}
		ksort($out);
		return array_merge($out, array(CertManager::CERT_DEF => lang('certificate_manager_default')));
	}

	private static function loadCerts() {
		$env = new Shell();
		$env->execute(CertManager::LIST_CERTS, null, true);
		return $env->get_output();
	}

	/**
	 * Returns certificate details
	 * @param string $cert_name
	 * @return string
	 */
	public static function certDetails($cert) {
		if(CertManager::check_cert_name($cert)) {
			$env = new Shell();
			$env->execute(sprintf(CertManager::DETAIL_CERT, $cert), null, true);
			$lines = $env->get_output();
			return implode("\n", $lines);
		}
	}

	/**
	 * Removes certificate with name $cert
	 * @param string $cert_name
	 * @return string
	 */
	public static function certRemove($cert) {
		if(CertManager::check_cert_name($cert)) {
			// could not remove default ClearOS certificate
			if($cert == CertManager::CERT_DEF) {
				$err[] = lang('certificate_manager_fail_certDef');
				return $err;
			}
			// check if certificate is not used
			exec(CertManager::CERT_CHECK, $out);
			$crtRegex = "%".CertManager::CERT_PLACE."/".$cert.".".CertManager::CERT_CRT."%";
			$nameRegex = "%ServerName[ \t]+([^ \t]+)%";
			$name = 'default';
			foreach ($out as $n => $line) {
				$line=trim($line);
				if($line[0] == '#')
					continue;
				if(preg_match($nameRegex, $line, $match)) {
					$name = $match[1];
				} else if(preg_match($crtRegex, $line)) {
					$err[] = sprintf(lang('certificate_manager_fail_certUse'), $cert, $name);
					return $err;
				}
			}
			// it is possible to safetly remove certificate
			$env = new Shell();
			$env->execute(sprintf(CertManager::DROP_CERT, $cert), null, true);
			$lines = $env->get_output();
			return implode("\n", $lines);
		}
	}

	/**
	 * Returns array (ca -> certificate.ca, key -> certificate.key, crt -> certificate.crt)
	 * where certificate is absolute file path
	 * @return map
	 */
	public static function getCert($cert) {
		if(CertManager::check_cert_name($cert)) {
			$env = new Shell();
			$env->execute(sprintf(CertManager::GET_CERT, $cert), null, true);
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

	public static function getCert_CA($cert) {
		return CertManager::getCertPart($cert, CertManager::CERT_CA);
	}

	public static function getCert_CRT($cert) {
		return CertManager::getCertPart($cert, CertManager::CERT_CRT);
	}

	public static function getCert_KEY($cert) {
		return CertManager::getCertPart($cert, CertManager::CERT_KEY);
	}

	private static function getCertPart($cert, $part) {
		if($cert != null && is_array($cert)) {
			return $cert[$part];
		}
	}

	public static function update($input) {
		$name = $input->post('name');
		$env = new Shell();
		$env->execute("cp -f ".$_FILES['certFile']['tmp_name']." ".CertManager::CERT_PLACE."/$name.crt", null, true);
		$env->execute("cp -f ".$_FILES['keyFile']['tmp_name']." ".CertManager::CERT_PLACE."/$name.key", null, true);
		if($_POST['caFile']) {
			$env->execute("cp -f ".$_FILES['caFile']['tmp_name']." ".CertManager::CERT_PLACE."/$name.ca", null, true);
		}
	}

	private static function check_cert_name($name) {
		return strlen($name) > 3 && preg_match("%^[a-zA-Z0-9\\-_]+(\\.[a-zA-Z0-9\\-_])*$%", $name);
	}

	public function validate_cert_name($name) {
		clearos_profile(__METHOD__, __LINE__);
		if(CertManager::check_cert_name($name)) {
			if(is_null($this->certs)) {
				$this->certs = CertManager::getCerts();
			}
			foreach ($this->certs as $cert => $k) {
				if($cert == $name) {
					return lang('certificate_manager_fail_certName');
				}
			}
		} else {
			return lang('certificate_manager_fail_nameInvalid');
		}
	}

	private function checkCert($cmd, $cert, $type) {
		$out = exec($cmd.$cert." 2>&1 ");
		if(!preg_match(CertManager::CHECK_RES, $out)) {
			return lang('certificate_manager_fail_file'.$type);
		}
		if(!is_null($_POST['_cert_data_'])) {
			if($_POST['_cert_data_'] != $out) {
				return lang('certificate_manager_fail_certMatch');
			}
		} else {
			$_POST['_cert_data_'] = $out;
		}
	}

	public function validate_crt_file($certFile) {
		clearos_profile(__METHOD__, __LINE__);
		return $this->checkCert(CertManager::CHECK_CRT, $_FILES[$certFile]['tmp_name'], 'CRT');
	}

	public function validate_key_file($keyFile) {
		clearos_profile(__METHOD__, __LINE__);
		return $this->checkCert(CertManager::CHECK_KEY, $_FILES[$keyFile]['tmp_name'], 'KEY');
	}

	public function validate_ca_file($caFile) {
		clearos_profile(__METHOD__, __LINE__);
		exec(CertManager::CHECK_CA.$_FILES[$caFile]['tmp_name'].' '.$_FILES[$certFile]['tmp_name'], $out);
		foreach ($out as $n => $line) {
			if(preg_match("/^error (.*)$/", $line, $match)) {
				return lang('certificate_manager_fail_fileCA').': '.$match[1];
			}
		}
	}
}
?>