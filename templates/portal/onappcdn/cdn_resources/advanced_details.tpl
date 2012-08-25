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
      <a title="{$LANG.onappcdnresourceslist}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=resources&id={$id}">{$LANG.onappcdnresourceslist}</a>
    | <a title="{$LANG.onappcdninstructionssettings}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=details&id={$id}&resource_id={$resource_id}">{$LANG.onappcdninstructionssettings}</a>
    | <strong>{$LANG.onappcdnadvanceddetails}</strong>
    | <a title="{$LANG.onappcdnprefetch}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=prefetch&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnprefetch}</a>
    | <a title="{$LANG.onappcdnpurge}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=purge&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnpurge}</a>
    | <a title="{$LANG.onappcdnbillingstatistics}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=billing_statistics&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnbillingstatistics}</a>
</div>
<h2>{$_LANG.onappcdnresourceadvancedstatus}</h2>

<div class="description">
   {$_LANG.onappcdnresourceadvancedstatusinfo}
</div>

<h4>{$_LANG.onappcdnresourceadvanceddetails}</h4> <hr />

<table cellspacing="0" cellpadding="10" border="0" width="100%">
  {*  <tr>
        <td valign="top" width="150px">{$LANG.onappcdnpublishername}</td>
        <td>{$details->_publisher_name}</td>
    </tr> *}
    <tr>
        <td valign="top">{$LANG.onappcdnipaccesspolicy}</td>
        <td>{$details->_ip_access_policy}</td>
    </tr>
    <tr>
        <td valign="top">{$LANG.onappcdnipaccess}</td>
        <td>{$details->_ip_addresses}</td>
    </tr>
    <tr>
        <td valign="top">{$LANG.onappcdncountryaccesspolicy}</td>
        <td>{$details->_country_access_policy}</td>
    </tr>
    <tr>
        <td valign="top">{$LANG.onappcdncountryaccess}</td>
        <td>
            {foreach  item=country from=$selected_countries}
                {$country} <br />
            {/foreach}
        </td>
    </tr>
    <tr>
        <td valign="top">{$LANG.onappcdnurlsigningenabled}</td>
        <td>
           {if $details->_url_signing_on eq true}
               {$LANG.onappcdnyes}
           {else} 
               {$LANG.onappcdnno}
           {/if}
        </td>
    </tr> 
    <tr>
        <td valign="top">{$LANG.onappcdnurlsigningkey}</td>
        <td>{$details->_url_signing_key}</td>
    </tr>
    <tr>
        <td valign="top">{$LANG.onappcdnhotlinkpolicy}</td>
        <td>{$details->_hotlink_policy}</td>
    </tr>
    <tr>
        <td valign="top">{$LANG.onappcdnpasswordon}</td>
        <td>
           {if $details->_password_on eq true}
               {$LANG.onappcdnyes}
           {else}
               {$LANG.onappcdnno}
           {/if}
        </td>
    </tr>
    <tr>
        <td valign="top">{$LANG.onappcdnpasswords}</td>
        <td>
            {foreach key=login item=pass from=$details->_passwords}
                {$login} : {$pass} <br />
            {/foreach}
        </td>
    </tr>
    <tr>
        <td valign="top">{$LANG.onappcdnunauthorizedhtml}</td>
        <td>{$details->_password_unauthorized_html|htmlspecialchars}</td>
    </tr>
    <tr>
        <td valign="top">{$LANG.onappcdncacheexpiry}</td>
        <td>{$details->_cache_expiry}</td>
    </tr>
</table>
<br />

<form action="" method="get">
    <input type="hidden" name="action" value="edit" />
    <input type="hidden" name="page" value="resources" />
    <input type="hidden" name="resource_id" value="{$resource_id}" />
    <input type="hidden" name="id" value="{$id}" />
    <input type="submit" value="{$_LANG.onappcdnedit}"/>
</form>

<br /><br />
