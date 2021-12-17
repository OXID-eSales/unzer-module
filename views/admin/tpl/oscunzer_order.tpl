[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
    [{else}]
    [{assign var="readonly" value=""}]
    [{/if}]

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="order_main">
</form>

[{if !$oUnzerTransactions}]
    [{oxmultilang ident="OSCUNZER_NO_UNZER_ORDER"}]
[{else}]
    <table>
        <tr>
            <th>[{oxmultilang ident="OSCUNZER_TRANSACTION_CREATED" suffix="COLON"}]</th>
            <th>[{oxmultilang ident="OSCUNZER_TRANSACTION_CUSTOMERID"}]</th>
            <th>[{oxmultilang ident="OSCUNZER_TRANSACTION_STATUS"}]</th>
            <th>[{oxmultilang ident="OSCUNZER_TRANSACTION_TYPEID"}]</th>
        </tr>
        [{foreach from=$oUnzerTransactions item="oUnzerTransaction"}]
            <tr>
                <td>[{$oUnzerTransaction->getUnzerCreated()|escape}]</td>
                <td>[{$oUnzerTransaction->getUnzerCustomerId()|escape}]</td>
                <td>[{$oUnzerTransaction->getUnzerAction()|escape}]</td>
                <td>[{$oUnzerTransaction->getUnzerTypeId()|escape}]</td>
            </tr>
        [{/foreach}]
    </table>

    <div>&nbsp;</div>

    [{assign var="bHasAdditionalData" value=false}]
    [{capture assign="additionalData"}]
    [{assign var="aPaymentData" value=$oUnzerTransaction->getUnzerMetaData()}]
    [{if is_array($aPaymentData)}]
    <div><b>[{oxmultilang ident="OSCUNZER_TRANSACTION_PAYMENTMETA"}]</b></div>
    <table>
        [{foreach from=$aPaymentData key="paramName" item="paramValue"}]
        [{assign var="bHasAdditionalData" value=true}]
        <tr>
            <td>[{$paramName|escape}]:</td>
            <td>&nbsp;</td>
            <td>[{$paramValue|escape}]</td>
        </tr>
        [{/foreach}]
    </table>
    [{/if}]
    [{/capture}]

    [{if $bHasAdditionalData}][{$additionalData}][{/if}]
[{/if}]

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
