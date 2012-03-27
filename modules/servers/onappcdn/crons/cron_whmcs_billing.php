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
        onappc.onapp_user_id,
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

echo PHP_EOL . PHP_EOL . 'CDN Billing CronJob Runs at ' . date('Y-m-d H:i:s'), ' (UTC)', PHP_EOL, PHP_EOL;

if ( ! $result || mysql_num_rows( $result ) < 1 ) {
    die( '******** Cron Failed MySQL error or NUL rows result ********* '
        . PHP_EOL . mysql_error() . PHP_EOL . '**************************' . PHP_EOL );
}

$duedate = $today = date( 'Y-m-d H:i:s' );

while ( $row = mysql_fetch_assoc( $result ) ) {
echo PHP_EOL . '************************************************************************ H O S T I N G  L I N E *************************' . PHP_EOL ;    
    
    $cost_query = "
        SELECT
            SUM( bandwidth.cached )      as cached,
            SUM( bandwidth.non_cached )  as non_cached,
            MIN( bandwidth.created_at )  as min_date,
            MAX( bandwidth.created_at )  as max_date,
           /* MIN( bandwidth.currency_rate)as rate, */
            bandwidth.currency_rate as rate,
            bandwidth.price
        FROM 
            tblonappcdn_bandwidth as bandwidth
        WHERE
            bandwidth.hosting_id = $row[hostingid]
        GROUP BY
            bandwidth.price, rate  
        ORDER BY
            bandwidth.created_at
    ";
    
    $cost_result = full_query( $cost_query );
    if ( ! $cost_result ) {
        die('Cost Query Error ' . PHP_EOL . mysql_error() . PHP_EOL );
    }
    
    $billing_stat_for_whole_period = 'CDN Usage:' . PHP_EOL;
    $total_cost = 0;
    
    while ( $cost_row = mysql_fetch_assoc( $cost_result ) ) {
        $price = $cost_row[price];
        $cost =  ( $cost_row[cached] + $cost_row[non_cached] ) * ( $price * $cost_row['rate'] );       
        
        $billing_stat_for_whole_period .= '***'. PHP_EOL .
            'Start Date: (' . $cost_row[min_date] . ') End Date: (' . $cost_row[max_date] . ') :' . PHP_EOL . PHP_EOL .
            'Data Cached           ' . $cost_row[cached] .'MB'. PHP_EOL .
            'Data Non Cached       ' . $cost_row[non_cached] . 'MB'.  PHP_EOL .
            'Base Currency Price   ' . $cost_row[price]. PHP_EOL .
            'Currency Rate         ' . $cost_row['rate'] . PHP_EOL .       
            'Cost                  ' . $cost . PHP_EOL;
        
        $total_cost += $cost;    
        print('<pre>');
        print_r( $cost_row );
    }
    
    $billing_stat_for_whole_period .= 
       PHP_EOL.  '___' .PHP_EOL . PHP_EOL . 
        'Total Cost              ' . $total_cost; 
        ; 

        echo $billing_stat_for_whole_period . PHP_EOL;
    
    $client_amount_query =
        "SELECT
	        SUM( i.subtotal ) AS amount
	    FROM
            tblinvoices as i
	    WHERE
		    i.userid = $row[userid]
		   /* AND i.status = 'Unpaid' */
              AND i.notes = $row[hostingid] 
        GROUP BY      
            i.notes
        ";
    
    $client_amount_result = full_query($client_amount_query);
    
    if ( ! $client_amount_result ) {
        echo '******** Cron Failed MySQL error *********' . PHP_EOL;
        die( mysql_error() );
    }

    $invoiced_amount = mysql_fetch_assoc($client_amount_result);
    
// debug    
    echo 'Total Invoices Amount   ' . $invoiced_amount['amount'] . PHP_EOL;  
    
    $amount = round( $total_cost - $invoiced_amount['amount'], 2 );
 
// debug    
    echo PHP_EOL .'Not Invoiced Amount     ' . $amount . PHP_EOL; 
    

    if ( $amount > 0.1 ) {
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
            'itemamount1'      => $amount,
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

echo 'CDN Billing CronJob was Finished Successfully at ' . date('Y-m-d H-i-s') . ' (UTC)' , PHP_EOL;
