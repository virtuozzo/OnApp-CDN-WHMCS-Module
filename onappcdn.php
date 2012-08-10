<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
//error_reporting( E_ALL );
//ini_set( 'display_errors', 1 );

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
$page   = ( $page )   ? $page    : 'resources';

// Register pages
$pages = array(
    'advanced_details'           =>  'AdvancedDetails',
    'billing_statistics'         =>  'BillingStatistics',
    'total_billing_statistics'   =>  'TotalBillingStatistics',
    'prefetch'                   =>  'Prefetch',
    'purge'                      =>  'Purge',
    'resources'                  =>  'Resources',
    'details'                    =>  'Details',
    'default'                    =>  'Default',
    'error'                      =>  'Error',
);

$action = OnAppCDN::get_value( 'action' );
$action = ( $action ) ? $action  : 'show';

// Register actions
$actions = array(
    'default'                    => array('create'),
    'details'                    => array(),
    'advanced_details'           => array(),
    'billing_statistics'         => array(),
    'total_billing_statistics'   => array(),
    'prefetch'                   => array('prefetch'),
    'purge'                      => array('purge'),
    'resources'                  => array('enable', 'edit', 'delete', 'add', 'choose_resource_type'),
    'error'                      => array(),
);

if ( ! in_array( $action, $actions[$page] ) && $action != 'show' ) {
    exit('Invalid Token ( code : 1 )');               // Try to access not registred action
}

if ( array_key_exists($page, $pages) ) {
    $name = 'OnAppCDN'. $pages[$page];
}
else {
    die('Invalid Token ( code : 2 )');               // Try to access not registered controller
}

require_once ONAPPCDN_DIR. 'controllers' .DS. $name . '.php';

$class = new $name;

$user = $class->get_user();

if ( ! isset( $user['onapp_user_id'] ) && $name != 'OnAppCDNDefault' && $name != 'OnAppCDNError' ) {
    die('Invalid Token ( code : 3 )');                 // CDN User is not created yet
}

// Verify whether User can access service
if ( ! in_array( $class->getServiceId(), $class->getUserServisesIds() ) )
    die('Invalid Token ( code : 4 )');                // Try to access not own hosting account or not loggedin
  
// Verify whether User can access resource
$resource_id = OnAppCDN::get_value( 'resource_id' );

if ( ! is_null( $resource_id ) ) {
    $class->ifHaveAccessToResource( $resource_id );
}

$class->runAction($action);