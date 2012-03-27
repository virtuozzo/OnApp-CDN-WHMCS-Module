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
    | <a title="{$LANG.onappcdnpurge}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=purge&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnpurge}</a>
    | <strong>{$LANG.onappcdnbwstatistics}</strong>
</div>
<h2>{$_LANG.onappcdnbandwidthstatistics}</h2>
<p>{$_LANG.onappcdnbandwidthstatisticsinfo}</p>

{if $statistics|count}
    {$pagination} <div class="items_per_page"> {$items_per_page}</div>
{/if}
<br /><br />

<table cellspacing="0" cellpadding="10" border="0" width="100%" class="data">
    <tr>
        <th>{$_LANG.onappcdnhostname}</th>
        <th>{$_LANG.onappcdndate}</th>
        <th>{$_LANG.onappcdncached}</th>
        <th>{$_LANG.onappcdnnoncached}</th>
    </tr>
    {if $statistics|count}
        {foreach from=$statistics item=statistic}
        <tr>
            <td><b>{$statistic.cdn_hostname}</b></td>
            <td>{$statistic.created_at}</td>
            <td>{$statistic.cached}</td>
            <td>{$statistic.non_cached}</td>
        </tr>
        {/foreach}

        {else}
            <tr>
                <td>{$_LANG.onappcdnnostatistics}</td>
            </tr>
    {/if}


</table>
{if $statistics|count}
    {$pagination}
{/if}
<br /><br />


