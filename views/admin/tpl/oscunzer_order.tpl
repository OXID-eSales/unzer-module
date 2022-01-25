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
            <th>[{oxmultilang ident="OSCUNZER_TRANSACTION_SHORTID"}]</th>
            <th>[{oxmultilang ident="OSCUNZER_TRANSACTION_CUSTOMERID"}]</th>
            <th>[{oxmultilang ident="OSCUNZER_TRANSACTION_STATUS"}]</th>
            <th>[{oxmultilang ident="OSCUNZER_TRANSACTION_TYPEID"}]</th>
        </tr>

        [{foreach from=$oUnzerTransactions item="oUnzerTransaction"}]
        <tr>
            <td>[{$oUnzerTransaction->getUnzerCreated()|escape}]</td>
            <td>[{$oUnzerTransaction->getUnzerShortId()|escape}]</td>
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
[{block name="unzer_ship"}]
    [{if $blShipment}]
        <br><br>
        <b>Shipments</b><br>
        [{if $aShipments}]
            <table>
                <tr>
                    <th>[{oxmultilang ident="GENERAL_DATE"}]</th>
                    <th>[{oxmultilang ident="OSCUNZER_SHIP_ID"}]</th>
                    <th>[{oxmultilang ident="AMOUNT"}]</th>
                    <th>[{oxmultilang ident="ORDER_MAIN_BILLNUM"}]</th>
                </tr>
                [{foreach from=$aShipments item="oUnzerShipment"}]
                <tr>
                    <td>[{$oUnzerShipment.shipingDate|escape}]</td>
                    <td>[{$oUnzerShipment.shipId|escape}]</td>
                    <td>[{$oUnzerShipment.amount|escape}]</td>
                    <td>[{$oUnzerShipment.invoiceid|escape}]</td>
                </tr>
                [{/foreach}]
            </table>
        [{else}]
            <br>[{oxmultilang ident="OSCUNZER_NOSHIPINGYET"}]
            <form name="uzr" id="uzr_collect" action="[{$oViewConf->getSelfLink()}]" method="post">
                <input type="hidden" name="cl" value="unzer_admin_order">
                <input type="hidden" name="fnc" value="sendShipmentNotification">
                <input type="hidden" name="oxid" value="[{$oxid}]">
                <input type="hidden" name="unzerid" value="[{$sPaymentId}]">
                <button type="submit">[{oxmultilang ident="OSCUNZER_SHIPMENT_NOTIFICATION"}]</button>
            </form>
        [{/if}]
    [{/if}]
[{/block}]
[{block name="unzer_collect"}]
    [{if $AuthId}]
        <br><br>
        <b>[{oxmultilang ident="OSCUNZER_AUTHORIZATION"}]</b><br>
        <b>[{oxmultilang ident="OSCUNZER_REMAING_AMOUNT"}]</b>: [{$AuthAmountRemaining}]<br>
        <b>[{oxmultilang ident="OSCUNZER_ORDER_AMOUNT"}]</b>: [{$AuthAmount}]<br>

        [{if $AuthAmountRemaining>0}]
            <form name="uzr" id="uzr_collect" action="[{$oViewConf->getSelfLink()}]" method="post">
                <input type="hidden" name="cl" value="unzer_admin_order">
                <input type="hidden" name="fnc" value="doUnzerCollect">
                <input type="hidden" name="oxid" value="[{$oxid}]">
                <input type="hidden" name="unzerid" value="[{$sPaymentId}]">
                <table>
                    <tr>
                        <td><input type="text" name="amount" value="[{$AuthAmountRemaining}]"></td>
                        <td><button type="submit">[{oxmultilang ident="OSCUNZER_CHARGE_COLLECT"}]</button></td>
                    </tr>
                </table>
            </form>
        [{/if}]
    [{/if}]
[{/block}]

[{block name="unzer_refund"}]
    <br><br>
    [{if $aCharges}]
        <b>Charges</b>
        <table>
            <tr>
                <th>[{oxmultilang ident="GENERAL_DATE"}]</th>
                <th>[{oxmultilang ident="OSCUNZER_CHARGE_ID"}]</th>
                <th>[{oxmultilang ident="OSCUNZER_CHARGED_AMOUNT"}]</th>
                <th>[{oxmultilang ident="OSCUNZER_CHARGED_CANCELLED"}]</th>
                <th>[{oxmultilang ident="OSCUNZER_CHARGE_CANCELREASON"}]</th>
                <th>[{oxmultilang ident="OSCUNZER_CHARGE_CANCELAMOUNT"}]</th>
                <th></th>
            </tr>

            [{foreach from=$aCharges item="oUnzerCharge"}]
            <tr>
                <form name="uzr" id="uzr_[{$oUnzerCharge.chargeId}]" action="[{$oViewConf->getSelfLink()}]" method="post">
                    [{$oViewConf->getHiddenSid()}]
                    <input type="hidden" name="chargeid" value="[{$oUnzerCharge.chargeId}]">
                    <input type="hidden" name="chargedamount" value="[{$oUnzerCharge.chargedAmount}]">
                    <input type="hidden" name="unzerid" value="[{$sPaymentId}]">
                    <input type="hidden" name="cl" value="unzer_admin_order">
                    <input type="hidden" name="fnc" value="doUnzerCancel">
                    <input type="hidden" name="oxid" value="[{$oxid}]">

                    <td>[{$oUnzerCharge.chargeDate|escape}]</td>
                    <td>[{$oUnzerCharge.chargeId|escape}]</td>
                    <td>[{$oUnzerCharge.chargedAmount|escape}]</td>
                    <td>[{$oUnzerCharge.cancelledAmount|escape}]</td>

                    <td><input type="text" name="reason" [{if !$oUnzerCharge.cancellationPossible}]disabled[{/if}]></td>
                    <td><input type="text" name="amount" value="[{$oUnzerCharge.chargedAmount}]" [{if !$oUnzerCharge.cancellationPossible}]disabled[{/if}]></td>
                    <td><button type="submit" [{if !$oUnzerCharge.cancellationPossible}]disabled[{/if}]>Payout</button></td>
                </form>
            </tr>
            [{/foreach}]
        </table>
    [{/if}]
    [{/block}]

[{block name="unzer_cancellation"}]
<br><br>
    [{if $aCancellations}]
        <b>Cancellations</b>
        <table>
            <tr>
                <th>[{oxmultilang ident="GENERAL_DATE"}]</th>
                <th>[{oxmultilang ident="OSCUNZER_CANCEL_ID"}]</th>
                <th>[{oxmultilang ident="OSCUNZER_CHARGE_CANCELAMOUNT"}]</th>
                <th>[{oxmultilang ident="OSCUNZER_CHARGE_CANCELREASON"}]</th>
            </tr>
            [{foreach from=$aCancellations item="oUnzerCancel"}]
                <tr>
                    <td>[{$oUnzerCancel.cancelDate|escape}]</td>
                    <td>[{$oUnzerCancel.cancellationId|escape}]</td>
                    <td>[{$oUnzerCancel.cancelledAmount|escape}]</td>
                    <td>[{$oUnzerCancel.cancelReason|escape}]</td>
                </tr>
            [{/foreach}]
        </table>
    [{/if}]
[{/block}]

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
