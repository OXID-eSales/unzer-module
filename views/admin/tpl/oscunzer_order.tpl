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
                    <th>[{oxmultilang ident="OSCUNZER_ORDER_AMOUNT"}]</th>
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
        [{if $errShip}]
            <div style="color: red">[{$errShip}]</div>
        [{/if}]
    [{/if}]
[{/block}]
[{block name="unzer_collect"}]
    [{if $AuthId}]
        <br><br>
        <b>[{oxmultilang ident="OSCUNZER_AUTHORIZATION"}]</b><br>
        <b>[{oxmultilang ident="OSCUNZER_REMAING_AMOUNT"}]</b>: [{$AuthAmountRemaining}] [{$AuthCur}]<br>
        <b>[{oxmultilang ident="OSCUNZER_ORDER_AMOUNT"}]</b>: [{$AuthAmount}]  [{$AuthCur}]<br>

        [{if $AuthAmountRemaining>0}]
            <form name="uzr" id="uzr_collect" action="[{$oViewConf->getSelfLink()}]" method="post">
                <input type="hidden" name="cl" value="unzer_admin_order">
                <input type="hidden" name="fnc" value="doUnzerCollect">
                <input type="hidden" name="oxid" value="[{$oxid}]">
                <input type="hidden" name="unzerid" value="[{$sPaymentId}]">
                <table>
                    <tr>
                        <td><input type="text" name="amount" value="[{$AuthAmountRemaining}]"> [{$AuthCur}]</td>
                        <td><button type="submit">[{oxmultilang ident="OSCUNZER_CHARGE_COLLECT"}]</button></td>
                    </tr>
                </table>
            </form>
        [{/if}]
    [{/if}]
    [{if $errAuth}]
        <div style="color: red">[{$errAuth}]</div>
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
                    <td>[{$oUnzerCharge.chargedAmount|escape}] [{$uzrCurrency}]</td>
                    <td>[{$oUnzerCharge.cancelledAmount|escape}] [{$uzrCurrency}]</td>

                    <td>
                        <select name="reason" id="reason_[{$oUnzerCharge.chargeId}]" [{if !$oUnzerCharge.cancellationPossible}]disabled[{/if}]>
                            [{if !$blCancelReasonReq}]<option value="">NONE</option>[{/if}]
                            <option value="CANCEL">[{oxmultilang ident="OSCUNZER_REASON_CANCEL"}]</option>
                            <option value="RETURN">[{oxmultilang ident="OSCUNZER_REASON_RETURN"}]</option>
                            <option value="CREDIT">[{oxmultilang ident="OSCUNZER_REASON_CREDIT"}]</option>
                        </select>
                    </td>
                    <td><input type="text" name="amount" id="amount_[{$oUnzerCharge.chargeId}]" value="[{$oUnzerCharge.chargedAmount}]" [{if !$oUnzerCharge.cancellationPossible}]disabled[{/if}]> [{$uzrCurrency}]</td>
                    <td><input type="submit" id="submit_[{$oUnzerCharge.chargeId}]" [{if !$oUnzerCharge.cancellationPossible}]disabled[{/if}]
                               value="[{oxmultilang ident="OSCUNZER_PAYOUT"}]">
                    </td>
                    [{capture assign="cancelConfirm"}]
                    const inAmount = document.getElementById('amount_[{$oUnzerCharge.chargeId}]');
                    const form = document.getElementById('uzr_[{$oUnzerCharge.chargeId}]');
                    form.addEventListener('submit', function (e) {
                    if (window.confirm('[{oxmultilang ident="OSCUNZER_CANCEL_ALERT"}]' + ' ' + inAmount.value)) {
                        return true;
                    } else {
                        return false;
                    }
                    });
                    [{/capture}]
                    [{oxscript add=$cancelConfirm}]
                </form>
            </tr>
            [{/foreach}]
            [{if $errCancel}]
                <tr>
                    <td colspan="7"><div style="color: red">[{$errCancel}]</div></td>
                </tr>
            [{/if}]
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
