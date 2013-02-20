{literal}
<script type="text/javascript">
    
$(document).ready(function(){

    var advanced_container    = $("#advanced_settings")
    var advanced_checkbox     = $("#advanced_settings_input")
    var urlsigning_checkbox   = $('#urlsigning_input')
    var password_fields_tr    = '<tr>' + $('#passwords_table tr').eq(1).html() + '</tr>'
    var passwords_container   = $('#passwords_container')


function in_array(needle, haystack){
    for(var i=0; i<haystack.length; i++)
        if(needle == haystack[i])
            return true;
    return false;
}
// Password Container Checkbox //
////////////////////////////////

    passwords_container.hide()

    $('#passwordon_input').change(function(){
        if (this.checked) {
            passwords_container.show()
        }
        else {
            passwords_container.hide()
        }
    });
// END Password Container //
///////////////////////////

    $('#plus_user').click(function() {
        $('#passwords_table').append( password_fields_tr )
    })

// CSS cosmetics
//    $('table tbody tr td:even').attr('class', 'label_width').attr('valign', 'top')

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

{/literal}
    {if $session_resource.advanced_settings eq true}
        advanced_checkbox.attr('checked', 'checked').change()
    {/if}
{literal}
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

{/literal}
    hotlink_policy = '{$session_resource.hotlink_policy}'
{literal}

    $('#hotlinkpolicy option').each( function() {
        if ( this.value == hotlink_policy ) {
            this.selected = true
            $('#hotlinkpolicy').change()
        }
    })
        
// END Hotlink Policy Checkbox //
////////////////////////////////


// Country Access Policy //
//////////////////////////

{/literal}
    country_access_policy = '{$session_resource.country_access_policy}'
{literal}

    $('#country_access_policy option').each( function() {
        if ( this.value == country_access_policy ) {
            this.selected = true
            $('#country_access_policy').change()
        }
    })

// END Country Access Policy //
//////////////////////////////

// Selecting Countries //
////////////////////////

// Select countries
    {/literal} 
        countries_ids = {$countries}
    {literal}
    if ( countries_ids ) {console.log(countries_ids)
        $('#country_access option').each( function(){
            if ( in_array( this.value, countries_ids ) ) {
                this.selected = true
            }
        })
    }
        
// END Selecting Countries //
////////////////////////////

// Ip policy //
//////////////

    {/literal}
        ip_access_policy = '{$session_resource.ip_access_policy}'
    {literal}

    $('#ip_access_policy option').each( function() {
        if ( this.value == ip_access_policy ) {
            this.selected = true
            $('#ip_access_policy').change()
        }
    })

// END Ip Policy //
//////////////////

// URL signing Checkbox //
/////////////////////////
    
    $('#urlsigning_tr').hide()

    urlsigning_checkbox.change(function(){
        if (this.checked) {
            $('#urlsigning_tr').show()
        }
        else {
            $('#urlsigning_tr').hide()
        }
    });

// Check Url Signing Url checkbox
 {/literal}
    {if $session_resource.url_signing_on eq true}
        urlsigning_checkbox.attr( 'checked', 'checked' ).change()
    {/if}
{literal}
// END URL Signing Checkbox //
/////////////////////////////

// Fill up passwords fields //
/////////////////////////////

// Check Password checkbox
 {/literal}
    {if $session_resource.password_on eq true}
        $('#passwordon_input').attr( 'checked', 'checked' ).change()
    {/if}
{literal}

    
{/literal}
  var passwords_html = '{$passwords_html}'
{literal}

$('#passwords_table tr').eq(1).remove()
$('#passwords_table').append( passwords_html )

// END Fill up password fields //
////////////////////////////////
    var ofselect = $('select[name="resource[resource_type]"]')
        
    ofselect.change( function(){
        var ofinput = $('input#origin_ftppass_field') 
        var labeltd = ofinput.parent().prev()
         
         if ( $(this).val() == 'HTTP_PUSH' ){
             ofinput.attr('name', 'resource[ftp_password]').val('{/literal}{$session_resource.ftp_password}{literal}')
             labeltd.html('{/literal}{$LANG.onappcdnftppassword}{literal}')    
         } else {
             ofinput.attr('name', 'resource[origin]').val('{/literal}{$session_resource.origin}{literal}')
             labeltd.html('{/literal}{$LANG.onappcdnorigins}{literal}')    
         }
    })
        
    ofselect.change()    
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
    {* | <a title="{$LANG.onappcdnbwstatistics}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=bandwidth_statistics&id={$id}">{$LANG.onappcdnbwstatistics}</a> *}
  </div>

<h2>{$_LANG.onappcdnnewresource}</h2>

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
            <input class="textfield" type="text" value="{$session_resource.cdn_hostname}" name="resource[cdn_hostname]" />
        </td>
    </tr>
    
    <tr>
        <td>
            {$_LANG.onappcdnresourcetype}
        </td>
        <td class="label_width" valign="top">
            
            <select class="selectfield" name="resource[resource_type]">
                <option value="HTTP_PULL" {if $session_resource.resource_type == 'HTTP_PULL'}selected{/if}>HTTP PULL</option>
<!--                <option value="HTTP_PUSH" {if $session_resource.resource_type == 'HTTP_PUSH'}selected{/if}>HTTP PUSH</option> -->
            </select>
        </td>
    </tr>   
    <tr>
        <td>
            {$_LANG.onappcdnorigins}
        </td>
        <td class="label_width" valign="top">
            <input id="origin_ftppass_field" class="textfield" type="text" value="{$session_resource.origin}" name="resource[origin]" />
        </td>
    </tr>
    <tr><td colspan="2"><h5 class="without_padding">{$_LANG.onappcdnsslmodeinfo}</h5></td></tr>
    <tr>
        <td>
            {$_LANG.onappcdnsslmode}
        </td>
        <td class="label_width" valign="top">
            <select class="selectfield" name="resource[ssl_on]">
                <option value="0" {if $session_resource.ssl_on == '0'}selected{/if}>{$_LANG.onappcdnwithoutssl}</option>
                <option value="1" {if $session_resource.ssl_on == '1'}selected{/if}>{$_LANG.onappcdnwithssl}</option>
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
                <select class="selectfield" id="ip_access_policy" name="resource[ip_access_policy]">
                    <option value="NONE">{$_LANG.onappcdndisabled}</option>
                    <option value="ALLOW_BY_DEFAULT">{$_LANG.onappcdnallowbydefault}</option>
                    <option value="BLOCK_BY_DEFAULT">{$_LANG.onappcdnblockbydefault}</option>
                </select>
            </td>
        </tr>
        <tr>
            <td >
                {$_LANG.onappcdnipaccess}
            </td>
            <td>
                <textarea id="ip_access" cols="40" rows="5" placeholder="10.10.10.10, 20.20.20.0/24, ..." name="resource[ip_addresses]" >{$session_resource.ip_addresses}</textarea>
            </td>
        </tr>
    </table>

    <h4>{$_LANG.onappcdncountryaccess}</h4> <hr />

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
            <td>{$_LANG.onappcdncountryaccesspolicy}</td>
            <td class="label_width" valign="top">
                <select class="selectfield" id="country_access_policy" name="resource[country_access_policy]">
                    <option value="NONE">{$_LANG.onappcdndisabled}</option>
                    <option value="ALLOW_BY_DEFAULT">{$_LANG.onappcdnallowbydefault}</option>
                    <option value="BLOCK_BY_DEFAULT">{$_LANG.onappcdnblockbydefault}</option>
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
                <textarea placeholder="www.yoursite.com mirror.yoursite.com" id="domains" cols="40" rows="5" name="resource[domains]" >{$session_resource.domains}</textarea>
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
            </td>
        </tr>
        <tr id="urlsigning_tr">
            <td>{$_LANG.onappcdnurlsigningkey}</td>
            <td class="label_width" valign="top">
                <input class="textfield" value="{$session_resource.url_signing_key}" type="text" name="resource[url_signing_key]" />
            </td>
        </tr>
    </table>

    <h4>{$_LANG.onappcdncacheexpiry}</h4> <hr />

    <h5>{$_LANG.onappcdncacheexpiryinfo}</h5>

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
            <td>{$_LANG.onappcdncacheexpiry}</td>
            <td class="label_width" valign="top">
                <input class="textfield" value="{$session_resource.cache_expiry}" id="cache_input" type="text" name="resource[cache_expiry]" />
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
                <textarea id="auth_html" cols="40" rows="5" placeholder="<span style='color: red'>Invalid username or password</span>" name="resource[password_unauthorized_html]" >{$session_resource.password_unauthorized_html}</textarea>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td class="label_width" valign="top">
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
                <input id="mp4_pseudo_on_input" value="1" type="checkbox" name="resource[mp4_pseudo_on]" {if $session_resource.mp4_pseudo_on eq true}checked{/if}/>
            </td>
        </tr>
        <tr >
            <td>
                {$_LANG.onappcdnpenableflvpseudostreaming}
            </td>
            <td class="label_width" valign="top">
                <input id="flv_pseudo_on_input" value="1" type="checkbox" name="resource[flv_pseudo_on]" {if $session_resource.flv_pseudo_on eq true}checked{/if} />
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
                <input id="ignore_set_cookie_on_input" value="1" type="checkbox" name="resource[ignore_set_cookie_on]" {if $session_resource.ignore_set_cookie_on eq true}checked{/if} />
            </td>
        </tr>
    </table>            
            
</div>   <!--end advanced -->                 
                    

<h4>{$_LANG.onappcdnedgegroups}</h4>
<hr />


<table cellspacing="0" cellpadding="10" border="0" width="100%">

{foreach item=group from=$edge_group_baseresources}

    <tr>
            <td valign="top">
                <b>{$group.label}</b> - {$whmcs_client_details.currencyprefix} {$group.price*$whmcs_client_details.currencyrate} {$whmcs_client_details.currencycode} <br />
                
                {foreach item=location from=$group.locations}
                    {$location->_city|ucfirst}, {$location->_country}    <br />
                {/foreach}
            </td> 
        <td class="label_width" valign="top">
            <div >
            <input id="advanced_settings_input" value="{$group.id}" type="checkbox" name="resource[edge_group_ids][]" 
          {if isset($session_resource.edge_group_ids) }{if $group.id|in_array:$session_resource.edge_group_ids}checked{/if}{/if}/>
        </div>
        </td>
    </tr>

{/foreach}
</table>
<input type="hidden" name="add" value="1" /> <br /> <br />
<input type="submit" value="{$_LANG.onappcdncreateresource}" />
<input type="hidden" name="template" value="add" />
</form>
<br /><br />
