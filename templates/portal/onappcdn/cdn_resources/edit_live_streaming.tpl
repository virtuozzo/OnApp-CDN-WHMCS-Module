{literal}
<script type="text/javascript">

function in_array(needle, haystack){
    for(var i=0; i<haystack.length; i++)
        if(needle == haystack[i])
            return true;
    return false;
}
  
$(document).ready(function(){

    var advanced_container    = $("#advanced_settings")
    var advanced_checkbox     = $("#advanced_settings_input")
    var urlsigning_checkbox   = $('#urlsigning_input')
    var password_fields_tr    = '<tr>' + $('#passwords_table tr').eq(1).html() + '</tr>'
    var passwords_container   = $('#passwords_container')


// Advanced Settings Checkbox //
///////////////////////////////

    passwords_container.hide()

    $('#passwordon_input').change(function(){
        if (this.checked) {
            passwords_container.show()
        }
        else {
            passwords_container.hide()
        }
    });
// END Advanced Settings Checkbox //
///////////////////////////////////

    $('#plus_user').click(function() {
        $('#passwords_table').append( password_fields_tr )
    })

// Advanced Settings Checkbox //
///////////////////////////////
    advanced_container.hide()
    advanced_checkbox.change(function(){
        if (this.checked) {
            advanced_container.slideDown()
        }
        else {
            advanced_container.slideUp()
        }
    });
// END Advanced Settings Checkbox //
///////////////////////////////////
    
// Hotlink Policy Checkbox //
////////////////////////////
    $('#domains_tr').hide()

    $('#hotlinkpolicy').change( function(){
        if ( $(this).val() == 'NONE' ) {
            $('#domains_tr').hide()
        }
        else {
            $('#domains_tr').show()
        }
    })

// END Hotlink Policy Checkbox //
////////////////////////////////

// Advanced Settings Checkbox //
///////////////////////////////
    
    $('#urlsigning_tr').hide()

    urlsigning_checkbox.change(function(){
        if (this.checked) {
            $('#urlsigning_tr').show()
        }
        else {
            $('#urlsigning_tr').hide()
        }
    });
// END Advanced Settings Checkbox //
///////////////////////////////////

// Check advanced checkbox
    advanced_checkbox.attr('checked', 'checked');
    advanced_checkbox.change();

// Select countries
    var countries_ids = {/literal}{$countries_ids}{literal}
        
    if ( countries_ids ) {
        $('#country_access option').each( function(){
            if ( in_array( $(this).val(), countries_ids ) ) {
                $(this).attr('selected', 'true')
            }
        })
    }

 // Check Url Signing Url checkbox
 {/literal}
    {if $advanced_details->_url_signing_on eq true}
        urlsigning_checkbox.attr( 'checked', 'cheched' ).change()
    {/if}
{literal}

// Hot policy
{/literal}
    hotlink_policy = '{$advanced_details->_hotlink_policy}'
{literal}

    $('#hotlinkpolicy option').each( function() {
        if ( this.value == hotlink_policy ) {
            this.selected = true
            $('#hotlinkpolicy').change()
        }
    })

 // Check Password checkbox
 {/literal}
    {if $advanced_details->_password_on eq true}
        $('#passwordon_input').attr( 'checked', 'checked' ).change()
    {/if}
{literal}

// Fill up passwords fields
{/literal}
  var passwords_html = '{$passwords_html}'
{literal}

$('#passwords_table tr').eq(1).remove()
$('#passwords_table').append( passwords_html )


});

</script>
{/literal}


{if isset($errors)}
    <div class="errorbox">
        {$errors}
    </div>
{/if}

{if isset($messages)}
    <div class="successbox">
        {$messages}
    </div>
{/if}

  <div class="contentbox">
      <a title="{$LANG.onappcdnresources}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=resources&id={$id}">{$LANG.onappcdnresources}</a>
    {*-- | <a title="{$LANG.onappcdnbwstatistics}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=bandwidth_statistics&id={$id}">{$LANG.onappcdnbwstatistics}</a> *}
  </div>

<h2>{$_LANG.onappcdneditresource}</h2>

{$_LANG.onappcdnresourceadddescription}

<h4>{$_LANG.onappcdnresourceproperties}</h4>
<hr />
<h5>{$_LANG.onappcdnresourcepropertiesinfo}</h5>
<form action="" method="post" >
<table cellspacing="0" cellpadding="10" border="0" width="100%">
    <tr>
        <td>
            {$_LANG.onappcdnhostname}
        </td>
        <td class="label_width" valign="top">
            <input class="textfield" type="text" value="{$resource->_cdn_hostname}" name="resource[cdn_hostname]" />
        </td>
    </tr>
    
 {*   <tr>
        <td>
            {$_LANG.onappcdnpublishingpoint}
        </td>
        <td class="label_width" valign="top">
            <select class="selectfield" name="resource[publishing_point]">
                <option value="internal" >Internal</option>
                <option value="external">External</option>
            </select>
        </td>
    </tr> 

    <tr class="external_publishing_point_tr">
        <td>
            {$_LANG.onappcdnexternalpublishingurl}
        </td>
        <td class="label_width" valign="top">
            <input class="textfield" id="external_publishing_url_input" type="text" name="resource[external_publishing_url]" />
        </td>
    </tr>
    
    <tr class="external_publishing_point_tr">
        <td>
            {$_LANG.onappcdnfailoverexternalpublishingurl}
        </td>
        <td class="label_width" valign="top">
            <input class="textfield" id="failover_external_publishing_url_input" type="text" name="resource[failover_external_publishing_url]" />
        </td>
    </tr>   *} 
    
    <tr>
        <td>
            {$_LANG.onappcdnadvancedsettings}
        </td>
        <td class="label_width" valign="top">
            <input id="advanced_settings_input" type="checkbox" name="resource[advanced_settings]" />
        </td>
    </tr>
</table>


<div id="advanced_settings">
    <h4>{$_LANG.onappcdnantileech}</h4> <hr />
    <h5>{$_LANG.onappcdnantileechinfo}</h5>

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
            <td>{$_LANG.onappcdnenableautoleech}</td>
            <td class="label_width" valign="top">
                <select class="selectfield" id="anti_leech_on" name="resource[anti_leech_on]">
                    <option value="NONE">{$_LANG.onappcdndisabled}</option>
                    <option value="ALLOW_BY_DEFAULT">{$_LANG.onappcdnallowbydefault}</option>
                    <option value="BLOCK_BY_DEFAULT">{$_LANG.onappcdnblockbydefault}</option>
                </select>
            </td>
        </tr>
        <tr id="anti_leech_domains" >
            <td>{$_LANG.onappcdnalloweddomains}</td>
            <td class="label_width" valign="top">
                <textarea placeholder="www.yoursite.com mirror.yoursite.com" id="" cols="40" rows="5" name="resource[anti_leech_domains]" >{$resource->anti_leech_domains}</textarea>
            </td>
        </tr>
    </table>  
            
<h4>{$_LANG.onappcdncountryaccess}</h4> <hr />

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
            <td>{$_LANG.onappcdncountryaccesspolicy}</td>
            <td class="label_width" valign="top">
                <select class="selectfield" id="country_access_policy" name="resource[country_access_policy]">
                    <option value="NONE" {if $advanced_details->_country_access_policy eq 'NONE'}selected{/if}>{$_LANG.onappcdndisabled}</option>
                    <option value="ALLOW_BY_DEFAULT" {if $advanced_details->_country_access_policy eq 'ALLOW_BY_DEFAULT'}selected{/if}>{$_LANG.onappcdnallowbydefault}</option>
                    <option value="BLOCK_BY_DEFAULT" {if $advanced_details->_country_access_policy eq 'BLOCK_BY_DEFAULT'}selected{/if}>{$_LANG.onappcdnblockbydefault}</option>
                </select>
            </td>
        </tr>
        <tr>
            <td >
                {$_LANG.onappcdncountryaccess}
            </td>
            <td class="label_width" valign="top">
                <div id="country_wrapper">
                <select class="selectfield multiselect" id="country_access" name="resource[countries][]" multiple>
                    {include file="$template/onappcdn/cdn_resources/countries_options.tpl"}
                </select>
                </div>
            </td>
        </tr>
    </table>            

    <h4>{$_LANG.onappcdnsecurewowza}</h4> <hr />

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr >
            <td>
                {$_LANG.onappcdnenablesecurewowza}
            </td>
            <td class="label_width" valign="top">
                <input id="secure_wowza_on_input" value="1" type="checkbox" name="resource[secure_wowza_on]" {if $session_resource.secure_wowza eq true}checked{/if}/>
            </td>
        </tr>
        <tr id="secure_wowza_on_tr">
            <td>{$_LANG.onappcdntokenforedgeflashplayer}</td>
            <td class="label_width" valign="top">
                <input class="textfield" value="{$resource->secure_wowza_token}" type="text" name="resource[secure_wowza_token]" />
            </td>
        </tr>
    </table>
           
            
</div>   <!--end advanced -->                 

<h4>{$_LANG.onappcdnedgegroups}</h4>
<hr />


<table cellspacing="0" cellpadding="10" border="0" width="100%">

{foreach item=group from=$edge_group_baseresources}

    <tr>
        <td>
             <b>{$group.label}</b> - {$whmcs_client_details.currencyprefix} {$group.price*$whmcs_client_details.currencyrate} {$whmcs_client_details.currencycode} <br />
                {foreach item=location from=$group.locations}
                    {$location->_city|ucfirst}, {$location->_country}    <br />
                {/foreach}
        </td>
        <td class="label_width" valign="top">
            <input id="advanced_settings_input" value="{$group.id}" type="checkbox" name="resource[edge_group_ids][]" {if $group.id|in_array:$edge_group_ids}checked{/if} />
        </td>
    </tr>

{/foreach}
</table>
{*
<div id="internal_publishing_point_div">
    <h4>{$_LANG.onappcdnpublishingpoint}</h4> <hr />

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
        <td>
            {$_LANG.onappcdninternalpublishingpoint}
        </td>
        <td class="label_width" valign="top">
            <select id="internal_publishing_point_select" class="selectfield" name="resource[internal_publishing_point]">
                <option value></option>
            </select>
        </td>
        </tr>
        <tr>
        <td>
            {$_LANG.onappcdnfailoverinternalpublishingpoint}
        </td>
        <td class="label_width" valign="top">
            <select id="failover_internal_publishing_point_select" class="selectfield" name="resource[failover_internal_publishing_point]">
                <option value></option>
            </select>
        </td> 
        </tr>      
    </table>
</div> *}

<input type="hidden" name="edit" value="1" /> <br /> <br />
<input type="hidden" name="resource[resource_type]" value="STREAM_LIVE" />
<input type="submit" value="{$_LANG.onappcdnapplychanges}" />

</form>
<br /><br />
