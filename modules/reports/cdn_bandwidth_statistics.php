<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

if ( isset( $_POST['update']) && $_POST['update'] == 'run' ) {
    ob_start();
        include '../modules/servers/onappcdn/crons/cron_bandwidth.php';
    ob_end_clean();
}

error_reporting(E_ERROR);
ini_set("display_errors", 1);

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

    if ( isset ( $bw[start] ) && $bw[start] != "" ) {
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
        hosting.userid,
        client.firstname              as clientfirstname,
        client.lastname               as clientlastname,
        server.hostname,
        server.ipaddress,
        server.id                     as serverid,
        onappclient.onapp_user_id,
        sum( bandwidth.cached )       as cached,
        sum( bandwidth.non_cached )   as non_cached,
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
        hosting.id
    ";

 // debug
//    echo '<pre>';
//    echo $query1 . PHP_EOL ;

}

// End Filtering //
//////////////////

// Filter HTML //
////////////////

$reportdata["title"] = "CDN Bandwidth Statistics";
$reportdata["description"] = "This report shows bandwidth usage of CDN Resources.<br /><br />

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

$reportdata["tableheadings"] = array( "Status", "Service ID", "Client Name", "Product Name", "Cached","Non Cached", "Total Bandwidht");

$result = mysql_query($query1);

$total            = 0;
$total_cached     = 0;
$total_non_cached = 0;

while($data = mysql_fetch_assoc($result)) {
    $total_bandwidth  = $data['cached']+$data['non_cached'];
    $total            += $total_bandwidth;
    $total_cached     += $data['cached'];
    $total_non_cached += $data['non_cached'];

// debug
//    print('<pre>');
//    print_r($data);

    $clientlink = '<a href="clientssummary.php?userid='.$data['userid'].'">';
    $servicelink = '<a href="clientshosting.php?userid= '. $data['userid'] .'&id='. $data['hostingid'] .'">';;

    $reportdata["tablevalues"][] = array($data['domainstatus'], $servicelink.$data['hostingid'].'</a>' , $clientlink.$data['clientfirstname'].' '.$data['clientlastname'] . '</a>', $data['productname'], $data['cached'], $data['non_cached'], $total_bandwidth);
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
    $reportdata['tablevalues'][] = array( '<b>Total</b>', '','', '', '<b>'. $total_cached . '</b>', '<b>'. $total_non_cached . '</b>', '<b>'. $total . '</b>');
}

function onappcdn_dates_mysql ( $date ){
    $date = explode('/', $date );
    $date = array_reverse( $date );
    $date = implode( '-', $date );
    return $date;
}

