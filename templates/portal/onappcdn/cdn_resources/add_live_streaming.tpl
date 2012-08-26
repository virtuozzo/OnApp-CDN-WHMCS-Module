{literal}
<script type="text/javascript">
    
$(document).ready(function(){

    var advanced_container    = $("#advanced_settings")
    var advanced_checkbox     = $("#advanced_settings_input")
    var secure_wowza_on_checkbox   = $('#secure_wowza_on_input')

    function in_array(needle, haystack){
        for(var i=0; i<haystack.length; i++)
            if(needle == haystack[i])
                return true;
        return false;
    }
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
    
// Anti leech domains Checkbox //
////////////////////////////
    $('#anti_leech_domains').hide()

    $('#anti_leech_on').change( function(){
        if ( $(this).val() == 'NONE' ) {
            $('#anti_leech_domains').hide()
        }
        else {
            $('#anti_leech_domains').show()
        }
    })

{/literal}
    var anti_leech_on = '{$session_resource.anti_leech_on}'
{literal}

    $('#anti_leech_on option').each( function() {
        if ( this.value == anti_leech_on ) {
            this.selected = true
            $('#anti_leech_on').change()
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
    if ( countries_ids ) {
        $('#country_access option').each( function(){
            if ( in_array( this.value, countries_ids ) ) {
                this.selected = true
            }
        })
    }
// END Selecting Countries //
////////////////////////////

// URL signing Checkbox //
/////////////////////////
    $('#secure_wowza_on_tr').hide()

    secure_wowza_on_checkbox.change(function(){
        if (this.checked) {
            $('#secure_wowza_on_tr').show()
        }
        else {
            $('#secure_wowza_on_tr').hide()
        }
    });
// Check Secure wowza checkbox
 {/literal}
    {if $session_resource.secure_wowza_on eq true}
        secure_wowza_on_checkbox.attr( 'checked', 'checked' ).change()
    {/if}
{literal}
// END Secure wowza Checkbox //
//////////////////////////////
    
// Internal Publishing Point //
////////////////////////////// 
    {/literal} 
        edge_group_locations_ids = {$edge_group_locations_ids}
        {if $session_resource.publishing_point}
            session_publishing_point = '{$session_resource.publishing_point}'
        {/if}
    {literal} 
        
        if ( typeof session_publishing_point != 'undefined'){
            $('select[name="resource[publishing_point]"]').val(session_publishing_point)
        }        
        
        $('select[name="resource[publishing_point]"]').change( function(){
            if( $(this).val() == 'internal' ) {
                $('#internal_publishing_point_div').show()
                $('tr.external_publishing_point_tr').hide()
                
                fill_internal_select_options()
                if( typeof session_publishing_point != 'undefined' &&
                    session_publishing_point == 'internal'
                ){
                    $('#internal_publishing_point_select').val({/literal}'{$session_resource.internal_publishing_point}'{literal})
                    $('#failover_internal_publishing_point_select').val({/literal}'{$session_resource.failover_internal_publishing_point}'{literal})
                }
            } else {
                $('tr.external_publishing_point_tr').show()
                $('#internal_publishing_point_div').hide()
            }
        })
            
        $('select[name="resource[publishing_point]"]').change()
            
        $('input[name="resource[edge_group_ids][]"]').change(function(){
            fill_internal_select_options()
        }) 
            
        function fill_internal_select_options() {
            var ipps    = $('#internal_publishing_point_select')
            var fipps   = $('#failover_internal_publishing_point_select') 
            var options = '<option value></option>\n\r'
            var selected_edge_group_ids = []
            var locations    
                
            ipps.html('')
            fipps.html('')    
                
            $('input[name^="resource[edge_group_ids]"]').each(function(){
                if( $(this).is(':checked') ){
                    selected_edge_group_ids.push( $(this).val() ) 
                }
            })

            for ( var i in selected_edge_group_ids ){
                locations = edge_group_locations_ids[ selected_edge_group_ids[i]]
                if( typeof locations !== "undefined" ){
                    for ( var j in locations ){
                        options += '<option value="'+ j +'">'+ locations[j] + '</option>\n\r' 
                    }    
                }        
            }
                
            ipps.html( options )
            fipps.html( options )    
        }
            
// END Internal Publishing Point //
//////////////////////////////////    
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

<h2>{$_LANG.onappcdnnewresourcelivestreaming}</h2>

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
            <input class="textfield" id="external_publishing_url_input" type="text" value="{$session_resource.external_publishing_url}" name="resource[external_publishing_url]" />
        </td>
    </tr>
    
    <tr class="external_publishing_point_tr">
        <td>
            {$_LANG.onappcdnfailoverexternalpublishingurl}
        </td>
        <td class="label_width" valign="top">
            <input class="textfield" id="failover_external_publishing_url_input" value="{$session_resource.failover_external_publishing_url}" type="text" name="resource[failover_external_publishing_url]" />
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
                <textarea placeholder="www.yoursite.com mirror.yoursite.com" id="" cols="40" rows="5" name="resource[anti_leech_domains]" >{$session_resource.anti_leech_domains}</textarea>
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
                <input class="textfield" value="{$session_resource.secure_wowza_token}" type="text" name="resource[secure_wowza_token]" />
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
            <input id="advanced_settings_input_{$group.id}" value="{$group.id}" type="checkbox" name="resource[edge_group_ids][]" 
          {if isset($session_resource.edge_group_ids) }{if $group.id|in_array:$session_resource.edge_group_ids}checked{/if}{/if}/>
        </div>
        </td>
    </tr>

{/foreach}
</table>

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
</div>

<input type="hidden" name="add" value="1" /> <br /> <br />
<input type="hidden" name="resource[resource_type]" value="STREAM_LIVE" />
<input type="submit" value="{$_LANG.onappcdncreateresource}" />

</form>
<br /><br />
