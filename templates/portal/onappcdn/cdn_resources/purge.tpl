{literal}
<script type="text/javascript">

$(document).ready(function(){
    $('#textarea_wrapper').css('width', '260px')
    $('textarea').val('/home/somefile.jpg').attr( 'disabled', true )

    $('textarea').blur( function(){
       if ( $('textarea').val() == '' ) {
           $('textarea').val('/home/somefile.jpg').attr( 'disabled', true )
       }
    })

    $('#textarea_wrapper').click( function(){
        if ( $('textarea').attr('disabled') == true ) {
            $('textarea').val('').removeAttr('disabled').focus()
        }
    })

});

</script>

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
    | <a title="{$LANG.onappcdninstructionssettings}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=resources&action=details&id={$id}&resource_id={$resource_id}">{$LANG.onappcdninstructionssettings}</a>
    | <a title="{$LANG.onappcdnadvanceddetails}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=advanced_details&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnadvanceddetails}</a>
    | <a title="{$LANG.onappcdnprefetch}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=prefetch&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnprefetch}</a>
    | <strong>{$LANG.onappcdnpurge}</strong>
    | <a title="{$LANG.onappcdnbillingstatistics}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=billing_statistics&id={$id}&resource_id={$resource_id}">{$LANG.onappcdnbillingstatistics}</a>
</div>

{$_LANG.onappcdnpurgeinfo}

<h2>{$_LANG.onappcdnhttppurge}</h2>

{$_LANG.onappcdnpurgeinfo1}

<form action="{$smarty.const.ONAPPCDN_FILE_NAME}?page=purge&action=purge&id={$id}&resource_id={$resource_id}" method="post" >
<table cellspacing="0" cellpadding="10" border="0" width="100%">
    <tr>
        <td valign="top">
            <b>{$_LANG.onappcdnpathtopurge}</b>
        </td>
        <td>
            <div id="textarea_wrapper">
            <textarea cols="40" rows="5" name="purge[purge_paths]" >
            </textarea>
            </div>
        </td>
    </tr>
</table>
<input type="submit" value="{$_LANG.onappcdnpurge}" />

</form> <br /><br />