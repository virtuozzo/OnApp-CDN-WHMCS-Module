<h2>{$_LANG.onappcdnselectcdnresourcetypetitle}</h2>

<div class="description">
   {$_LANG.onappcdnselectcdnresourcetypeinfo}
</div><br />

<form method="post" id="form" action=""> </form>
<table cellspacing="0" cellpadding="10" border="0" width="100%" class="data">
    <tr>
        <th>{$LANG.onappcdnresourcetype}</th>
        <th>{$LANG.onappcdndescription}</th>
    </tr>
        {foreach item=type key=typekey from=$resource_types}
        {if $typekey != 'HTTP_PUSH'}    
        <tr>
            <td>
                <input value="{$type.label}" type="button" 
                       onclick="$('#form').attr('action', '{$smarty.const.ONAPPCDN_FILE_NAME}?page=resources&type={$typekey}&action=add&id={$id}').submit()" />
            </td>
            <td>
                {$type.description}
            </td>
        </tr>
        {/if}
        {/foreach}
        
</table>
