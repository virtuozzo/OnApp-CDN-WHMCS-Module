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
    | <a title="{$LANG.onappcdnadvanceddetails}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=advanced_details&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnadvanceddetails}</a>
    | <a title="{$LANG.onappcdnprefetch}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=prefetch&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnprefetch}</a>
    | <strong>{$LANG.onappcdnpurge}</strong>
    | <a title="{$LANG.onappcdnbillingstatistics}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=billing_statistics&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnbillingstatistics}</a>
</div>

{$_LANG.onappcdnpurgeinfo}

<h2>{$_LANG.onappcdnhttppurge}</h2>

<h5>{$_LANG.onappcdnpurgeinfo1}</h5>

<form action="{$smarty.const.ONAPPCDN_FILE_NAME}?page=purge&action=purge&id={$id}&resource_id={$resource_id}" method="post" >
<table cellspacing="0" cellpadding="10" border="0" width="100%">
    <tr>
        <td valign="top">
            <b>{$_LANG.onappcdnpathtopurge}</b>
        </td>
        <td>
            <textarea placeholder="/home/somefile.jpg" cols="40" rows="5" name="purge[purge_paths]" >{$purge.purge_paths}</textarea>
        </td>
    </tr>
</table>
<input type="submit" value="{$_LANG.onappcdnpurge}" />

</form> <br /><br />