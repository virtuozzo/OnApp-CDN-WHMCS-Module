<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('html_errors', 1);
@date_default_timezone_set('UTC');
@ini_set( 'memory_limit', '512M' );
@ini_set( 'max_execution_time', 0 );
@set_time_limit( 0 );

define("ROOT", realpath( dirname(__FILE__) .'/../../../../' ). '/' );

require_once ROOT . "dbconnect.php";
require_once ROOT . "includes/functions.php";
require_once ROOT . "includes/clientareafunctions.php";
require_once ROOT . "includes/wrapper/OnAppInit.php";

if ( $argv[1] == '--fullupdate'  ) {
    new Cron_Job( true );
    
} else {
    new Cron_Job();
}

class Cron_Job {
    private $first_time = false;
    private $sql_file;
    private $cron_query = "
        SELECT
            h.userid,
            h.domain,
            c.email               as whmcsclientemail,
            s.hostname,
            s.ipaddress,
            s.id                  as serverid,
            onappc.onapp_user_id,
            onappc.email          as username,
            onappc.password,
            h.id                  as hostingid,
            curr.rate             as currency_rate,
            p.configoption6       as price
        FROM
            tblservers as s
        LEFT JOIN
            tblhosting as h
            ON h.server = s.id
        LEFT JOIN
            tblonappcdnclients as onappc
            ON onappc.service_id = h.id
        LEFT JOIN
            tblproducts as p
            ON h.packageid = p.id
        LEFT JOIN
            tblclients as c
            ON h.userid = c.id
        LEFT JOIN
            tblcurrencies as curr
            ON curr.id = c.currency
        WHERE
            s.type = 'onappcdn' AND
            onappc.onapp_user_id != ''
    "; 
    
    private $max_stat_time_query = "SELECT MAX(stat_time) as stat_time FROM `tblonappcdn_billing`";
    
    /**
     * Class construct
     * 
     * @param boolean $full_update  
     */
    public function __construct( $full_update = false ) {
        
        $this->first_time = $full_update;
        $this->sql_file = dirname( __FILE__ ) . '/billing.sql';
        $this->bootstrap();
        $this->run_cron();
    }
    
    /**
     * Cron bootstrap
     * 
     * @return void
     */
    private function bootstrap(){
        $bandwidth_table_exists = ( boolean )mysql_num_rows ( full_query("SHOW TABLES LIKE 'tblonappcdn_bandwidth'") );
        $billing_table_exists   = ( boolean )mysql_num_rows ( full_query("SHOW TABLES LIKE 'tblonappcdn_billing'") );

        if ( $bandwidth_table_exists || ! $billing_table_exists ) {

            full_query('DROP TABLE IF EXISTS tblonappcdn_bandwidth');

            if( file_exists( $this->sql_file ) ) {
                $this->runSQLFromFile( $this->sql_file );
            } else {
                $this->error( 'Module Install Error:File not found ' . $this->sql_file );
            }

            $this->first_time = true;
        }        
    }
    
    /**
     * Run sql form the file
     * 
     * @param string $path 
     */
    private function runSQLFromFile( $path ) {
        $sql  = file_get_contents( $path );
        $sql  = explode( PHP_EOL . PHP_EOL, $sql );

        foreach( $sql as $qry ) {
            full_query( $qry );
        }
    }    

    /**
     * Run cron
     * 
     * @return void 
     */
    private function run_cron(){
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime( $today ) + 86400 );
        
        $result = full_query( $this->cron_query );
        
        if ( ! $result ){
            $this->error('Main query error: ' . mysql_error() );
        }
        
        if ( mysql_num_rows($result) < 1 ) {
            $this->error( 'No active CDN resources exiting..' );
        }
        
        while ( $row = mysql_fetch_assoc( $result ) ) {
            
            $this->debug('****************************************************** H O S T I N G   L I N E ********************************************'); 
            
            $onapp = new OnApp_Factory(
                ( $row['hostname'] ) ? $row['hostname'] : $row['ipaddress'],
                $row['username'],
                $row['password']
            ); 
            
            if ( $onapp->getErrorsAsArray() ) {
                $this->debug( 'OnApp Login Error' . PHP_EOL . $onapp->getErrorsAsString( ) );
                continue;
            }            
            
            $_resource  = $onapp->factory('CDNResource', true );
            $_resources = $_resource->getList( );
            
            $resources = array();
            foreach( $_resources as $resource ){
                if( $resource->_user_id == $row['onapp_user_id'] ){
                    $resources[] = $resource;
                }
            }

            if (  $_resource->getErrorsAsArray() ) {
                $this->debug( 'Error Loading OnApp_CDNResource Object '  . PHP_EOL . $_resource->getErrorsAsString() );
                continue;
            }

            if ( count( $resources ) < 1 ) {
                $this->debug('This user have no CDN Resources. Skipping' . PHP_EOL );
                continue;
            }
            
            foreach ( $resources as $resource ) {
                
                $bs = $onapp->factory('CDNResource_BillingStatistic', true );

                $this->debug( $this->max_stat_time_query );
                $max_result = full_query( $this->max_stat_time_query );
                
                if ( ! $max_result ) {
                    $this->debug( 'Max Result Query Error: ' . mysql_error() );
                    continue;
                } elseif ( mysql_num_rows($max_result) < 1 ){
                    $this->debug('Billing Table is Empty, launch fullupdate');
                    $this->first_time = true;
                } else { 
                    $max_row = mysql_fetch_assoc( $max_result );
                    $this->debug( print_r( $max_row['stat_time'] ) );
                }               
                
                if ( $this->first_time ){
                    $_bs = $bs->getList( $resource->_id, array( 'per_page' => 'all' ) );
                } else {
                   
                    $_bs = $bs->getList( 
                            $resource->_id, 
                            array( 
                                'per_page' => 'all',
                                'period[startdate]' => date('Y-m-d H:i:s', strtotime( $max_row['stat_time'] ) - 7200 ),
                                'period[enddate]'   => $tomorrow,
                            ) );
                }
                
                $this->debug( print_r($bs->getResponse()) );

                foreach ( $_bs as $_obj ) {
                    $replace_query = "
                        REPLACE INTO
                            tblonappcdn_billing(
                                cost,
                                edge_group_id,
                                edge_group_label,
                                stat_time,
                                traffic,
                                cdn_resource_id,
                                hosting_id,
                                currency_rate
                            )
                            VALUES (
                                '$_obj->_cost',
                                $_obj->_edge_group_id,
                                '$_obj->_edge_group_label',
                                '$_obj->_stat_time',
                                '$_obj->_value',
                                $resource->_id,
                                $row[hostingid],
                                $row[currency_rate]
                            )
                    ";
                    
                    $this->debug( $replace_query );
                    
                    $replace_result = full_query( $replace_query );
                    
                    if ( ! $replace_result ) {
                        $this->debug( 'Replace error: ' . mysql_error());
                    }
                }                 
            }
        }
    }

    /**
     * Error message and exit 
     * 
     * @param string $message 
     */
    private function error( $message ) {
        echo PHP_EOL . $message . PHP_EOL ;
        exit();
    }
    
    /**
     * Debug message
     * 
     * @param string $message 
     */
    private function debug( $message ){
        echo PHP_EOL . $message . PHP_EOL . PHP_EOL;
    }
    
}
