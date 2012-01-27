{if $resources eq null}

{if isset($error)}

<div class="errorbox">
    {$error}
</div>
{/if}

<div class="description">
   {$_LANG.onappcdnresourcesenabledescription}
</div>

<a href="{$smarty.const.ONAPP_FILE_NAME}?page=resources&id={$id}&action=enable">{$_LANG.onappcdnenable}</a>

{else}

   <div class="contentbox">
    <strong>{$LANG.onappcdnresources}</strong>
    | <a title="{$LANG.onappcdnbwstatistics}" href="{$smarty.const.ONAPP_FILE_NAME}?page=bandwidth_statistics&id={$id}">{$LANG.onappcdnbwstatistics}</a>
  </div>

  <pre> {$resources|print_r}
{/if}
