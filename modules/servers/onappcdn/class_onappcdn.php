<?php

require_once dirname( __FILE__ ).'/../../../includes/functions.php';

class OnAppCDN {

    private $salt   = "ec457d0a974c48d5685a7efa03d137dc8bbde7e3";
    private $hostname = 'whmcs.com';
    private $serviceid;
    private $created;
    private $server;
    private $user;

    public $error;

    function __construct($serviceid) {
        if( is_numeric($serviceid) )
            $this->serviceid = $serviceid;
        else
            die('$serviceid is not a number or a numeric string');
    }

    public function get_user() {
        if (! is_null($this->user) )
            return $this->user;

        $sql      = "select * from tblonappcdnclients where service_id = " . $this->serviceid;
        $resource = full_query( $sql );
        $user     = mysql_fetch_assoc( $resource );

        $this->user = $user;
        return $user;
    }

    private function getServer() {
        if (! is_null($this->server) )
            return $this->server;
        $sql = "SELECT
                tblservers.*
            FROM
                tblhosting
                LEFT JOIN tblproducts ON tblproducts.id = packageid
                LEFT JOIN tblservers ON tblproducts.configoption1 = tblservers.id
            WHERE
                tblhosting.id = " . $this->serviceid;
        $resource = full_query( $sql );
        $server   = mysql_fetch_assoc( $resource );


        if ( !is_null($server) ) {
            $server['address'] = $server['ipaddress'] != '' ? $server['ipaddress'] : $server['hostname'];

            if ($server["secure"] == "on")
                $server['address'] = 'https://'.$server['address'];

            $server['password'] = decrypt($server['password']);

            $this->server = $server;
            return $this->server;
        } else
            die( 'OnApp CDN server for service ' . $this->serviceid . ' not found.' );
    }

    public function create_user() {

        $server = $this->getServer();

        $service_id = $this->serviceid;

        $onapp = new OnApp_Factory(
            $server['address'], 
            $server['username'],
            $server['password']
        );

        if( ! $onapp->_is_auth ) {
            return "Can not login as '".$server['username']."' on '".$server['address'];
        } else if ( $onapp->getErrorsAsArray() ) {
            return implode("\n", $onapp->getErrorsAsArray());
        } else {
            $sql = "SELECT
                tblproducts.*
            FROM
                tblhosting
                LEFT JOIN tblproducts ON tblproducts.id = packageid
            WHERE
                tblhosting.id = " . $this->serviceid;

            $resource = full_query( $sql );
            $hosting   = mysql_fetch_assoc( $resource );

            if( ! $hosting )
                return "Can't execute SQL query \"$sql\"";

            $email    = 'cdnuser'.$this->serviceid.'@'.$this->hostname;
            $password = md5($this->serviceid . $this->salt . date('h-i-s, j-m-y') );

            $user = $onapp->factory( 'User', true );

            $user->_email           = $email;
            $user->_password        = $password;
            $user->_login           = $email;
            $user->_first_name      = 'WHMCS CDN User';
            $user->_last_name       = '#'.$this->serviceid;

            $user->_role_ids        = $hosting["configoption2"];
            $user->_user_group_id   = $hosting["configoption4"];
            $user->_billing_plan_id = $hosting["configoption3"];
            $user->_time_zone       = $hosting["configoption5"];

            $user->save();

            if( $user->error )
                return implode( "\n", $user->error );
            else {
                $user_id = $user->_id;

                $sql = "REPLACE tblonappcdnclients SET
                    service_id    = '$service_id' ,
                    server_id     = '".$server['id']."',
                    email         = '$email',
                    password      = '$password',
                    onapp_user_id = '$user_id'";

                if( ! full_query($sql) )
                    return "Can't execute SQL query \"$sql\"";
                else {
                    $this->created = true;
                    return 'success';
                }
            }
        }

        return 'Something went wrong';
    }

    public function delete_user() {
        $server = $this->getServer();

        $onapp = new OnApp_Factory(
            $server['address'],
            $server['username'],
            $server['password']
        );

        if( ! $onapp->_is_auth ) {
            return "Can not login as '".$server['username']."' on '".$server['address'];
        } else if ( $onapp->getErrorsAsArray() ) {
            return implode("\n", $onapp->getErrorsAsArray());
        } else {
            $user = $this->get_user();

            $onapp_user = $onapp->factory( 'User', true );

            $onapp_user->load($user["onapp_user_id"]);

            $onapp_user->delete();

            $onapp_user->delete();

            if( $onapp_user->error )
                return implode( "\n", $onapp_user->error );
            else {
                $sql = "DELETE FROM tblonappcdnclients WHERE service_id = " . $this->serviceid;

                if( ! full_query($sql) )
                    return "Can't execute SQL query \"$sql\"";
                else
                    return 'success';
            }
        }

        return 'Something went wrong';
    }

    public function suspend_user() {
        $server = $this->getServer();

        $onapp = new OnApp_Factory(
            $server['address'],
            $server['username'],
            $server['password']
        );

        if( ! $onapp->_is_auth ) {
            return "Can not login as '".$server['username']."' on '".$server['address'];
        } else if ( $onapp->getErrorsAsArray() ) {
            return implode("\n", $onapp->getErrorsAsArray());
        } else {
            $user = $this->get_user();

            $onapp_user = $onapp->factory( 'User', true );

            $onapp_user->load($user["onapp_user_id"]);

            if( $onapp_user->_obj->_status == 'suspended')
                return "User is already suspended";

            $onapp_user->suspend();

            if( $onapp_user->error )
                return implode( "\n", $onapp_user->error );
            else
                return 'success';
        }
        return 'Something went wrong';
    }

    public function unsuspend_user() {
        $server = $this->getServer();

        $onapp = new OnApp_Factory(
            $server['address'],
            $server['username'],
            $server['password']
        );

        if( ! $onapp->_is_auth ) {
            return "Can not login as '".$server['username']."' on '".$server['address'];
        } else if ( $onapp->getErrorsAsArray() ) {
            return implode("\n", $onapp->getErrorsAsArray());
        } else {
            $user = $this->get_user();

            $onapp_user = $onapp->factory( 'User', true );

            $onapp_user->load($user["onapp_user_id"]);

            if( $onapp_user->_obj->_status != 'suspended')
                return "User is not suspended";

            $onapp_user->activate_user();

            if( $onapp_user->error )
                return implode( "\n", $onapp_user->error );
            else
                return 'success';
        }
        return 'Something went wrong';
    }

/**
 * Get GET or POST value
 */
    public static function get_value($value) {
        return $_GET[$value] ? $_GET[$value] : ( $_POST[$value] ? $_POST[$value] : null );
    }

/**
 * Init OnApp PHP wrapper
 */
    public static function init_wrapper() {
        if ( ! defined('ONAPP_FILE_NAME') )
            define("ONAPP_FILE_NAME", "onapp.php");

        if ( ! defined('ONAPP_WRAPPER_INIT') )
            define('ONAPP_WRAPPER_INIT', dirname(__FILE__).'/../../../includes/wrapper/OnAppInit.php');

        if ( file_exists( ONAPP_WRAPPER_INIT ) )
            require_once ONAPP_WRAPPER_INIT;
    }

/**
 * Load $_LANG from language file
 */
    public static function loadcdn_language() {
        global $_LANG;
        $dir = dirname(__FILE__).'/lang/';

        if(! file_exists($dir))
           return;

        $dh = opendir ($dir);

        while (false !== $file2 = readdir ($dh)) {
            if (!is_dir ('' . 'lang/' . $file2) ) {
                $pieces = explode ('.', $file2);
                if ($pieces[1] == 'txt') {
                    $arrayoflanguagefiles[] = $pieces[0];
                    continue;
                }
                continue;
            }
        };

        closedir ($dh);

        $language = $_SESSION['Language'];

        if( ! in_array ($language, $arrayoflanguagefiles) )
            $language =  "English";

        if( file_exists( dirname(__FILE__) . "/lang/$language.txt" ) ) {
            ob_start ();
            include dirname(__FILE__) . "/lang/$language.txt";
            $templang = ob_get_contents ();
            ob_end_clean ();
            eval ($templang);
        }
    }

/**
 * Create base OnApp CDN module tables structure
 */
    public static function createTables() {
        global $_LANG, $whmcsmysql;

        define ("CREATE_TABLE_CDNCLIENTS",
'CREATE TABLE IF NOT EXISTS `tblonappcdnclients` (
`server_id` int( 11 ) NOT NULL,
`service_id` int( 11 ) NOT NULL,
`onapp_user_id` int( 11 ) NOT NULL,
`password` text NOT NULL,
`email` text NOT NULL,
PRIMARY KEY( `server_id`, `service_id` ),
KEY `client_id` ( `service_id` )
) ENGINE = InnoDB;');

        if ( ! full_query( CREATE_TABLE_CDNCLIENTS, $whmcsmysql ) )
            return array(
                "error" => sprintf($_LANG["onappcdnerrtablecreate"], 'onappcdnclients')
            );

        return;
    }

/**
 * Get list of onapp cdn servers
 */
    public static function getservers() {
        $product_id = OnAppCDN::get_value("id");
          
        $sql = "SELECT
    tblservers.*, 
    tblservergroupsrel.groupid,
    tblproducts.servergroup
FROM 
    tblservers 
    LEFT JOIN tblservergroupsrel ON 
        tblservers.id = tblservergroupsrel.serverid
    LEFT JOIN tblproducts ON
        tblproducts.id = $product_id 
WHERE
    tblservers.disabled = 0
    AND tblservers.type = 'onappcdn'";

        $sql_servers_result = full_query($sql);      

        $servers = array();
        while ( $server = mysql_fetch_assoc($sql_servers_result)) {
            if ( is_null($server['groupid']) )   
                $server['groupid'] = 0;
            $server['password'] = decrypt($server['password']);

            $servers[$server['id']] = $server;
        }

        return $servers;
    }


}
