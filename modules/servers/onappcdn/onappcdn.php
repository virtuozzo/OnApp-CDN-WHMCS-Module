<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
//error_reporting( E_ALL );
//ini_set( 'display_errors', 1 );

if ( ! defined('ONAPPCDN_FILE_NAME') )
    define("ONAPPCDN_FILE_NAME", "onappcdn.php");

if ( ! defined('ONAPP_WRAPPER_INIT') )
    define('ONAPP_WRAPPER_INIT', dirname(__FILE__).'/../../../includes/wrapper/OnAppInit.php');

if ( file_exists( ONAPP_WRAPPER_INIT ) )
    require_once ONAPP_WRAPPER_INIT;

require_once dirname(__FILE__).'/lib.php';

load_language();

function onappcdn_ConfigOptions() {
    global $packageconfigoption, $_GET, $_POST, $_LANG;

    $serviceid = $_GET["id"] ? $_GET["id"] : $_POST["id"];
    $serviceid = addslashes($serviceid);

    $configarray = array();

    if ( ! file_exists( ONAPP_WRAPPER_INIT ) ) {
        return array(
            sprintf(
                "%s " . realpath( dirname(__FILE__).'/../../../' ) . "/includes",
                $_LANG['onappcdnwrappernotfound']
            ) => array()
        );
    }

    $table_result = onappcdn_createTables();

    if ( $table_result["error"] )
        return array(
            sprintf(
                "<font color='red'><b>%s</b></font>",
                $table_result["error"]
            ) => array()
        );

//    # Should return an array of the module options for each product - maximum of 24
print_r($packageconfigoption); 
    $configarray = array(
        "Package Configuration Options" => array( "Type" => "text" ),
    );

    return $configarray;
}

function onappcdn_CreateAccount($params) {

//    # ** The variables listed below are passed into all module functions **

//    $serviceid = $params["serviceid"]; # Unique ID of the product/service in the WHMCS Database
//    $pid = $params["pid"]; # Product/Service ID
//    $producttype = $params["producttype"]; # Product Type: hostingaccount, reselleraccount, server or other
//    $domain = $params["domain"];
//    $username = $params["username"];
//    $password = $params["password"];
//    $clientsdetails = $params["clientsdetails"]; # Array of clients details - firstname, lastname, email, country, etc...
//    $customfields = $params["customfields"]; # Array of custom field values for the product
//    $configoptions = $params["configoptions"]; # Array of configurable option values for the product

//    # Product module option settings from ConfigOptions array above
//    $configoption1 = $params["configoption1"];
//    $configoption2 = $params["configoption2"];
//    $configoption3 = $params["configoption3"];
//    $configoption4 = $params["configoption4"];

//    # Additional variables if the product/service is linked to a server
//    $server = $params["server"]; # True if linked to a server
//    $serverid = $params["serverid"];
//    $serverip = $params["serverip"];
//    $serverusername = $params["serverusername"];
//    $serverpassword = $params["serverpassword"];
//    $serveraccesshash = $params["serveraccesshash"];
//    $serversecure = $params["serversecure"]; # If set, SSL Mode is enabled in the server config

//    # Code to perform action goes here...

//    if ($successful) {
        $result = "success";
//    } else {
//        $result = "Error Message Goes Here...";
//    }
    return $result;
}

function onappcdn_TerminateAccount($params) {

//    # Code to perform action goes here...

//    if ($successful) {
        $result = "success";
//    } else {
//        $result = "Error Message Goes Here...";
//    }
    return $result;
}

function onappcdn_SuspendAccount($params) {

//    # Code to perform action goes here...

//    if ($successful) {
        $result = "success";
//    } else {
//        $result = "Error Message Goes Here...";
//    }
//    return $result;
}

function onappcdn_UnsuspendAccount($params) {

//    # Code to perform action goes here...

//    if ($successful) {
        $result = "success";
//    } else {
//        $result = "Error Message Goes Here...";
//    }
    return $result;
}

function onappcdn_ChangePassword($params) {

//    # Code to perform action goes here...

//    if ($successful) {
        $result = "success";
//    } else {
//        $result = "Error Message Goes Here...";
//    }
    return $result;
}

function onappcdn_ChangePackage($params) {

//    # Code to perform action goes here...

//    if ($successful) {
        $result = "success";
//    } else {
//        $result = "Error Message Goes Here...";
//    }
    return $result;
}

function onappcdn_ClientArea($params) {

//    # Output can be returned like this, or defined via a clientarea.tpl onappcdn file (see docs for more info)

//    $code = '<form action="http://'.$serverip.'/controlpanel" method="post" target="_blank">
//<input type="hidden" name="user" value="'.$params["username"].'" />
//<input type="hidden" name="pass" value="'.$params["password"].'" />
//<input type="submit" value="Login to Control Panel" />
//<input type="button" value="Login to Webmail" onClick="window.open(\'http://'.$serverip.'/webmail\')" />
//</form>';
//    return $code;

}

function onappcdn_AdminLink($params) {

    $code = "";
//    $code = '<form action=\"http://'.$params["serverip"].'/controlpanel" method="post" target="_blank">
//<input type="hidden" name="user" value="'.$params["serverusername"].'" />
//<input type="hidden" name="pass" value="'.$params["serverpassword"].'" />
//<input type="submit" value="Login to Control Panel" />
//</form>';
    return $code;

}

//function onappcdn_LoginLink($params) {

//    echo "<a href=\"http://".$params["serverip"]."/controlpanel?gotousername=".$params["username"]."\" target=\"_blank\" style=\"color:#cc0000\">login to control panel</a>";

//}

//function onappcdn_reboot($params) {

//    # Code to perform reboot action goes here...

//    if ($successful) {
//        $result = "success";
//    } else {
//        $result = "Error Message Goes Here...";
//    }
//    return $result;

//}

//function onappcdn_shutdown($params) {

//    # Code to perform shutdown action goes here...

//    if ($successful) {
//        $result = "success";
//    } else {
//        $result = "Error Message Goes Here...";
//    }
//    return $result;

//}

//function onappcdn_ClientAreaCustomButtonArray() {
//    $buttonarray = array(
//     "Reboot Server" => "reboot",
//    );
//    return $buttonarray;
//}

//function onappcdn_AdminCustomButtonArray() {
//    $buttonarray = array(
//     "Reboot Server" => "reboot",
//     "Shutdown Server" => "shutdown",
//    );
//    return $buttonarray;
//}

//function onappcdn_extrapage($params) {
//    $pagearray = array(
//     'onappcdnfile' => 'example',
//     'breadcrumb' => ' > <a href="#">Example Page</a>',
//     'vars' => array(
//        'var1' => 'demo1',
//        'var2' => 'demo2',
//     ),
//    );
//    return $pagearray;
//}

//function onappcdn_UsageUpdate($params) {

//    $serverid = $params['serverid'];
//    $serverhostname = $params['serverhostname'];
//    $serverip = $params['serverip'];
//    $serverusername = $params['serverusername'];
//    $serverpassword = $params['serverpassword'];
//    $serveraccesshash = $params['serveraccesshash'];
//    $serversecure = $params['serversecure'];

//    # Run connection to retrieve usage for all domains/accounts on $serverid

//    # Now loop through results and update DB

//    foreach ($results AS $domain=>$values) {
//        update_query("tblhosting",array(
//         "diskused"=>$values['diskusage'],
//         "dislimit"=>$values['disklimit'],
//         "bwused"=>$values['bwusage'],
//         "bwlimit"=>$values['bwlimit'],
//         "lastupdate"=>"now()",
//        ),array("server"=>$serverid,"domain"=>$values['domain']));
//    }

//}

//function onappcdn_AdminServicesTabFields($params) {

//    $result = select_query("mod_customtable","",array("serviceid"=>$params['serviceid']));
//    $data = mysql_fetch_array($result);
//    $var1 = $data['var1'];
//    $var2 = $data['var2'];
//    $var3 = $data['var3'];
//    $var4 = $data['var4'];

//    $fieldsarray = array(
//     'Field 1' => '<input type="text" name="modulefields[0]" size="30" value="'.$var1.'" />',
//     'Field 2' => '<select name="modulefields[1]"><option>Val1</option</select>',
//     'Field 3' => '<textarea name="modulefields[2]" rows="2" cols="80">'.$var3.'</textarea>',
//     'Field 4' => $var4, # Info Output Only
//    );
//    return $fieldsarray;

//}

//function onappcdn_AdminServicesTabFieldsSave($params) {
//    update_query("mod_customtable",array(
//        "var1"=>$_POST['modulefields'][0],
//        "var2"=>$_POST['modulefields'][1],
//        "var3"=>$_POST['modulefields'][2],
//    ),array("serviceid"=>$params['serviceid']));
//}

function onappcdn_UsageUpdate($params) {
// 
//    $serverid = $params['serverid'];
//    $serverhostname = $params['serverhostname'];
//    $serverip = $params['serverip'];
//    $serverusername = $params['serverusername'];
//    $serverpassword = $params['serverpassword'];
//    $serveraccesshash = $params['serveraccesshash'];
//    $serversecure = $params['serversecure'];
// 
//    # Run connection to retrieve usage for all domains/accounts on $serverid

//    # Now loop through results and update DB

//    foreach ($results AS $domain=>$values) {
//        update_query("tblhosting",array(
//         "diskused"=>$values['diskusage'],
//         "dislimit"=>$values['disklimit'],
//         "bwused"=>$values['bwusage'],
//         "bwlimit"=>$values['bwlimit'],
//         "lastupdate"=>"now()",
//        ),array("server"=>$serverid,"domain"=>$values['domain']));
//    }
}
