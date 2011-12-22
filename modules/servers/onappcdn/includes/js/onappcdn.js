$(document).ready(function(){

// getting server configoption
    var serverId         =  $('input[name="packageconfigoption[1]"]').val()

// getting  other configs
    var configs = new Object()
    configs = jQuery.parseJSON( $('input[name="packageconfigoption[2]"]').val() )

/// assign config values , 0 if while first config ///
/////////////////////////////////////////////////////
    
    var billingPlanId    =  ( configs )  ?  configs.billing_plan_id      :  1
    var userGroupId      =  ( configs )  ?  configs.user_group_id        :  0
    var timeZoneId       =  ( configs )  ?  configs.time_zone_id         :  0
    var userRoleIds      =  ( configs )  ?  configs.user_role_ids        :  0
//    var edgeGroupIds     =  ( configs )  ?  configs.edge_group_ids       :  0
//    var allowUserToSetup =  ( configs )  ?  configs.allow_user_to_setup  :  0

/// END assign config values , 0 if while first config ///
/////////////////////////////////////////////////////////

// remove spare elements
    $('input[name^="packageconf"]').remove()

// Init null option for selects
    var nullOption = '<option value = "0"></option>'
    
/// Create Servers Sellect element ///
/////////////////////////////////////

    var servers_label = LANG['onappcdnservers']
    var servers_html = '<select name="packageconfigoption[1]">'+  nullOption
        
    for ( var option in servers ) {
        var selected = ( option == serverId ) ? ' selected="selected"' : ''

        servers_html +=
            '<option value="'+option+'"'+selected+'>'+
                ''+ servers[option] +
            '</option>';
    }

    servers_html += '</select>'

/// END Create Servers sellect element ///
/////////////////////////////////////////

/// Create Billing Plan sellect element ///
//////////////////////////////////////////

    var billing_plan_label = LANG['onappcdnbillingplans']
    var billing_plan_html = '<select name="billing_plan_id">'+  nullOption

    for ( option in billingPlans ) {
        selected = ( option == billingPlanId ) ? ' selected="selected"' : ''

        billing_plan_html +=
            '<option value="'+option+'"'+selected+'>'+
                ''+ billingPlans[option] +
            '</option>';
    }

    billing_plan_html += '</select>'
        
/// END Create Billing Plan sellect element ///
//////////////////////////////////////////////

/// Create Time Zone sellect element ///
///////////////////////////////////////

    var time_zone_label = LANG['onappcdntimezones']
    var time_zone_html =
        '<select name="time_zone_id">'+ OnAppUsersTZs + '</select>'
    
// END Time Zone Plan sellect element ///
////////////////////////////////////////

/// Create User Group sellect element ///
////////////////////////////////////////

    var user_group_label = LANG['onappcdnusergroups']
    var user_group_html = '<select name="user_group_id">'

    for ( option in userGroups ) {
        selected = ( option == userGroupId ) ? ' selected="selected"' : ''

        user_group_html +=
            '<option value="'+option+'"'+selected+'>'+
                ''+ userGroups[option] +
            '</option>';
    }

    user_group_html += '</select>'

/// END Create User Group sellect element ///
////////////////////////////////////////////

/// Create User Role multiselect element ///
///////////////////////////////////////////

    var user_role_label = LANG['onappcdnuserroles']
    var user_role_html = '<select class="multiselect" name="user_role_ids[]" size="10" multiple >'
    
    for ( option in userRoles ) {
        selected = ( in_array( option, userRoleIds ) ) ? ' selected="selected"' : ''
        user_role_html +=
            '<option value="'+option+'"'+selected+'>'+
                ''+ userRoles[option] +
            '</option>';
    }

    user_role_html += '</select>'

/// END Create User Role multiselect element ///
///////////////////////////////////////////////

/// Append HTML ///
//////////////////

// The first table
    var firstTable = $('table>tbody').eq(4);
    firstTable.append( row_html( servers_label, servers_html ))

// The second table
    var secondTable = $('table>tbody').eq(5);
    secondTable.append( row_html( user_role_label, user_role_html ) )
    secondTable.append( row_html ( billing_plan_label, billing_plan_html ) )
    secondTable.append( row_html ( user_group_label, user_group_html ) )
    secondTable.append( row_html( time_zone_label, time_zone_html ))

// Set Table Title
    $('table').eq(5).before('<h4>' + LANG['onappcdnusersproperties'] + '</h4>')

// Hide two first rows
    secondTable.find('tr').eq(0).hide()
    secondTable.find('tr').eq(1).hide()

// Remove line break
    $('#tab2box div#tab_content br:first').remove()

/// END Append HTML ///
//////////////////////

/// Set Selects After Append ///
///////////////////////////////

$('select[name="time_zone_id"]').val(timeZoneId)

/// END Selects After Append ///
///////////////////////////////

/// ONCHANGE ACTIONS ///
///////////////////////

// assign server select onChange action
    var serverSelect = $("select[name='packageconfigoption[1]']");

    serverSelect.change( function () {
        form = $("form[name$='packagefrm']");
        form.submit();
    })

// assign servergroup select onChange action
    serverGroupSelect = $("select[name='servergroup']")

    serverGroupSelect.change( function () {
        deal_server_groups()
        serverSelect.val('0')
    })

/// END ONCHANGE ACTIONS ///
///////////////////////////

/// FUNCTIONS ///
////////////////

    function row_html(label, html) {
        return '<tr><td class="fieldlabel">'+label+'</td><td class="fieldarea">'+html+'</td></tr>';
    }

    function deal_server_groups () {
        var groupSelected = $(serverGroupSelect).val()

        serverSelect.children().each(function(){
            if ( groupSelected == 0 ){
                if ( get_servers_hasgroups() && jQuery.inArray( $(this).val(), get_servers_hasgroups() ) > -1 ) {
                    $(this).attr("disabled", "disabled").hide()
                }
                else {
                    $(this).removeAttr("disabled").show()
                }
            }
            else if ( serverGroupRels[groupSelected] && jQuery.inArray( $(this).val(), serverGroupRels[groupSelected] ) > -1 ) {
                $(this).removeAttr("disabled", "disabled").show()
            }
            else {
                $(this).attr("disabled", "disabled").hide()
            }
        })
    }

    deal_server_groups()

    function get_servers_hasgroups() {
        var values = new Array()
        var k = 0;
        $.each(serverGroupRels, function( i, val){
            $.each(val, function(i1, val1){
                 values[k] = val1
                 k += 1
            })
        })

        return values
    }

    function prepare_config_json () {
        var billing_plan_id_conf = $("select[name='billing_plan_id']").val()
        var time_zone_id_conf = $("select[name='time_zone_id']").val()
        var user_group_id_conf = $("select[name='user_group_id']").val()
        var user_role_ids_conf = $("select[name^='user_role_ids']").val()
        
        var configurations =
            '{"billing_plan_id":"' + billing_plan_id_conf + '", "time_zone_id":"'
            + time_zone_id_conf + '", "user_group_id":"' + user_group_id_conf +
            '", "user_role_ids":[' + user_role_ids_conf + ']}'
        
        var html =
            "<input type='hidden' value='"+ configurations +"' name='packageconfigoption[2]'/>"

        form.append(html)
    }

    // form submit action
    var form = $("form[name$='packagefrm']");

    form.submit(function() {
        prepare_config_json()
    });

    function in_array(needle, haystack){
        for(var i=0; i<haystack.length; i++)
            if(needle == haystack[i])
                return true;
        return false;
    }
    
/// AND FUNCTIONS ///
////////////////////

/// Error Handling ///
/////////////////////

if ( ! serverId || serverId == 0 || serverId == -1  ) { console.log('here')
    secondTable.find('tr').hide()
    secondTable.append('<tr><td>' + LANG['onappcdnnoserverselected'] + '</td></tr>')
}

/// END Error Handling ///
/////////////////////////

});
