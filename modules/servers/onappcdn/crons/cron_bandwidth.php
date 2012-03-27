<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
@date_default_timezone_set('UTC');
@ini_set( 'memory_limit', '512M' );
@ini_set( 'max_execution_time', 0 );
@set_time_limit( 0 );

define("ROOT", realpath( dirname(__FILE__) .'/../../../../' ). '/' );

require_once ROOT . "dbconnect.php";
require_once ROOT . "includes/functions.php";
require_once ROOT . "includes/clientareafunctions.php";
require_once ROOT . "includes/wrapper/OnAppInit.php";


if( file_exists( dirname( __FILE__ ) . '/bandwidth.sql' ) ) {
    runSQL();
}

$query = "
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
        p.overagesbwprice     as price
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

$result   = full_query( $query );
$today    = date( 'Y-m-d' );
$now      = date('Y-m-d H:i:s'); 

echo PHP_EOL . PHP_EOL . 'CDN Bandwidth CronJob Runs at ' . $now, ' (UTC)', PHP_EOL, PHP_EOL;

while ( $row = mysql_fetch_assoc( $result ) ) {
// debug
    echo '*********************************** HOSTING ACCOUNT LINE ******************************************************************' .PHP_EOL .PHP_EOL;
    print_r($row);
    echo PHP_EOL;

    $onapp = new OnApp_Factory(
        ( $row['hostname'] ) ? $row['hostname'] : $row['ipaddress'],
        $row['username'],
        $row['password']
    );

    if ($onapp->getErrorsAsArray()) {
        print_r($onapp->getErrorsAsArray());
        echo PHP_EOL;
        echo 'OnApp Login Error' . PHP_EOL;
        continue;
    }

    $_resource  = $onapp->factory('CDNResource', true );
    $resources = $_resource->getList( );

    if (  $_resource->getErrorsAsArray() ) {
// debug
        echo 'Error Loading OnApp_CDNResource Object '  . PHP_EOL;
        print_r( $_resource->getErrorsAsArray() );
        echo PHP_EOL;
        continue;
//        print_r( $resources);
    }

    if ( count( $resources ) < 1 ) {
// debug
        echo PHP_EOL . 'This user have no CDN Resources. Skipping' . PHP_EOL . PHP_EOL ;
        continue;
    }

    foreach ( $resources as $resource ) {
        $query = "
            SELECT
                *
            FROM
                tblonappcdn_bandwidth
            WHERE
                aflexi_resource_id = $resource->_aflexi_resource_id
            ORDER BY
                created_at
            DESC LIMIT 1
        ";

// debug
        echo $query . PHP_EOL;

        $result_bw = full_query( $query );

        $row_bw    = mysql_fetch_assoc( $result_bw );

        if ( ! $result_bw ) {
// debug
            echo 'ERROR selecting last statistics query' . mysql_error() . PHP_EOL;
            continue;
        }

        if ( ! $row_bw['hosting_id'] ) {
// debug ///////////////////////////////////////////////////////////////////////////////////////////
            echo 'No records about this resource in database yet' . PHP_EOL;
            
            onappcdn_update_bandwidth_statistics( '0000-00-00', $resource, $_bw, $row, $onapp );
        }
        else {
// debug //////////////////////////////////////////////////////////////////////////////////////////////////////////
            echo 'There are some records in database. Here is the last one' . PHP_EOL;
            print_r( $row_bw ); echo PHP_EOL;

            if ( $row_bw['created_at'] == $today ) {
// debug
                echo 'Cron was already running today. Updating todays bandwidth' . PHP_EOL;

                onappcdn_update_bandwidth_statistics( $today, $resource, $_bw, $row, $onapp );
            }
            else {
// debug
                echo 'It\'s the first time Cron is running today. Updating bandwidth from the last time till today' . PHP_EOL;

                onappcdn_update_bandwidth_statistics( $row_bw['created_at'], $resource, $_bw, $row, $onapp );

            }
        }
    }
}

function onappcdn_update_bandwidth_statistics( $start, $resource, $_bw, $row, $onapp ) {
    global      $today;
    $tomorrow = date('Y-m-d', strtotime( $today ) + 86400 );
    
    $_bw = $onapp->factory('CDNResource_Bandwidth');

// debug
    echo 'Geting data from OnApp:   ' . PHP_EOL;
    echo '( start )                 ' . $start . PHP_EOL;
    echo '( end )                   ' . $tomorrow . PHP_EOL;
    echo '( resource_id )           ' . $resource->_id . PHP_EOL;
    echo '( resource_aflexi_id )    ' . $resource->_aflexi_resource_id . PHP_EOL . PHP_EOL;

    $url_args = array(
        'start'         => $start,
        'end'           => $tomorrow,
        'resource_type' => 'resource',
        'resources[]'   => $resource->_aflexi_resource_id,
        'type'          => 'GB',
    );

    $bw = $_bw->getList($url_args);

    foreach ($bw as $stat) {
        $date       = substr($stat->_date, 0, 10);
        $non_cached = $stat->_non_cached;
        $cached     = $stat->_cached;
// debug
        echo '( non_cached )     ' . $non_cached . PHP_EOL;
        echo '( cached )         ' . $cached . PHP_EOL;
        echo '( created_at )     ' . $date . PHP_EOL;
        echo '( cdn_hostname )   ' . $resource->_cdn_hostname . PHP_EOL . PHP_EOL;

        $query = "
            REPLACE INTO
                tblonappcdn_bandwidth(
                    created_at,
                    hosting_id,
                    cached,
                    non_cached,
                    aflexi_resource_id,
                    cdn_hostname,
                    resource_id,
                    price,
                    currency_rate
                 )
                 VALUES (
                     '$date',
                      $row[hostingid],
                      $cached,
                      $non_cached,
                      $resource->_aflexi_resource_id,
                      '$resource->_cdn_hostname',
                      $resource->_id,
                      $row[price],
                      $row[currency_rate]
                 )
         ";
// debug
        echo $query . PHP_EOL;

        $update_result = full_query($query);

// debug
        if (!$update_result) {
            echo 'REPLACE failed ' . PHP_EOL . mysql_error() . PHP_EOL;
        }
// debug
        echo '( REPLACE Result ) ';  var_dump($update_result) . PHP_EOL;
    }
}

function runSQL() {
	$file = dirname( __FILE__ ) . '/bandwidth.sql';
	$sql  = file_get_contents( $file );
	$sql  = explode( PHP_EOL . PHP_EOL, $sql );

	foreach( $sql as $qry ) {
		full_query( $qry );
	}
}

echo 'CDN Billing CronJob was Finished Successfully', PHP_EOL;

