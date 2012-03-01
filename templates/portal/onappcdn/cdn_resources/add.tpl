{literal}
<script type="text/javascript">
    
$(document).ready(function(){

    var advanced_container    = $("#advanced_settings")
    var advanced_checkbox     = $("#advanced_settings_input")
    var ip_access             = $('#ip_access')
    var domains               = $('#domains')
    var urlsigning_checkbox   = $('#urlsigning_input')
    var password_fields_tr    = '<tr>' + $('#passwords_table tr').eq(1).html() + '</tr>'
    var unauth_textarea       = $('#auth_html')
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

// UnAuth TextArea //
////////////////////

    unauth_textarea.val('<span style="color:red">Invalid username or password</span>').attr( 'disabled', true )

    $('#auth_html_wrapper').click( function(){
        if ( unauth_textarea.attr('disabled') == true ) {
            unauth_textarea.val('').removeAttr('disabled').focus()
        }
    })

    unauth_textarea.blur( function() {
        if( $(this).val() == '' ) {
            unauth_textarea.val('<span style="color:red">Invalid username or password</span>').attr( 'disabled', true )
        }
    })

// END UnAuth TextArea //
////////////////////////


    $('#plus_user').click(function() {
        $('#passwords_table').append( password_fields_tr )
    })

// CSS cosmetics
    $('table tr td:even').attr('class', 'label_width').attr('valign', 'top')

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

// Ip Access textarea //
///////////////////////
    ip_access.val('10.10.10.10, 20.20.20.0/24, ...').attr( 'disabled', true )

    $('#ip_wrapper').click( function(){
        if ( ip_access.attr('disabled') == true ) {
            ip_access.val('').removeAttr('disabled').focus()
        }
    })

    ip_access.blur( function() {
        if( $(this).val() == '' ) {
            ip_access.val('10.10.10.10, 20.20.20.0/24, ...').attr( 'disabled', true )
        }
    })
// Ip Access textarea //
///////////////////////
    
// Ip Domains textarea //
////////////////////////

    domains.val('www.yoursite.com mirror.yoursite.com').attr( 'disabled', true )

    $('#domains_wrapper').click( function(){
        if ( domains.attr('disabled') == true ) {
            domains.val('').removeAttr('disabled').focus()
        }
    })
        
    domains.blur( function() {
        if( $(this).val() == '' ) {
            domains.val('www.yoursite.com mirror.yoursite.com').attr( 'disabled', true )
        }
    })

// END Ip Domains textarea //
////////////////////////////
    
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
    <!-- | <a title="{$LANG.onappcdnbwstatistics}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=bandwidth_statistics&id={$id}">{$LANG.onappcdnbwstatistics}</a> -->
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
        <td>
            <input type="text" name="new_resource[cdn_hostname]" />
        </td>
    </tr>
    <tr>
        <td>
            {$_LANG.onappcdnorigins}
        </td>
        <td>
            <input type="text" name="new_resource[origin]" />
        </td>
    </tr>
    <tr>
        <td>
            {$_LANG.onappcdnresourcetype}
        </td>
        <td>
            <select name="new_resource[type]">
                <option value="HTTP_PULL">HTTP PULL</option>
            </select>
        </td>
    </tr>
    <tr>
        <td>
            {$_LANG.onappcdnadvancedsettings}
        </td>
        <td>
            <input id="advanced_settings_input" type="checkbox" name="new_resource[advanced_settings]" />
        </td>
    </tr>
</table>


<div id="advanced_settings">

    <h4>{$_LANG.onappcdnipaccess}</h4> <hr />

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
            <td>{$_LANG.onappcdnipaccesspolicy}</td>
            <td>
                <select name="new_resource[ip_access_policy]">
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
                <div id="ip_wrapper">
                <textarea id="ip_access" cols="40" rows="5" name="new_resource[ip_addresses]" >
                </textarea>
                </div>
            </td>
        </tr>
    </table>

    <h4>{$_LANG.onappcdncountryaccess}</h4> <hr />

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
            <td>{$_LANG.onappcdncountryaccesspolicy}</td>
            <td>
                <select name="new_resource[country_access_policy]">
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
            <td>
                <div id="country_wrapper">
                <select id="country_access" name="new_resource[countries][]" multiple> 
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
            <td>
                <select id="hotlinkpolicy" name="new_resource[hotlink_policy]">
                    <option value="NONE">{$_LANG.onappcdndisabled}</option>
                    <option value="ALLOW_BY_DEFAULT">{$_LANG.onappcdnallowbydefault}</option>
                    <option value="BLOCK_BY_DEFAULT">{$_LANG.onappcdnblockbydefault}</option>
                </select>
            </td>
        </tr>
        <tr id="domains_tr" >
            <td>{$_LANG.onappcdnexceptfordomains}</td>
            <td>
                <div id="domains_wrapper">
                    <textarea id="domains" cols="40" rows="5" name="new_resource[domains]" >
                    </textarea>
                </div>
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
            <td>
                <input id="urlsigning_input" value="1" type="checkbox" name="new_resource[url_signing_on]" />
            </td>
        </tr>
        <tr id="urlsigning_tr">
            <td>{$_LANG.onappcdnurlsigningkey}</td>
            <td>
                <input type="text" name="new_resource[url_signing_key]" />
            </td>
        </tr>
    </table>

    <h4>{$_LANG.onappcdncacheexpiry}</h4> <hr />

    <h5>{$_LANG.onappcdncacheexpiryinfo}</h5>

    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
            <td>{$_LANG.onappcdncacheexpiry}</td>
            <td>
                <input id="cache_input" type="text" name="new_resource[cache_expiry]" />
            </td>
        </tr>

    </table>

    <h4>{$_LANG.onappcdnpassword}</h4> <hr />
    <h5>{$_LANG.onappcdnclearbothfields}</h5>
    
    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
            <td>{$_LANG.onappcdnenablepassword}</td>
            <td>
                <input id="passwordon_input" value="1" type="checkbox" name="new_resource[password_on]" />
            </td>
        </tr>
    </table>
<div id="passwords_container">
    <table cellspacing="0" cellpadding="10" border="0" width="100%">
        <tr>
            <td>{$_LANG.onappcdnunauthorizedhtml}</td>
            <td>
                <div id="auth_html_wrapper">
                <textarea id="auth_html" cols="40" rows="5" placeholder="adfasdfs" name="new_resource[password_unauthorized_html]" >
                </textarea>
                </div>
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
                            <input id="username_input" type="text" name="new_resource[form_pass][user][]" />
                        </td>
                        <td>
                            <input id="password_input" type="text" name="new_resource[form_pass][pass][]" />
                        </td>
                    </tr>
                </table>
                <input id="plus_user" type="button" value="+ user" />
            </td>
        </tr>
    </table>

</div>
</div>

<h4>{$_LANG.onappcdnedgegroups}</h4>
<hr />


<table cellspacing="0" cellpadding="10" border="0" width="100%">

{foreach item=group from=$edge_group_baseresources}

    <tr>
        <td>
            {$group.label} - {$whmcs_client_details.currencyprefix}{$group.price|round:2} {$whmcs_client_details.currencycode} {$_LANG.onappcdnperGB} <br />
                {foreach item=location from=$group.locations}
                    {$location->_city}, {$location->_country}    <br />
                {/foreach}
        </td>
        <td>
            <input id="advanced_settings_input" value="{$group.id}" type="checkbox" name="new_resource[edge_group_ids][]" />
        </td>
    </tr>

{/foreach}
</table>
<input type="hidden" name="add" value="1" /> <br /> <br />
<input type="submit" value="{$_LANG.onappcdncreateresource}" />
</form>
<br /><br />
