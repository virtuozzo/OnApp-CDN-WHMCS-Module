<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

if ( isset( $_POST['update']) && $_POST['update'] == 'run' ) {
    ob_start();
        include '../modules/servers/onappcdn/crons/cron_bandwidth.php';
    ob_end_clean();
}

//error_reporting(E_ERROR);
//ini_set("display_errors", 1);

// Getting Product Name filter selects //
////////////////////////////////////////
// TODO multiselect
$product_query = "
    SELECT
        name,
        id
    FROM
        tblproducts
    WHERE
        servertype = 'onappcdn'
";

$product_result = full_query( $product_query );

while ( $row = mysql_fetch_assoc( $product_result ) ) {
    $product_names[$row['id']] = $row['name'];
}

$product_name_options = "";
foreach ( $product_names as $id => $name ) {
    $selected = '';

    if ( $id == $bw['productid'] )
        $selected = 'selected';

    $product_name_options .= "<option value='$id' $selected>$name</option>";
}

// END Getting Product Name filter selects //
////////////////////////////////////////////

// Getting Client Name filter selects //
///////////////////////////////////////

$client_query = "
    SELECT
        hosting.userid,
        client.firstname,
        client.lastname
    FROM
        tblservers as server
    LEFT JOIN
        tblhosting as hosting
        ON hosting.server = server.id
    LEFT JOIN
        tblclients as client
        ON hosting.userid = client.id
    WHERE
        server.type = 'onappcdn' AND
        hosting.userid != ''
";

$client_result = full_query( $client_query );

while ( $row = mysql_fetch_assoc( $client_result ) ) {
    $client_names[$row['userid']] = $row['firstname'] . ' ' . $row['lastname'];
}

$client_name_options = "";
foreach ( $client_names as $id => $name ) {
    $selected = '';
    if ( $id == $bw['clientid'] )
        $selected = 'selected';
    
    $client_name_options .= " <option value='$id' $selected>$name</option>";
}

// END Getting Product Name filter selects //
////////////////////////////////////////////
//
// Filtering //
//////////////

if ( isset( $_POST['bw']) ) {

    $filter_condition = "AND bandwidth.created_at != ''" . PHP_EOL;

    $bw = $_POST['bw'];

 //debug
//    print('<pre>');
//    print_r($bw);

    $filter_conditions = array();

    if ( isset ( $bw['end'] ) && $bw['end'] != "") {
        // TODO DATE_FORMAT
        $filter_conditions[] = "bandwidth.created_at >= '". onappcdn_dates_mysql($bw['end']) ."'";
    }

    if ( isset ( $bw['start'] ) && $bw[start] != "" ) {
        // TODO DATE_FORMAT
        $filter_conditions[] = "bandwidth.created_at <= '". onappcdn_dates_mysql($bw['start']). "'";
    }

    if ( isset ( $bw[serviceid] ) && $bw[serviceid] != "" ) {
        $filter_conditions[] = "hosting.id = $bw[serviceid]";
    }

    if ( isset ( $bw[clientid] ) && $bw[clientid] != "" ) {
        $filter_conditions[] = "client.id = $bw[clientid]";
    }

    if ( isset ( $bw[productid] ) && $bw[productid] != "" ) {
        $filter_conditions[] = "product.id = $bw[productid]";
    }
    
    foreach( $filter_conditions as $condition ) {
        $filter_condition  .=  ' AND '. PHP_EOL . $condition;
    }
// rename
// TODO add field client fullname instead of first an second
// TODO add pagenator
$query1 = "
    SELECT
        bandwidth.currency_rate       as rate,
        bandwidth.price               as price,
        hosting.userid,
        client.firstname              as clientfirstname,
        client.lastname               as clientlastname,
        client.id                     as clientid, 
        server.hostname,
        server.ipaddress,
        server.id                     as serverid,
        onappclient.onapp_user_id,
        sum( bandwidth.cached )       as cached,
        sum( bandwidth.non_cached )   as non_cached,
        sum( bandwidth.non_cached) + sum( bandwidth.cached ) as total_bandwidth,
        (sum( bandwidth.non_cached) + sum( bandwidth.cached )) * bandwidth.price / 1000 as cost,
        bandwidth.cdn_hostname,
        hosting.id                    as hostingid,
        bandwidth.created_at,
        product.name                  as productname,
        product.id                    as productid,
        hosting.domainstatus
    FROM
        tblservers as server
    LEFT JOIN
        tblhosting as hosting
        ON hosting.server = server.id
    LEFT JOIN
        tblonappcdnclients as onappclient
        ON onappclient.service_id = hosting.id
    LEFT JOIN
        tblclients as client
        ON hosting.userid = client.id
    LEFT JOIN
        tblproducts as product
        ON product.id = hosting.packageid
    LEFT JOIN
        tblonappcdn_bandwidth  as bandwidth
        ON bandwidth.hosting_id = hosting.id
    WHERE
        server.type = 'onappcdn' AND
        onappclient.onapp_user_id != ''
        $filter_condition
    GROUP BY
        bandwidth.hosting_id, bandwidth.price
    ORDER BY 
        bandwidth.created_at DESC
    ";

 // debug
//    echo '<pre>';
//    echo $query1 . PHP_EOL ;

}

// End Filtering //
//////////////////

// Filter HTML //
////////////////

$reportdata["title"] = "CDN Usage Statistics";
$reportdata["description"] = "This report shows usage statistics of CDN Resources and billing information.<br /><br />

<div id='tab_content'>

    <form id = 'form' action='' method='post'>
        <table class='form' width='100%' border='0' cellspacing='2' cellpadding='3'>
            <tr>
                <td width='15%' class='fieldlabel'>Start Date</td>
                <td class='fieldarea'><input class='datepick' type='text' name='bw[end]' size='20' value='$bw[end]'></td>
                <td class='fieldlabel'>Service Id</td>
                <td class='fieldarea'><input type='text' name='bw[serviceid]' size='20' value='$bw[serviceid]'></td>
            </tr>
            <tr>
                <td class='fieldlabel'>End Date</td>
                <td class='fieldarea'><input class='datepick' type='text' name='bw[start]' size='20' value='$bw[start]'></td>
                <td class='fieldlabel'>Client Name</td>
                <td class='fieldarea'>
                    <select name='bw[clientid]'>
                        <option value=''>- Any -</option>
                        $client_name_options
                    </select>
                </td>
            </tr>
            <tr>
                <td class='fieldlabel'>&nbsp;</td>
                <td class='fieldlarea'>&nbsp;</td>

                <td class='fieldlabel'>Product Name</td>
                <td class='fieldarea'>
                    <select name='bw[productid]'> <!-- TODO multiselect -->
                        <option value=''>- Any -</option>
                        $product_name_options
                    </select>
                </td>
            </tr>
        </table>
        <input id='update' type='hidden' name='update' value='' />
        <p align='center'><input type='submit' name='filter' value='Search' class='button'></p>
    </form>
  </div>
";

// END Filter HTML //
////////////////////
if ( $bw['end'] != "" || $bw['start'] != "" ) {
    $reportdata["tableheadings"] = array( "Status", "Service ID", "Client Name", "Product Name", "Cached","Non Cached", "Total Bandwidht", "Cost" );
} else {
    $reportdata["tableheadings"] = array( "Status", "Service ID", "Client Name", "Product Name", "Cached","Non Cached", "Total Bandwidht", "Cost", "Paid Invoices", "Unpaid Invoices", "Not Invoiced" );
}

$result = mysql_query($query1);

$total                 = 0;
$total_cost            = 0;
$total_paid            = 0;
$total_unpaid          = 0;
$not_invoiced_amount   = 0;

$_data = array();
while($row1 = mysql_fetch_assoc($result)) {
    if ( ! isset( $_data[$row1['hostingid']]) ){
        $_data[$row1['hostingid']] = $row1;
    } else {
        $_data[$row1['hostingid']]['cached'] += $row1['cached'];
        $_data[$row1['hostingid']]['non_cached'] += $row1['non_cached'];
        $_data[$row1['hostingid']]['total_bandwidth'] += $row1['total_bandwidth'];
        $_data[$row1['hostingid']]['cost'] += $row1['cost'];
    } 
}
//print('<pre>');
//print_r($_data);
//die();

foreach( $_data as $data ) {
    $invoices_query =
        "SELECT
            SUM( i.subtotal ) AS amount,
            status
        FROM
            tblinvoices as i
        WHERE
            i.userid = $data[clientid]
            AND i.notes = $data[hostingid]
        GROUP BY      
            i.notes, status
        ORDER BY 
            i.date DESC
        ";

    $invoices_result = full_query($invoices_query);

    $invoices_data           = array();
    $invoices_data['paid']   = 0;
    $invoices_data['unpaid'] = 0;
    

    while ($invoices = mysql_fetch_assoc($invoices_result)) {
        if ($invoices['status'] == 'Paid') {
            $invoices_data['paid'] = $invoices['amount'] / $data['rate'];
        } else {
            $invoices_data['unpaid'] = $invoices['amount'] / $data['rate'];
        }
    }
    
    $not_invoiced_amount = $data['cost'] - ( $invoices_data['paid'] + $invoices_data['unpaid'] );
    
    $total_paid         += $invoices_data['paid'];
    $total_unpaid       += $invoices_data['unpaid'];
    $total              += $data['total_bandwidth'];
    $total_cached       += $data['cached'];
    $total_non_cached   += $data['non_cached'];
    $total_cost         += $data['cost'];
    $total_not_invoiced += $not_invoiced_amount;
    
// debug
//    print('<pre>');
//    print_r($data);

    $clientlink = '<a href="clientssummary.php?userid='.$data['userid'].'">';
    $servicelink = '<a href="clientshosting.php?userid= '. $data['userid'] .'&id='. $data['hostingid'] .'">';;

    if ( $bw['end'] != "" || $bw['start'] != "" ) {
        $reportdata["tablevalues"][] = array($data['domainstatus'], $servicelink.$data['hostingid'].'</a>' , $clientlink.$data['clientfirstname'].' '.$data['clientlastname'] . '</a>', $data['productname'], $data['cached'], $data['non_cached'], $total, $data['cost']);
    } else {    
        $reportdata["tablevalues"][] = array($data['domainstatus'], $servicelink.$data['hostingid'].'</a>' , $clientlink.$data['clientfirstname'].' '.$data['clientlastname'] . '</a>', $data['productname'], $data['cached'], $data['non_cached'], $total, $data['cost'], $invoices_data['paid'], $invoices_data['unpaid'], $not_invoiced_amount);
    }
}

// Javascript //
///////////////
$javascript = '
    <script type="text/javascript" >'  . PHP_EOL .
    'function update_run(){'           . PHP_EOL .
        'jQuery("#update").val("run")' . PHP_EOL .
        'jQuery("#form").submit()'     . PHP_EOL .
    '}'                                . PHP_EOL .
    '</script>
';

echo $javascript;
// END Javascript //
///////////////////

$reportdata["footertext"] = '<a href="#" onClick="update_run()">Update Now</a>';

if ( ! mysql_num_rows($result) < 1 ) {
    if ( $bw['end'] != "" || $bw['start'] != "" ) {
        $reportdata['tablevalues'][] = array( '<b>Total</b>', '','', '', '<b>'. $total_cached . '</b>', '<b>'. $total_non_cached . '</b>', '<b>'. $total . '</b>', '<b>' .$total_cost. '</b>');
    } else {
        $reportdata['tablevalues'][] = array( '<b>Total</b>', '','', '', '<b>'. $total_cached . '</b>', '<b>'. $total_non_cached . '</b>', '<b>'. $total . '</b>', '<b>' .$total_cost. '</b>', '<b>' .$total_paid. '</b>', '<b>' .$total_unpaid. '</b>', '<b>' .$total_not_invoiced. '</b>');
    }
}

function onappcdn_dates_mysql ( $date ){
    $date = explode('/', $date );
    $date = array_reverse( $date );
    $date = implode( '-', $date );
    return $date;
}

