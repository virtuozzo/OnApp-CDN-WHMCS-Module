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
      <a title="{$LANG.onappcdnresources}" href="{$smarty.const.ONAPPCDN_FILE_NAME}?page=resources&id={$id}">{$LANG.onappcdnresources}</a>
    | <strong>{$LANG.onappcdnbillingstatistics}</strong>
</div>
<h2>{$_LANG.onappcdnbillingstatistics}</h2>
<p>{$_LANG.onappcdnbillingstatisticsinfo}</p>

{if $statistics|count}
    {$pagination} <div class="items_per_page"> {$items_per_page}</div>
{/if}
<br /><br />

<table cellspacing="0" cellpadding="10" border="0" width="100%" class="data">
    <tr>
        <th id="cdntabledate">{$_LANG.onappcdndate}</th>
        <th>{$_LANG.onappcdnresource}</th>
        <th id="cdntabletraffic">{$_LANG.onappcdntraffic}</th>
        <th id="cdntablepricebc">{$_LANG.onappcdnbasecurrencyprice}</th>
        <th id="cdntablecurrencyrate">{$_LANG.onappcdncurrencyrate}</th>
        <th id="cdntableprice">{$_LANG.onappcdnprice}</th>
        <th id="cdntablecost">{$_LANG.onappcdncost}</th>
    </tr>
    {if $statistics|count}
        {foreach from=$statistics item=statistic}
        <tr>
            <td>{$statistic.created_at}</td>
            <td>{$statistic.cdn_hostname}</td>
            <td>{$statistic.traffic} MB</td>
            <td> {$statistic.price}</td>
            <td>{$statistic.currency_rate}</td>
            <td>{$whmcs_client_details.currencyprefix} {$statistic.localprice} {$whmcs_client_details.currencycode}</td> 
            <td>{$whmcs_client_details.currencyprefix} {$statistic.cost} {$whmcs_client_details.currencycode}</td>
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
<pre>
{$_LANG.onappcdntotalamount}:           <b> {$whmcs_client_details.currencyprefix} {$total} {$whmcs_client_details.currencycode}</b>
{$_LANG.onappcdnpaidinvoicesamount}:   <b> {$whmcs_client_details.currencyprefix} {$invoices_data.paid} {$whmcs_client_details.currencycode}</b>
{$_LANG.onappcdnunpaidinvoicesamount}: <b> {$whmcs_client_details.currencyprefix} {$invoices_data.unpaid} {$whmcs_client_details.currencycode}</b>
{$_LANG.onappcdnnotinvoicedamount}:    <b> {$whmcs_client_details.currencyprefix} {$not_invoiced_amount} {$whmcs_client_details.currencycode}</b>
</pre>

