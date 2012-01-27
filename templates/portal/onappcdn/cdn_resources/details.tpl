{if isset($error)}

<div class="errorbox">
    {$error}
</div>
{/if}

<div class="contentbox">
    <strong>{$LANG.onappcdninstructionssettings}</strong>
    | <a title="{$LANG.onappcdnadvanceddetails}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=advanced_details&id={$id}">{$LANG.onappcdnadvanceddetails}</a>
    | <a title="{$LANG.onappcdnprefetch}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=prefetch&id={$id}">{$LANG.onappcdnprefetch}</a>
    | <a title="{$LANG.onappcdnpurge}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=purge&id={$id}">{$LANG.onappcdnpurge}</a>
    | <a title="{$LANG.onappcdnbillingstatistics}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=billing_statistics&id={$id}">{$LANG.onappcdnbillingstatistics}</a>
</div>

<div class="description">
   {$_LANG.onappcdnresourcedetailsdescription}
</div><br />


