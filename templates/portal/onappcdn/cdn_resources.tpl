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

{if $resources_enabled eq false}

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
        {foreach item=resource key=resource_id from=$resources}
        <tr>
            <td>
               <a href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=details&id={$id}&resource_id={$resource_id}">
                   {$resource._cdn_hostname}
               </a>
            </td>
            <td>{$resource._origins_for_api}</td>
            <td>{$resource._resource_type}</td>
            <td>{$resource._last_24h_cost}</td>
            <td>
                <a href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=resources&id={$id}&action=edit&resource_id={$resource_id}">{$_LANG.onappcdnedit}</a> &nbsp
                <a onclick="if( confirm('{$_LANG.onappcdnareyousureyouwantdelete}') ) return true; else return false" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=resources&id={$id}&action=delete&resource_id={$resource_id}">{$_LANG.onappcdndelete}</a>
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
