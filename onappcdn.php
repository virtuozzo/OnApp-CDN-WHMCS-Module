<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

define("CLIENTAREA",true);

if ( ! defined('ROOTPATH') ) define( 'ROOTPATH', realpath( dirname(__FILE__) ) );
if ( ! defined('DS') ) define('DS', DIRECTORY_SEPARATOR );
if ( ! defined('ONAPPCDN_FILE_NAME') ) define('ONAPPCDN_FILE_NAME', basename(__FILE__) );
if ( ! defined('ONAPPCDN_DIR') ) define('ONAPPCDN_DIR',  ROOTPATH . DS . 'modules' .DS. 'servers' .DS. 'onappcdn' .DS   );

require_once ROOTPATH .DS. 'dbconnect.php';
require_once ROOTPATH .DS. 'includes' .DS.'functions.php';
require_once ROOTPATH .DS. 'includes' .DS. 'clientareafunctions.php';

if ( file_exists( ONAPPCDN_DIR . 'class_onappcdn.php' ) ) {
    require_once ONAPPCDN_DIR  . 'class_onappcdn.php';
}
else {
    exit('CDN module is not installed correctly');
}

$page   = OnAppCDN::get_value( 'page' );
$action = OnAppCDN::get_value( 'action' );

$page   = ( $page )   ? $page    : 'default';
$action = ( $action ) ? $action  : 'show';

$pages = array(
    'advanced_details'     =>  'AdvancedDetails',
    'bandwidth_statistics' =>  'BandwidthStatistics',
    'billing_statistics'   =>  'BillingStatistics',
    'prefetch'             =>  'Prefetch',
    'purge'                =>  'Purge',
    'resources'            =>  'Resources',
    'settings'             =>  'Settings',
);

$actions = array(
    'default'              => array('create'),
    'advanced_details'     => array(),
    'bandwidth_statistics' => array(),
    'billing_statistics'   => array(),
    'prefetch'             => array('prefetch'),
    'purge'                => array('purge'),
    'resources'            => array('enable', 'edit', 'delete', 'add'),
    'settings'             => array(),
);

if ( ! in_array( $action, $actions[$page] ) && $action != 'show' ) {
    exit('Not registered action');
}

$name = array_key_exists($page, $pages) ? 'OnAppCDN'. $pages[$page] : 'OnAppCDNDefault';

require_once ONAPPCDN_DIR. 'controllers' .DS. $name . '.php';

$class = new $name;
$class->runAction($action);