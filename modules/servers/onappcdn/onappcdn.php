<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once dirname(__FILE__).'/class_onappcdn.php';

OnAppCDN::loadcdn_language();
$init_wrapper = OnAppCDN::init_wrapper();
if ( ! defined('ONAPPCDN_FILE_NAME') ) define('ONAPPCDN_FILE_NAME', 'onappcdn.php' );

function onappcdn_ConfigOptions() {
    global $packageconfigoption, $_GET, $_POST, $_LANG;
    $serviceid = addslashes( OnAppCDN::get_value("id") );
/// Create Tables ////////////////
//////////////////////////////////

    $table_result = OnAppCDN::createTables();

    if ( $table_result["error"] )
        return array(
            sprintf(
                "<font color='red'><b>%s</b></font>",
                $table_result["error"]
            ) => array()
        );

/// END Create Tables ////////////
//////////////////////////////////

/// BEGIN Load Servers details ///
//////////////////////////////////

    $cdnservers = OnAppCDN::getservers();

    if ( count($cdnservers) == 0 )
        return array(
            "<b class='cdnerrors'>" . $_LANG["onappcdnerrcantfoundactiveserver"] . "</b>" => array()
        );

    foreach($cdnservers as $key => $value)
        if( $value['groupid'] != $value['servergroup'] )
            unset($cdnservers[$key]);

    if ( count($cdnservers) == 0 )
        return array(
            "<b class='cdnerrors'>" . $_LANG["onappcdnerrcantfoundactiveserverforgroup"] . "</b>" => array()
        );

    $cdnservers[0] = array('name' => $_LANG["onappcdservernnotdefined"]);
    asort($cdnservers);

    $js_servers = array();
    foreach($cdnservers as $key => $value)
        $js_servers[$key] = $value['name'];

/// Javascript ///
/////////////////
        
    $javascript = "
        <link rel=\"stylesheet\" type=\"text/css\" href=\"../modules/servers/onappcdn/includes/style.css\" />
        <script type=\"text/javascript\" src=\"../modules/servers/onappcdn/includes/js/base.js\"></script>
        <script type=\"text/javascript\">
            var servers         = " . json_encode( $js_servers ) . "
        </script>";

/// END Javascript ///
/////////////////////

    $configarray = array(
        'CDN Server' => array( 
            "Type"        => "dropdown", 
            "Options"     => implode(',', array_keys($cdnservers)),
            "Description" => $javascript
        ));

    $server_id = $packageconfigoption[1];
    if ( is_null($server_id)                               // NULL
        || $server_id == 0                                 // not defined
        || ! in_array($server_id, array_keys($cdnservers)) // not in group
    ) {
        $configarray['CDN Server']['Description'] .= '  '.$_LANG['onappcdnnoserverselected'];
        return $configarray;
    }

/// END Load Servers details ///
////////////////////////////////

/// Get OnApp Instance /////////
////////////////////////////////
    
    $ipaddress = $cdnservers[$server_id]['ipaddress'];
    $hostname  = $cdnservers[$server_id]['hostname'];
    $username  = $cdnservers[$server_id]['username'];
    $password  = $cdnservers[$server_id]['password'];

    if ( $username && $password && ( $hostname || $ipaddress )  ) {
        $onapp_instance = new OnApp_Factory(
            ( $ipaddress ) ? $ipaddress : $hostname,
            $username,
            $password
        );
        if ( $onapp_instance->getErrorsAsArray() ) {
            foreach( $onapp_instance->getErrorsAsArray() as $error ) {
                $js_Errors[] = $error;
            }
        }
    } else {
        $js_Errors[] = $_LANG["onappcdnnologindatainserversettings"];
    }


    if( ! $onapp_instance->_is_auth ) {
        $configarray['CDN Server']['Description'] .= '  '.$_LANG['onappcdnwrongserverconfig'];
        return $configarray;
    }

/// END Get OnApp Instance /////////
////////////////////////////////////

/// BEGIN Load Roles ///
///////////////////////
    
    if ( $onapp_instance ) {
        $onapprole = $onapp_instance->factory( 'Role' );

        $roles = $onapprole->getList();

        $js_roles = "";
        $roles_options = array();
        if ( ! empty ( $roles ) ) {
            foreach ( $roles as $_role) {
                $js_roles .= "    userRoles[$_role->_id] = '".addslashes($_role->_label)."';\n";
                array_push($roles_options, $_role->_id);
            }
        }

        $configarray[$_LANG['onappcdnuserroles']] = array(
            "Type"        => "text",
//            "Options"     => implode(',', $roles_options)
        );
    }
    
/// END Load Roles     //////
////////////////////////////

/// BEGIN Load Billing Plans ///
///////////////////////////////

    if ( $onapp_instance ) {
        $onappbillingplan = $onapp_instance->factory( 'BillingPlan' );

        $billingplans = $onappbillingplan->getList();

        $js_billingplans = "    billingPlans[0] = '';\n";
        $billingplans_options = array(0);
        if ( ! empty ( $billingplans ) )
            foreach ( $billingplans as $_plan ) {
                $js_billingplans .= "    billingPlans[$_plan->_id] = '".addslashes($_plan->_label)."';\n";
                array_push($billingplans_options, $_plan->_id);
            };

        $configarray[$_LANG['onappcdnbillingplans']] = array(
            "Type"        => "dropdown",
            "Options"     => implode(',', $billingplans_options)
        );
    };

// END Load Billing Plans  //
////////////////////////////

/// BEGIN Load User Groups ///
/////////////////////////////

    if ( $onapp_instance ) {
        $onappusergroup = $onapp_instance->factory( 'UserGroup' );

        $usergroups = $onappusergroup->getList();

        $js_usergroups = "    userGroups[0] = '';\n";
        $usergroups_options = array(0);
        if ( ! empty ( $usergroups ) )
            foreach ( $usergroups as $_group ) {
                $js_usergroups .= "    userGroups[$_group->_id] = '".addslashes($_group->_label)."';\n";
                array_push($usergroups_options, $_group->_id);
            }

        $configarray[$_LANG['onappcdnusergroups']] = array(
            "Type"        => "dropdown",
            "Options"     => implode(',', $usergroups_options)
        );
    }

/// END Load User Groups  ///
////////////////////////////

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
        <script type=\"text/javascript\" src=\"../modules/servers/onappcdn/includes/js/timezone.php\"></script>
        <script type=\"text/javascript\" src=\"../modules/servers/onappcdn/includes/js/onappcdn.js\"></script>
        <script type=\"text/javascript\">
            var cdnErrors       = " . json_encode( $js_Errors ) . "

            var LANG = new Array()
                $js_localization_string
            var billingPlans = new Array()
                $js_billingplans
            var userGroups = new Array()
                $js_usergroups
            var userRoles = new Array()
                $js_roles
        </script>
    ";

/// END Javascript ///
/////////////////////

    $configarray[$_LANG['onappcdntimezones']] = array(
        "Type"        => "text",
        "Description" => $javascript
    );

    return $configarray;
}

function onappcdn_CreateAccount($params) {

    $onappcdn = new OnAppCDN($params["serviceid"]);

    $user = $onappcdn->get_user();

    if( $user )
        $result = 'OnApp CDN user already exists (onapp user id #'.$user['onapp_user_id'].')';
    else
        $result = $onappcdn->create_user();

    return $result;
}

function onappcdn_TerminateAccount($params) {

    $onappcdn = new OnAppCDN($params["serviceid"]);

    $user = $onappcdn->get_user();

    if( ! $user )
        $result = "OnApp CDN user do not exists";
    else
        $result = $onappcdn->delete_user();

    return $result;
}

function onappcdn_SuspendAccount($params) {

    $onappcdn = new OnAppCDN($params["serviceid"]);

    $user = $onappcdn->get_user();

    if( ! $user )
        $result = "OnApp CDN user do not exists";
    else
        $result = $onappcdn->suspend_user();

    return $result;
}

function onappcdn_UnsuspendAccount($params) {

    $onappcdn = new OnAppCDN($params["serviceid"]);

    $user = $onappcdn->get_user();

    if( ! $user )
        $result = "OnApp CDN user do not exists";
    else
        $result = $onappcdn->unsuspend_user();

    return $result;
}

function onappcdn_ClientArea( $params ) {
    global $_LANG;

    if ( ! $init_wrapper = OnAppCDN::init_wrapper() )
        return
            sprintf(
                "%s ",
                $_LANG['onapponmaintenance']
     );
    
    $onappcdn = new OnAppCDN( $params['serviceid']);
    $user = $onappcdn->get_user();
    
    if ( ! is_null($user["onapp_user_id"]) )
        return '<a href="' . ONAPPCDN_FILE_NAME . '?page=resources&id=' . $params['serviceid'] . '">' . $_LANG["onappcdnresources"] . '</a>';
    else
        return '<a href="' . ONAPPCDN_FILE_NAME . '?page=default&id=' . $params['serviceid'] . '&action=create_cdn">' . $_LANG["onappcdncreate"] . '</a>';
}
/*
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
*/
