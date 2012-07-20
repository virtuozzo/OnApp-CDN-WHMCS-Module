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
    | <strong>{$LANG.onappcdnprefetch}</strong>
    | <a title="{$LANG.onappcdnpurge}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=purge&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnpurge}</a>
    | <a title="{$LANG.onappcdnbillingstatistics}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=billing_statistics&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnbillingstatistics}</a>
</div>

{$_LANG.onappcdnprefetchinfo}

<h2>{$_LANG.onappcdnhttpprefetch}</h2>

<h5>{$_LANG.onappcdnprefetchinfo1}</h5>

<form action="{$smarty.const.ONAPPCDN_FILE_NAME}?page=prefetch&action=prefetch&id={$id}&resource_id={$resource_id}" method="post" >
<table cellspacing="0" cellpadding="10" border="0" width="100%">
    <tr>
        <td valign="top">
            <b>{$_LANG.onappcdnpathtoprefetch}</b>
        </td>
        <td>
            <textarea cols="40" rows="5" placeholder="/home/somefile.jpg" name="prefetch[prefetch_paths]" >{$prefetch.prefetch_paths}</textarea>
        </td>
    </tr>
</table>
<input type="submit" value="{$_LANG.onappcdnprefetch}" />

</form>