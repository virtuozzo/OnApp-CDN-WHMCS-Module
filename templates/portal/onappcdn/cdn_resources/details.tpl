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
      <a title="{$LANG.onappcdnresourceslist}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=resources&id={$id}">{$LANG.onappcdnresourceslist}</a>
    | <strong>{$LANG.onappcdninstructionssettings}</strong>
    | <a title="{$LANG.onappcdnadvanceddetails}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=advanced_details&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnadvanceddetails}</a>
    | <a title="{$LANG.onappcdnprefetch}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=prefetch&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnprefetch}</a>
    | <a title="{$LANG.onappcdnpurge}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=purge&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnpurge}</a>
    | <a title="{$LANG.onappcdnbwstatistics}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=bandwidth_statistics&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnbwstatistics}</a>
</div>

<div class="description">
   {$_LANG.onappcdnresourcedetailsdescription}
</div><br />

<h2>{$_LANG.onappcdnresourcedetails}</h2>

{$_LANG.onappcdnhostname}     - {$resource->_cdn_hostname} <br />
{$_LANG.onappcdnresourcetype} - {$resource->_resource_type} <br />
{$_LANG.onappcdnreference}    - {$resource->_aflexi_resource_id} <br />
{$_LANG.onappcdnstatus}       - {$resource->_status} <br />

<h2>{$_LANG.onappcdnorigins}</h2>

<table cellspacing="0" cellpadding="10" border="0" width="100%" class="data">
    <tr>
        <th>{$LANG.onappcdnresourcepath}</th>
        <th>{$LANG.onappcdnorigins}</th>
    </tr>

{foreach item=origin from=$resource->_origins_for_api}
    <tr>
        <td>/{$origin->_key}</td>
        <td>{$origin->_value}</td>
    </tr>
{/foreach}
</table>

<h2>{$_LANG.onappcdndnssettings}</h2>

<h5>{$_LANG.onappcdndnssettingsinfo} </h5>
<b>
   {$resource->_cdn_hostname} IN CNAME {$resource->_aflexi_resource_id}.r.worldcdn.net
</b>


<h2>{$_LANG.onappcdnedgegroups}</h2>

<table cellspacing="0" cellpadding="10" border="0" width="100%">
    
    {foreach item=group from=$edge_group_baseresources}
            <tr>
                <td valign="top"><b>{$group.label}</b></td>
                <td valign="top">   
                    {$whmcs_client_details.currencyprefix}{$group.price|round:2} {$whmcs_client_details.currencycode} {$_LANG.onappcdnperGB} <br />
                    {foreach item=location from=$group.locations}
                        {$location->_city}, {$location->_country}    <br />
                    {/foreach}
                </td>
            </tr>
    {/foreach}

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

