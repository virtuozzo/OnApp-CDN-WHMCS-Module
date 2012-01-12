<?php

class OnAppCDN {

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
 * Create base tables structure
 */

    public static function createTables() {
        global $_LANG, $whmcsmysql;

        define ("CREATE_TABLE_CDNCLIENTS",
"CREATE TABLE IF NOT EXISTS `tblonappcdnclients` (
  `service_id` int(11) NOT NULL,
  `onapp_user_id` int(11) NOT NULL,
  `password` text NOT NULL,
  `email` text NOT NULL,
  PRIMARY KEY (`service_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB;");


        if ( ! full_query( CREATE_TABLE_CDNCLIENTS, $whmcsmysql ) )
            return array(
                "error" => sprintf($_LANG["onappcdnerrtablecreate"], 'onappcdnclients')
            );

        return;
    }

/**
 * Get list of onapp cdn servers
 */
    public static function loadservers() {
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
