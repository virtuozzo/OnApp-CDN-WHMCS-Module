<?php
echo 'CDN Billing CronJob Start', PHP_EOL . PHP_EOL . PHP_EOL;

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
@date_default_timezone_set( 'UTC' );
@ini_set( 'memory_limit', '512M' );
@ini_set( 'max_execution_time', 0 );
@set_time_limit( 0 );

define( 'ROOT', dirname( dirname( dirname( dirname( dirname( $_SERVER[ 'SCRIPT_FILENAME' ] ) ) ) ) ) . '/' );

require_once ROOT . 'dbconnect.php';
require_once ROOT . 'includes/functions.php';
require_once ROOT . 'includes/clientareafunctions.php';
require_once ROOT . 'includes/wrapper/OnAppInit.php';
require_once ROOT . 'includes/invoicefunctions.php';
require_once ROOT . 'includes/processinvoices.php';

$query = "
    SELECT
        h.userid,
        h.domain,
        c.email               AS whmcsclientemail,
        c.taxexempt,
        c.state,
        c.country,
        h.paymentmethod,
        curr.code             AS currencycode,
        curr.prefix           AS currencyprefix,
        curr.rate             AS currencyrate,
        curr.default          AS currencydefault,
        s.hostname,
        onappc.onapp_user_id,
        s.ipaddress,
        h.id                  AS hostingid
    FROM
        tblservers AS s
    LEFT JOIN
        tblhosting AS h
        ON h.server = s.id
    LEFT JOIN
        tblonappcdnclients AS onappc
        ON onappc.service_id = h.id
    LEFT JOIN
        tblclients AS c
        ON h.userid = c.id
    LEFT JOIN
        tblcurrencies AS curr
        ON curr.id = c.currency
    WHERE
        s.type = 'onappcdn' AND
        onappc.onapp_user_id != ''
";

$result = full_query( $query );

echo PHP_EOL . PHP_EOL . 'CDN Billing CronJob Runs at ' . date( 'Y-m-d H:i:s' ), ' (UTC)', PHP_EOL, PHP_EOL;

if( ! $result || mysql_num_rows( $result ) < 1 ) {
	die( '******** Cron Failed MySQL error or NUL rows result ********* '
			. PHP_EOL . mysql_error() . PHP_EOL . '**************************' . PHP_EOL );
}

$duedate = $today = date( 'Y-m-d H:i:s' );

$tmp = array();
while( $row = mysql_fetch_assoc( $result ) ) {
	echo PHP_EOL . '************************************************************************ H O S T I N G  L I N E (' . $row[ 'hostingid' ] . ') *************************' . PHP_EOL;

	$cost_query = "
        SELECT
            SUM( cost * currency_rate ) AS price
        FROM
            tblonappcdn_billing
        WHERE
            hosting_id = $row[hostingid]
    ";

	$cost_result = full_query( $cost_query );

	if( ! $cost_result ) {
		echo 'Total cost query error ' . mysql_error();
		continue;
	}

	$cost_row = mysql_fetch_assoc( $cost_result );

	$total_cost = $cost_row[ 'price' ];

	if( is_null( $total_cost ) ) {
		print_r( $row );
		echo 'No CDN Usage for this hosting account (' . $row[ 'hostingid' ] . '). Skipping' . PHP_EOL;
		continue;
	}

	$client_amount_query =
			"SELECT
	        SUM( i.subtotal ) AS amount
	    FROM
            tblinvoices AS i
	    WHERE
		    i.userid = $row[userid]
		   /* AND i.status = 'Unpaid' */
              AND i.notes = $row[hostingid]
        GROUP BY
            i.notes
        ";

	$client_amount_result = full_query( $client_amount_query );

	if( ! $client_amount_result ) {
		echo '******** Client Amount MySQL error ' . mysql_error() . ' *********' . PHP_EOL;
		continue;
	}

	$invoiced_amount = mysql_fetch_assoc( $client_amount_result );

// debug
	echo 'Total Cost              ' . $total_cost . PHP_EOL;
// debug
	echo 'Total Invoices Amount   ' . $invoiced_amount[ 'amount' ] . PHP_EOL;

	$taxed = empty( $row[ 'taxexempt' ] ) && $CONFIG[ 'TaxEnabled' ];

	$taxrate = 0;
	$tax_amount = 0;
	$amount = round( $total_cost - $invoiced_amount[ 'amount' ], 2 );

	if( $taxed ) {
		echo 'taxed invoice' . PHP_EOL;
		$_taxrate = getTaxRate( 1, $row[ 'state' ], $row[ 'country' ] );
		$taxrate = $_taxrate[ 'rate' ];

		if( $CONFIG[ "TaxType" ] == "Inclusive" ) {
			$invoiced_amount[ 'amount' ] = $invoiced_amount[ 'amount' ] * ( 1 + $taxrate / 100 );
			$amount = round( $total_cost - $invoiced_amount[ 'amount' ], 2 );
			$tax_amount = round( $amount * $taxrate / ( 100 + $taxrate ), 2 );
		}
		else {
			$amount = round( $total_cost - $invoiced_amount[ 'amount' ], 2 );
			$tax_amount = round( $amount * $taxrate / 100, 2 );
		}
	}

// debug
	echo PHP_EOL . 'Not Invoiced Amount     ' . $amount . PHP_EOL;

	if( $amount > 0.2 ) {
// debug
		echo 'Generating Invoice' . PHP_EOL;

		$qry = 'SELECT
					`username`
				FROM
					`tbladmins`
				LIMIT 1';
		$res = mysql_query( $qry );
		$admin = mysql_result( $res, 0 );

		$data = array(
			'userid'          => $row[ 'userid' ],
			'date'            => $today,
			'duedate'         => $duedate,
			'paymentmethod'   => $row[ 'paymentmethod' ],
			'tax_type'        => $CONFIG[ 'TaxType' ],
			'taxrate'         => $taxrate,
			'tax_amount'      => $tax_amount,
			'itemdescription' => 'CDN Service Usage',
			'itemamount'      => $amount,
			'itemtaxed'       => $taxed,
			'notes'           => $row[ 'hostingid' ],
		);

// debug
		print_r( $data );
		echo PHP_EOL;

		$tmp[] = print_r( $data, 1 );
	}

// debug
//    $row['password'] = decrypt( $row['password'] );
	print_r( $row );
	echo '***************************' . PHP_EOL;
}

$logFile = dirname( __FILE__ ) . '/invoices.test.txt';
if( ! ( file_exists( $logFile ) && is_writable( $logFile ) ) ) {
	if( ! $log = fopen( $logFile, 'w' ) ) {
		exit( 'Can\'t write log file. Check if file ' . $logFile . ' exists and is writable!' . PHP_EOL );
	}
	else {
		fclose( $log );
	}
}

$splitter = PHP_EOL . str_repeat( '=', 60 ) . PHP_EOL . PHP_EOL;
$log = implode( $splitter, $tmp );
file_put_contents( $logFile, $log );
echo 'Open file ' . $logFile . ' in console!', PHP_EOL;


echo 'CDN Billing CronJob was Finished Successfully at ' . date( 'Y-m-d H-i-s' ) . ' (UTC)', PHP_EOL;