{if $resources_enabled eq false}

{if isset($error)}

<div class="errorbox">
    {$error}
</div>
{/if}

<div class="description">
   {$_LANG.onappcdnresourcesenabledescription}
</div>

<a href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=resources&id={$id}&action=enable">{$_LANG.onappcdnenable}</a>

{else}

  <div class="contentbox">
    <strong>{$LANG.onappcdnresources}</strong>
    | <a title="{$LANG.onappcdnbwstatistics}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=bandwidth_statistics&id={$id}">{$LANG.onappcdnbwstatistics}</a>
  </div>

<div class="description">
   {$_LANG.onappcdnresourcedescription}
</div><br />

<table cellspacing="0" cellpadding="10" border="0" width="100%" class="data">
    <tr>
        <th>{$LANG.onappcdnhostname}</th>
        <th>{$LANG.onappcdnoriginsites}</th>
        <th>{$LANG.onappcdntype}</th>
        <th>{$LANG.onappcdnlast24cost}</th>
        <th>&nbsp;</th>
    </tr>
    {if count($resources) > 0}
        {foreach item=resource from=$resources}

        <tr>
            <td>
               <a href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=resources&id={$id}&action=details&resource_id={$resource->_id}">
                   {$resource->_cdn_hostname}
               </a>
            </td>
            <td>
                {foreach item=origin from=$resource->_origins_for_api}
                    {$origin->value}
                {/foreach}
            </td>
            <td>{$resource->_resource_type}</td>
            <td>{$resource->_last_24h_cost}</td>
            <td>
                <a href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=resources&id={$id}&action=edit&resource_id={$resource->_id}">{$_LANG.onappcdnedit}</a> &nbsp
                <a href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=resources&id={$id}&action=delete&resource_id={$resource->_id}">{$_LANG.onappcdndelete}</a>
            </td>
        </tr>
        {/foreach}
        
        {else}
        <tr>
            <td>
                {$LANG.onappcdnresourcesnotfound}
            </td>
        </tr>
    {/if}
</table>

<form action='{$smarty.const.ONAPPCDN_FILE_NAME}?page=resources&id={$id}&action=add}' method="get">
    <input type="hidden" name="page" value="resources" />
    <input type="hidden" name="action" value="add" />
    <input type="hidden" name="id" value="{$id}" />
    <input type="submit" value="{$_LANG.onappcdnnewresource}" />
</form>

{/if}
