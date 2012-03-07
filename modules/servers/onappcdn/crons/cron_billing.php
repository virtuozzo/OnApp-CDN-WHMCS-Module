<?php
echo 'CDN Billing CronJob Start', PHP_EOL .PHP_EOL . PHP_EOL;
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

$query = "
    SELECT
        h.userid,
        h.domain,
        c.email               as whmcsclientemail,
        c.taxexempt,
        c.state,
        c.country,
        h.paymentmethod,
        curr.code             as currencycode,
        curr.prefix           as currencyprefix,
        curr.rate             as currencyrate,
        curr.default          as currencydefault,
        s.hostname,
        s.secure,
        onappc.onapp_user_id,
        s.username,
        s.password,
        s.ipaddress,
        h.id                 as hostingid
    FROM
        tblservers as s
    LEFT JOIN
        tblhosting as h
        ON h.server = s.id
    LEFT JOIN
        tblonappcdnclients as onappc
        ON onappc.service_id = h.id
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

$result = full_query( $query );

if ( ! $result || mysql_num_rows( $result ) < 1 ) {
    die( '******** Cron Failed MySQL error or NUL rows result ********* '
        . PHP_EOL . mysql_error() . PHP_EOL . '**************************' . PHP_EOL );
}

$duedate = $today = date( 'Y-m-d H:i:s' );

while ( $row = mysql_fetch_assoc( $result ) ) {

    if ( ! $onapp ) {
        $onapp = new OnApp_Factory(
            ( $row['hostname'] ) ? $row['hostname'] : $row['ipaddress'],
            $row['username'],
            decrypt( $row['password'])
        );

        if ( $onapp->getErrorsAsArray() ) {
            print_r( $onapp->getErrorsAsArray() );
            echo PHP_EOL;
            die('OnApp Login Error');
        }
    }

    $users = $onapp->factory('User', true );
    $user = $users->load( $row['onapp_user_id'] );

    if ( $users->getErrorsAsArray() ) {
        echo 'Error Loading OnApp_User Object '  . PHP_EOL;
        echo 'OnApp User Id ' . $users->_id . PHP_EOL;
        print_r( $users->getErrorsAsArray() );
//        print_r( $users);
    }

    $client_amount_query =
        'SELECT
	        i.subtotal AS amount
	    FROM
            tblinvoices as i
	    WHERE
		    i.userid = ' . $row['userid'] . '
		    AND i.status = "Unpaid"
            AND i.notes = '. $row['hostingid'];
    
    $client_amount_result = full_query($client_amount_query);
    
    if ( ! $client_amount_result ) {
        echo '******** Cron Failed MySQL error *********' . PHP_EOL;
        die( mysql_error() );
    }

    $client_amount = 0;
    while ($amount = mysql_fetch_assoc($client_amount_result)) {
        $client_amount += $amount['amount'];
    }
    
    $amount_diff = $user->_outstanding_amount - $client_amount / $row['currencyrate'];
    $amount_diff = round( $amount_diff * $row['currencyrate'], 2 );

    echo '( Unpaid Amount in Base Currency )  '. $client_amount / $row['currencyrate'] . PHP_EOL;
    echo '( Currency Rate )                   '. $row['currencyrate'] . PHP_EOL;
    echo '( Unpaid Amount )                   '. $client_amount . PHP_EOL;
    echo '( Outstanding Amount )              '. $user->_outstanding_amount . PHP_EOL;
    echo '( Billing Amount )                  '. $amount_diff . PHP_EOL;

    if ( $amount_diff > 1 ) {
// debug
        echo 'Generating Invoice' . PHP_EOL;

        $sql = 'SELECT username FROM tbladmins LIMIT 1';

        $res = full_query($sql);

        $admin = mysql_fetch_assoc($res);

        $taxed = empty($row['taxexempt']) && $CONFIG['TaxEnabled'];

        if ($taxed) {
// debug
            echo 'taxed invoice' . PHP_EOL;
            $taxrate = getTaxRate(1, $row['state'], $row['country']);
            $taxrate = $taxrate['rate'];
        } else {
            $taxrate = '';
        }

        $data = array(
            'userid'           => $row['userid'],
            'date'             => $today,
            'duedate'          => $duedate,
            'paymentmethod'    => $row['paymentmethod'],
            'taxrate'          => $taxrate,
            'itemdescription1' => 'CDN Service Usage',
            'itemamount1'      => $amount_diff,
            'itemtaxed1'       => $taxed,
            'notes'            => $row['hostingid'],
        );

// debug
        print_r($data);
        echo PHP_EOL;

        $invoice = localAPI('CreateInvoice', $data, $admin);

        if ($invoice['result'] != 'success') {
// debug
            echo 'Generating Invoice Error ' . PHP_EOL . $invoice['result'] . PHP_EOL;
        }
        else {
// debug
            echo 'Invoice was Generated Successfully' . PHP_EOL;
        }
    }

// debug
//    $row['password'] = decrypt( $row['password'] );
    print_r($row);
    echo '***************************'  . PHP_EOL;
}

echo 'CDN Billing CronJob was Finished Successfully', PHP_EOL;
