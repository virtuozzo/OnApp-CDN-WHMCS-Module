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
    | <a title="{$LANG.onappcdninstructionssettings}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=resources&action=details&id={$id}&resource_id={$resource_id}">{$LANG.onappcdninstructionssettings}</a>
    | <a title="{$LANG.onappcdnadvanceddetails}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=advanced_details&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnadvanceddetails}</a>
    | <a title="{$LANG.onappcdnprefetch}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=prefetch&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnprefetch}</a>
    | <a title="{$LANG.onappcdnpurge}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=purge&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnpurge}</a>
    | <strong>{$LANG.onappcdnbillingstatistics}</strong>
</div>
<h2>{$_LANG.onappcdnbillingstatistics}</h2>

<table cellspacing="0" cellpadding="10" border="0" width="100%" class="data">
    <tr>
        <th>{$_LANG.onappcdndate}</th>
        <th>{$_LANG.onappcdnedgegroup}</th>
        <th>{$_LANG.onappcdntraffic}</th>
        <th>{$_LANG.onappcdncost}</th>
    </tr>
    {foreach from=$statistics item=statistic}
    <tr>
        <td><b>{$statistic.date}</b></td>
        <td>{$statistic.label}</td>
        <td>{$statistic.traffic}</td>
        <td>{$statistic.cost}</td>
    </tr>
    {/foreach}

</table>
<form action="" method="post">
   Page : <input type="submit" value="<<" onclick = "$('input[name=page_number]').attr('value', '{$page_number-1}')" />
   <input name="page_number" size="1" value="{$page_number}" />
   <input type="submit" value=">>"  onclick = "$('input[name=page_number]').attr('value', '{$page_number+1}')"/>
   <input type="submit" value="{$_LANG.onappcdnjumptopage}" />
</form> <br /><br />

