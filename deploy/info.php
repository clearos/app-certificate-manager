<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'certificate_manager';
$app['version'] = '1.6.7';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('certificate_manager_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('certificate_manager_app_name');
$app['category'] = lang('base_category_system');
$app['subcategory'] = lang('base_subcategory_security');

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////

$app['controllers']['certificate_manager']['title'] = $app['name'];
$app['controllers']['certificate']['title'] = lang('certificate_manager_certificates');
$app['controllers']['browser']['title'] = lang('certificate_manager_web_browser_warning');
$app['controllers']['policy']['title'] = lang('base_app_policy');

// Wizard extras
$app['controllers']['certificate_manager']['wizard_name'] = lang('certificate_manager_certificate_manager');
$app['controllers']['certificate_manager']['wizard_description'] = lang('certificate_manager_wizard_description');
$app['controllers']['certificate_manager']['inline_help'] = array(
    lang('certificate_manager_warning') => lang('certificate_manager_certificate_change_warning'),
    lang('certificate_manager_security_is_important') => lang('certificate_manager_app_description'),
);

$app['controllers']['certificate']['wizard_name'] = lang('certificate_manager_certificate_manager');
$app['controllers']['certificate']['wizard_description'] = lang('certificate_manager_wizard_description');
$app['controllers']['certificate']['inline_help'] = array(
    lang('certificate_manager_warning') => lang('certificate_manager_certificate_change_warning'),
    lang('certificate_manager_security_is_important') => lang('certificate_manager_app_description'),
);

$app['controllers']['browser']['wizard_name'] = lang('certificate_manager_web_browser_warning');
$app['controllers']['browser']['wizard_description'] = lang('certificate_manager_wizard_description');
$app['controllers']['browser']['inline_help'] = array(
    lang('certificate_manager_security_is_important') => lang('certificate_manager_app_description'),
);

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['requires'] = array(
    'app-accounts',
);

$app['core_requires'] = array(
    'app-base-core >= 1:1.4.15',
    'app-events-core >= 1:1.6.0',
    'app-network-core', 
    'app-organization-core',
    'app-mode-core',
    'csplugin-filesync',
    'openssl >= 1.0.0'
);

$app['core_file_manifest'] = array( 
    'filewatch-certificate-manager-event.conf'=> array('target' => '/etc/clearsync.d/filewatch-certificate-manager-event.conf'),
    'index.txt' => array(
        'target' => '/etc/pki/CA/index.txt',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
    'serial' => array(
        'target' => '/etc/pki/CA/serial',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
    'openssl.cnf' => array(
        'target' => '/etc/pki/CA/openssl.cnf',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
    'certificate_manager_event' => array(
        'target' => '/var/clearos/events/certificate_manager/certificate_manager',
        'mode' => '0755',
    ),
);

$app['core_directory_manifest'] = array(
    '/etc/clearos/certificate_manager.d' => array(),
    '/var/clearos/certificate_manager' => array(),
    '/var/clearos/certificate_manager/backup' => array(),
    '/var/clearos/events/certificate_manager' => array(),
);
