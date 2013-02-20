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
        
        console.log(countries_ids)

    if ( countries_ids ) {
        $('#country_access option').each( function(){
            console.log( $(this).val())
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

// TODO add form validation
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
{*    | <a title="{$LANG.onappcdnbwstatistics}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=bandwidth_statistics&id={$id}">{$LANG.onappcdnbwstatistics}</a> *}
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
            <input class="textfield" value="{$resource->_cdn_hostname}" type="text" name="resource[cdn_hostname]" />
        </td>
    </tr>    
    
    <tr>
        <td>
            {$_LANG.onappcdnresourcetype}
        </td>
        <td class="label_width" valign="top">
            <select class="selectfield" name="resource[resource_type]" disabled>
                <option value="HTTP_PULL" {if $resource->_resource_type == 'HTTP_PULL'}selected{/if}>HTTP PULL</option>
<!--                <option value="HTTP_PUSH" {if $resource->_resource_type == 'HTTP_PUSH'}selected{/if}>HTTP PUSH</option> -->
            </select>
        </td>
    </tr>
    {if $ssl_on eq false}
    <tr>
        <td>
            {$_LANG.onappcdnorigins}
        </td>
        <td class="label_width" valign="top">
            <input class="textfield" value="{foreach item=origin from=$resource->_origins}{$origin->_value}{/foreach}" type="text" name="resource[origin]" />
        </td>
    </tr>
    {/if}
    
    <tr>
        <td>
            {$_LANG.onappcdnsslmode}
        </td>
        <td class="label_width" valign="top">
            <select class="selectfield" name="resource[ssl_on]" disabled>
                <option value="0" {if $ssl_on eq false}selected{/if}>{$_LANG.onappcdnwithoutssl}</option>
                <option value="1" {if $ssl_on eq true}selected{/if}>{$_LANG.onappcdnwithssl}</option>
            </select>
        </td>
    </tr>

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

    <h4>{$_LANG.onappcdnipaccess}</h4> <hr />

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
            <td>{$_LANG.onappcdnipaccesspolicy}</td>
            <td class="label_width" valign="top">
                <select class="selectfield" name="resource[ip_access_policy]">
                    <option value="NONE" {if $advanced_details->_ip_access_policy eq 'NONE'}selected{/if}>{$_LANG.onappcdndisabled}</option>
                    <option value="ALLOW_BY_DEFAULT" {if $advanced_details->_ip_access_policy eq 'ALLOW_BY_DEFAULT'}selected{/if}>{$_LANG.onappcdnallowbydefault}</option>
                    <option value="BLOCK_BY_DEFAULT" {if $advanced_details->_ip_access_policy eq 'BLOCK_BY_DEFAULT'}selected{/if}>{$_LANG.onappcdnblockbydefault}</option>
                </select>
            </td>
        </tr>
        <tr>
            <td >
                {$_LANG.onappcdnipaccess}
            </td>
            <td class="label_width" valign="top">
                <div id="ip_wrapper">
                <textarea placeholder="10.10.10.10, 20.20.20.0/24, ..." id="ip_access" cols="40" rows="5" name="resource[ip_addresses]" >{$advanced_details->_ip_addresses}</textarea>
                </div>
            </td>
        </tr>
    </table>

    <h4>{$_LANG.onappcdncountryaccess}</h4> <hr />

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
            <td>{$_LANG.onappcdncountryaccesspolicy}</td>
            <td class="label_width" valign="top">
                <select class="selectfield" name="resource[country_access_policy]">
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
                <select class="selectfield multiselect" id="country_access" name="resource[countries][]" multiple>
                    {include file="$template/onappcdn/cdn_resources/countries_options.tpl"}
                </select>
            </td>
        </tr>
    </table>

    <h4>{$_LANG.onappcdnhotlinkpolicy}</h4> <hr />

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
            <td>{$_LANG.onappcdnhotlinkpolicy}</td>
            <td class="label_width" valign="top">
                <select class="selectfield" id="hotlinkpolicy" name="resource[hotlink_policy]">
                    <option value="NONE">{$_LANG.onappcdndisabled}</option>
                    <option value="ALLOW_BY_DEFAULT">{$_LANG.onappcdnallowbydefault}</option>
                    <option value="BLOCK_BY_DEFAULT">{$_LANG.onappcdnblockbydefault}</option>
                </select>
            </td>
        </tr>
        <tr id="domains_tr" >
            <td>{$_LANG.onappcdnexceptfordomains}</td>
            <td class="label_width" valign="top">
                <textarea placeholder="www.yoursite.com mirror.yoursite.com" id="domains" cols="40" rows="5" name="resource[domains]" >{$advanced_details->_domains}</textarea>
            </td>
        </tr>
    </table>

    <h4>{$_LANG.onappcdnurlsigning}</h4> <hr />

    <h5>{$_LANG.onappcdnurlsigninginfo}</h5>

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr >
            <td>
                {$_LANG.onappcdnenableurlsigning}
            </td>
            <td class="label_width" valign="top">
                <input id="urlsigning_input" value="1" type="checkbox" name="resource[url_signing_on]" />
            </td >
        </tr>
        <tr id="urlsigning_tr">
            <td>{$_LANG.onappcdnurlsigningkey}</td>
            <td class="label_width" valign="top">
                <input class="textfield" type="text" value="{$advanced_details->_url_signing_key}" name="resource[url_signing_key]" />
            </td>
        </tr>
    </table>

    <h4>{$_LANG.onappcdncacheexpiry}</h4> <hr />

    <h5>{$_LANG.onappcdncacheexpiryinfo}</h5>

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
            <td>{$_LANG.onappcdncacheexpiry}</td>
            <td class="label_width" valign="top">
                <input class="textfield" id="cache_input" type="text" value="{$advanced_details->_cache_expiry}" name="resource[cache_expiry]" />
            </td>
        </tr>

    </table>

    <h4>{$_LANG.onappcdnpassword}</h4> <hr />
    <h5>{$_LANG.onappcdnclearbothfields}</h5>
    
    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
            <td>{$_LANG.onappcdnenablepassword}</td>
            <td class="label_width" valign="top">
                <input id="passwordon_input" value="1" type="checkbox" name="resource[password_on]" />
            </td>
        </tr>
    </table>
<div id="passwords_container">
    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
            <td>{$_LANG.onappcdnunauthorizedhtml}</td>
            <td class="label_width" valign="top">
                <textarea id="auth_html" cols="40" rows="5" placeholder="<span style='color: red'>Invalid username or password</span>" name="resource[password_unauthorized_html]" >{$advanced_details->_password_unauthorized_html}</textarea>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>
                <table id="passwords_table" cellspacing="0" cellpadding="10" border="0" width="100%">
                    <tr>
                        <th>{$_LANG.onappcdnusername}</th>
                        <th>{$_LANG.onappcdnpassword}</th>
                    </tr>
                    <tr>
                        <td>
                            <input id="username_input" type="text" name="resource[form_pass][user][]" />
                        </td>
                        <td>
                            <input id="password_input" type="text" name="resource[form_pass][pass][]" />
                        </td>
                    </tr>
                </table>
                <input id="plus_user" type="button" value="+ user" />
            </td>
        </tr>
    </table>

</div>
                    
    <h4>{$_LANG.onappcdnpseudostreaming}</h4> <hr />

    <h5>{$_LANG.onappcdnpseudostreaminginfo}</h5>

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr >
            <td>
                {$_LANG.onappcdnenablemp4pseudostreaming}
            </td>
            <td class="label_width" valign="top">
                <input id="mp4_pseudo_on_input" value="1" type="checkbox" name="resource[mp4_pseudo_on]" {if $advanced_details->_mp4_pseudo_on eq true}checked{/if} />
            </td>
        </tr>
        <tr >
            <td>
                {$_LANG.onappcdnpenableflvpseudostreaming}
            </td>
            <td class="label_width" valign="top">
                <input id="flv_pseudo_on_input" value="1" type="checkbox" name="resource[flv_pseudo_on]" {if $advanced_details->_flv_pseudo_on eq true}checked{/if}/>
            </td>
        </tr>        
    </table>
            
    <h4>{$_LANG.onappcdningnoresetcookie}</h4> <hr />

    <h5>{$_LANG.onappcdningnoresetcookieinfo}</h5>

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr >
            <td>
                {$_LANG.onappcdningnoresetcookie}
            </td>
            <td class="label_width" valign="top">
                <input id="ignore_set_cookie_on_input" value="1" type="checkbox" name="resource[ignore_set_cookie_on]" {if $advanced_details->_ignore_set_cookie_on eq true}checked{/if} />
            </td>
        </tr>
    </table>                     
                    
</div>

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
<input type="hidden" name="resource[id]" value="{$resource_id}" />
<input type="hidden" name="edit" value="1" /> <br /> <br />
<input type="submit" value="{$_LANG.onappcdnapplychanges}" />
</form>
<br /><br />
