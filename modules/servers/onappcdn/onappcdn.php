<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
//error_reporting( E_ALL );
//ini_set( 'display_errors', 1 );

require_once dirname(__FILE__).'/lib.php';

loadcdn_language();

function onappcdn_ConfigOptions() {
    global $packageconfigoption, $_GET, $_POST, $_LANG;
    $serviceid = $_GET["id"] ? $_GET["id"] : $_POST["id"];
    $serviceid = addslashes($serviceid);


/// BEGIN Load Servers details ///
/////////////////////////////////

    $sql_servers_result = full_query(
        "SELECT 
            id, name, ipaddress, hostname, password, username
        FROM
            tblservers
        WHERE type = 'onappcdn'"
    );

    $onapp_servers = array();

    while ( $server = mysql_fetch_assoc($sql_servers_result)) {
        $js_onappServers[$server['id']] = $server['name'];

        $server_credentials[$server['id']]['hostname']  = $server['hostname'];
        $server_credentials[$server['id']]['ipaddress'] = $server['ipaddress'];
        $server_credentials[$server['id']]['username'] = $server['username'];
        $server_credentials[$server['id']]['password']  = decrypt( $server['password'] );
    }

    // Error if not found onapp server
    if ( ! $js_onappServers )
        return array(
            "<b class='red'>" . $_LANG["onapperrcantfoundactiveserver"] . "</b>" => array()
    );

/// END Load Servers details ///
///////////////////////////////

/// Get OnApp Instance /////////////
///////////////////////////////////
    
    $ipaddress = $server_credentials[$packageconfigoption[1]]['ipaddress'];
    $hostname  = $server_credentials[$packageconfigoption[1]]['hostname'];
    $username  = $server_credentials[$packageconfigoption[1]]['username'];
    $password  = $server_credentials[$packageconfigoption[1]]['password'];

    if ( $username && $password ) {
        $onapp_instance = new OnApp_Factory(
            ( $ipaddress ) ? $ipaddress : $hostname,
            $username,
            $password
        );
    }

/// END Get OnApp Instance /////////
///////////////////////////////////
   
/// Create Tables ////////////
/////////////////////////////

    $table_result = onappcdn_createTables();

    if ( $table_result["error"] )
        return array(
            sprintf(
                "<font color='red'><b>%s</b></font>",
                $table_result["error"]
            ) => array()
        );

/// END Create Tables ////////
/////////////////////////////

/// BEGIN Load Server Groups Rels ///
////////////////////////////////////

    $query = "SELECT * FROM tblservergroupsrel";
    $result = full_query( $query );

    if ( mysql_num_rows($result) > 0 ) {
        while( $row = mysql_fetch_assoc($result) ) {
            $js_serverGroupsRels[$row['groupid']][] = $row['serverid'];
        }
    }
    else
        $js_serverGroupsRels = array();
    
/// END Load Server Groups Rels ///
//////////////////////////////////

/// BEGIN Load Billing Plans ///
///////////////////////////////

    if ( $onapp_instance ) {
        $bplan = $onapp_instance->factory( 'BillingPlan' );

        $bplans = $bplan->getList();

        $js_bplanOptions = '';

        if ( ! empty ( $bplans ) ) {
            foreach ( $bplans as $_plan ) {
                $js_bplanOptions .= "    billingPlans[$_plan->_id] = '".addslashes($_plan->_label)."';\n";
            }
        }
    }
    
// END Load Billing Plans  //
////////////////////////////

/// BEGIN Load User Groups ///
/////////////////////////////

    if ( $onapp_instance ) {
        $ugroup = $onapp_instance->factory( 'UserGroup' );

        $ugroups = $ugroup->getList();

        $js_ugroupOptions = "    userGroups[0] = '';\n";

        if ( ! empty ( $ugroups ) ) {
            foreach ( $ugroups as $_group ) {
                $js_ugroupOptions .= "    userGroups[$_group->_id] = '".addslashes($_group->_label)."';\n";
            }
        }
    }

/// END Load User Groups  ///
////////////////////////////

/// BEGIN Load Roles ///
///////////////////////
    
    if ( $onapp_instance ) {
        $role = $onapp_instance->factory( 'Role' );

        $roles = $role->getList();

        $role_ids = array();
        $js_roleOptions = '';

        if ( ! empty ( $roles ) ) {
            foreach ( $roles as $_role) {
                $js_roleOptions .= "    userRoles[$_role->_id] = '".addslashes($_role->_label)."';\n";
            }
        }
    }
    
/// END Load Roles     //////
////////////////////////////

/// Data check ///
/////////////////

    // check config json
    if ( $packageconfigoption[2] != '' &&
        ! json_decode( htmlspecialchars_decode ( $packageconfigoption[2] ) ) )
    {
        return array(
            "<b class='red'>" . $_LANG["onappcdnerrorinvalidconfigjson"] . "</b>" => array()
        );
    }
    
    // Check wrapper
    if ( ! file_exists( ONAPP_WRAPPER_INIT ) ) {
        return array(
            sprintf(
                "%s " . realpath( dirname(__FILE__).'/../../../' ) . "/includes",
                $_LANG['onappcdnwrappernotfound']
            ) => array()
        );
    }

/// Localization ///
///////////////////
    
    $js_localization_array = array(
        'servers',
        'billingplans',
        'timezones',
        'usergroups',
        'userroles',
        'usersproperties',
        'cdnresourceproperties',
        'noserverselected',
    );

    $js_localization_string = '';

    foreach ($js_localization_array as $string)
        if (isset($_LANG['onappcdn'.$string]))
            $js_localization_string .= "    LANG['onappcdn$string'] = '".$_LANG['onappcdn'.$string]."';\n";

/// END Localization ///
///////////////////////

/// Javascript ///
/////////////////
        
    $javascript = "
        <link rel=\"stylesheet\" type=\"text/css\" href=\"../modules/servers/onappcdn/includes/style.css\" />
        <script type=\"text/javascript\" src=\"../modules/servers/onappcdn/includes/js/onappcdn.js\"></script>
        <script type=\"text/javascript\" src=\"../modules/servers/onappcdn/includes/js/tz.js\"></script>
        <script type=\"text/javascript\">
        
            var servers         = " . json_encode( $js_onappServers ) . "
            var serverGroupRels = " . json_encode( $js_serverGroupsRels ) . "

            var LANG = new Array()
                $js_localization_string
            var billingPlans = new Array()
                $js_bplanOptions
            var userGroups = new Array()
                $js_ugroupOptions
            var userRoles = new Array()
                $js_roleOptions
        </script>
    ";

/// END Javascript ///
/////////////////////

/// Passing data to the view ///
///////////////////////////////
    $configarray = array();
    
    $configarray = array(
        "&nbsp" => array(
            "Type" => "text" ),
        "&nbsp" => array(
            "Type" => "text" ),
        "&nbsp;" => array(
            "Type"        => "text",
            "Description" => "\n$javascript",
        )
    );

/// End Pass Data to the view ///
////////////////////////////////
    
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


