[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
[{else}]
    [{assign var="readonly" value=""}]
[{/if}]
[{if $paymentTitle && $totalBasketPrice}]
<h3>[{$paymentTitle}] : [{$totalBasketPrice}]</h3>
[{/if}]
<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="order_main">
</form>

<div id="liste" style="margin:0;">
    [{if !$oUnzerTransactions}]
        [{oxmultilang ident="OSCUNZER_NO_UNZER_ORDER"}]
    [{else}]
        <table>
            <tbody>
                <tr>
                    <td class="listheader">[{oxmultilang ident="OSCUNZER_TRANSACTION_CREATED"}]</td>
                    <td class="listheader">[{oxmultilang ident="OSCUNZER_TRANSACTION_SHORTID"}]</td>
                    <td class="listheader">[{oxmultilang ident="OSCUNZER_TRANSACTION_TRACEID"}]</td>
                    <td class="listheader">[{oxmultilang ident="OSCUNZER_TRANSACTION_CUSTOMERID"}]</td>
                    <td class="listheader">[{oxmultilang ident="OSCUNZER_TRANSACTION_STATUS"}]</td>
                    <td class="listheader">[{oxmultilang ident="OSCUNZER_TRANSACTION_TYPEID"}]</td>
                    <td class="listheader">[{oxmultilang ident="OSCUNZER_TRANSACTION_AMOUNT"}]</td>
                </tr>
                [{foreach from=$oUnzerTransactions item="oUnzerTransaction"}]
                    [{assign var="transaction_state" value=$oUnzerTransaction->getUnzerState()|escape}]
                    <tr>
                        <td>[{$oUnzerTransaction->getUnzerCreated()|escape}]</td>
                        <td>[{$oUnzerTransaction->getUnzerShortId()|escape}]</td>
                        <td>[{$oUnzerTransaction->getUnzerTraceId()|escape}]</td>
                        <td>[{$oUnzerTransaction->getUnzerCustomerId()|escape}]</td>
                        <td>[{'OSCUNZER_TRANSACTION_STATUS_'|cat:$transaction_state|oxmultilangassign}]</td>
                        <td>[{$oUnzerTransaction->getUnzerTypeId()|escape}]</td>
                        <td>[{$oUnzerTransaction->getUnzerAmount()|string_format:"%.2f"}]
                            [{$oUnzerTransaction->getUnzerCurrency()}]
                        </td>
                    </tr>
                [{/foreach}]
            </tbody>
        </table>
    [{/if}]

    [{block name="unzer_ship"}]
        [{if $blShipment}]
            <br><br>
            <h3>[{oxmultilang ident="OSCUNZER_SHIPMENTS"}]</h3>
            [{if $aShipments}]
                <table>
                    <tbody>
                        <tr>
                            <td class="listheader">[{oxmultilang ident="GENERAL_DATE"}]</td>
                            <td class="listheader">[{oxmultilang ident="OSCUNZER_SHIP_ID"}]</td>
                            <td class="listheader">[{oxmultilang ident="OSCUNZER_ORDER_AMOUNT"}]</td>
                            <td class="listheader">[{oxmultilang ident="ORDER_MAIN_BILLNUM"}]</td>
                        </tr>
                        [{foreach from=$aShipments item="oUnzerShipment"}]
                            [{if !$oUnzerShipment.success}]
                                [{assign var=unzStyle value='class ="listitem3"'}]
                            [{else}]
                                [{assign var=unzStyle value=''}]
                            [{/if}]
                            <tr>
                                <td [{$unzStyle}]>[{$oUnzerShipment.shipingDate|escape}]</td>
                                <td [{$unzStyle}]>[{$oUnzerShipment.shipId|escape}]</td>
                                <td [{$unzStyle}]>[{$oUnzerShipment.amount|escape|string_format:"%.2f"}]</td>
                                <td [{$unzStyle}]>[{$oUnzerShipment.invoiceid|escape}]</td>
                            </tr>
                            [{/foreach}]
                    </tbody>
                </table>
            [{/if}]
            [{if !$blSuccessShipped}]
                [{oxmultilang ident="OSCUNZER_NOSHIPINGYET"}]<br>
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
            [{if $AuthAmountRemaining > 0}]
                <h3>[{oxmultilang ident="OSCUNZER_AUTHORIZATION"}]</h3>
                <form name="uzr" id="uzr_collect" action="[{$oViewConf->getSelfLink()}]" method="post">
                    <input type="hidden" name="cl" value="unzer_admin_order">
                    <input type="hidden" name="fnc" value="doUnzerCollect">
                    <input type="hidden" name="oxid" value="[{$oxid}]">
                    <input type="hidden" name="unzerid" value="[{$sPaymentId}]">
                    <table>
                        <tbody>
                            <tr>
                                <td>
                                    <b>[{oxmultilang ident="OSCUNZER_REMAING_AMOUNT" suffix="COLON"}]</b>[{$AuthAmountRemaining|string_format:"%.2f"}] [{$AuthCur}]<br>
                                    <b>[{oxmultilang ident="OSCUNZER_ORDER_AMOUNT" suffix="COLON"}]</b>[{$AuthAmount|string_format:"%.2f"}] [{$AuthCur}]<br>
                                </td>
                                <td><input type="text" name="amount" value="[{$AuthAmountRemaining|string_format:"%.2f"}]"> [{$AuthCur}]</td>
                                <td><button type="submit">[{oxmultilang ident="OSCUNZER_CHARGE_COLLECT"}]</button></td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            [{/if}]
            [{if $AuthAmountRemaining == $AuthAmount}]
                <form name="uzr" id="uzr_collect" action="[{$oViewConf->getSelfLink()}]" method="post">
                    <input type="hidden" name="cl" value="unzer_admin_order">
                    <input type="hidden" name="fnc" value="doUnzerAuthorizationCancel">
                    <input type="hidden" name="oxid" value="[{$oxid}]">
                    <input type="hidden" name="unzerid" value="[{$sPaymentId}]">
                    <table>
                        <tbody>
                        <tr>
                            <td>[{oxmultilang ident="OSCUNZER_AUTHORIZE_CANCEL_POSSIBLE"}]</td>
                            [{if $isCreditCard}]
                            <td><input type="text" name="amount" value="[{$AuthAmountRemaining|string_format:"%.2f"}]"> [{$AuthCur}]</td>
                            [{else}]
                            <td><input type="hidden" name="amount" value="[{$AuthAmountRemaining|string_format:"%.2f"}]"></td>
                            [{/if}]
                            <td><button type="submit">[{oxmultilang ident="OSCUNZER_AUTHORIZE_CANCEL"}]</button></td>
                        </tr>
                        </tbody>
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
            <h3>[{oxmultilang ident="OSCUNZER_CHARGES"}]</h3>
            <table>
                <tbody>
                    <tr>
                        <td class="listheader">[{oxmultilang ident="GENERAL_DATE"}]</td>
                        <td class="listheader">[{oxmultilang ident="OSCUNZER_CHARGE_ID"}]</td>
                        <td class="listheader">[{oxmultilang ident="OSCUNZER_CHARGED_AMOUNT"}]</td>
                        <td class="listheader">[{oxmultilang ident="OSCUNZER_CHARGED_CANCELLED"}]</td>
                        [{if $blCancelReasonReq}]
                            <td class="listheader">[{oxmultilang ident="OSCUNZER_CHARGE_CANCELREASON"}]</td>
                        [{/if}]
                        <td class="listheader">[{oxmultilang ident="OSCUNZER_CHARGE_CANCELAMOUNT"}]</td>
                        <td class="listheader"> </td>
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
                            <td>[{$oUnzerCharge.chargedAmount|escape|string_format:"%.2f"}] [{$uzrCurrency}]</td>
                            [{if $oUnzerCharge.cancellationPossible}]
                                <td>
                                    [{$oUnzerCharge.cancelledAmount|escape|string_format:"%.2f"}] [{$uzrCurrency}]
                                </td>
                                [{if $blCancelReasonReq}]
                                    <td>
                                        <select name="reason" id="reason_[{$oUnzerCharge.chargeId}]">
                                            <option value="CANCEL">[{oxmultilang ident="OSCUNZER_REASON_CANCEL"}]</option>
                                            <option value="RETURN">[{oxmultilang ident="OSCUNZER_REASON_RETURN"}]</option>
                                            <option value="CREDIT">[{oxmultilang ident="OSCUNZER_REASON_CREDIT"}]</option>
                                        </select>
                                    </td>
                                [{/if}]
                                <td>
                                    <input type="text"
                                           name="amount"
                                           id="amount_[{$oUnzerCharge.chargeId}]"
                                           value="[{math equation="x - y" x=$oUnzerCharge.chargedAmount y=$oUnzerCharge.cancelledAmount format="%.2f"}]"
                                           [{if !$oUnzerCharge.cancellationPossible}]disabled[{/if}]>
                                    [{$uzrCurrency}]
                                </td>
                                <td>
                                    <input type="submit"
                                           id="submit_[{$oUnzerCharge.chargeId}]"
                                           [{if !$oUnzerCharge.cancellationPossible}]disabled[{/if}]
                                           value="[{oxmultilang ident="OSCUNZER_PAYOUT"}]">
                                </td>
                            [{else}]
                                <td>[{$oUnzerCharge.cancelledAmount|string_format:"%.2f"}] [{$uzrCurrency}]</td>
                                <td>[{$oUnzerCharge.chargedAmount|string_format:"%.2f"}] [{$uzrCurrency}]</td>
                                <td></td>
                            [{/if}]
                        </form>
                    </tr>
                    [{/foreach}]
                    [{if $canCancelAmount > 0}]
                        <form name="uzr" id="uzr_payout" action="[{$oViewConf->getSelfLink()}]" method="post">
                        [{$oViewConf->getHiddenSid()}]
                        <input type="hidden" name="unzerid" value="[{$sPaymentId}]">
                        <input type="hidden" name="chargedamount" value="[{$canCancelAmount}]">
                        <input type="hidden" name="cl" value="unzer_admin_order">
                        <input type="hidden" name="fnc" value="doUnzerCancel">
                        <input type="hidden" name="oxid" value="[{$oxid}]">
                        <tr>
                            [{if $blCancelReasonReq}]
                            <td colspan="3" align="right">
                            [{else}]
                            <td colspan="2" align="right">
                            [{/if}]
                            [{oxmultilang ident="OSCUNZER_CHARGE_CANCEL_FROM_PAYMENT"}]</td>
                            <td>[{$totalAmountCharge|string_format:"%.2f"}] [{$uzrCurrency}]</td>
                            <td>[{$totalAmountCancel|string_format:"%.2f"}] [{$uzrCurrency}]</td>
                            <td><input type="text" id="amount_payout" name="amount" value="[{$canCancelAmount|string_format:"%.2f"}]"> [{$uzrCurrency}]</td>
                            <td><button type="submit">[{oxmultilang ident="OSCUNZER_PAYOUT"}]</button></td>
                        </tr>
                        </form>
                    [{/if}]
                    [{if $errCancel}]
                        <tr>
                            <td colspan="[{if $blCancelReasonReq}]7[{else}]6[{/if}]"><div style="color: red">[{$errCancel}]</div></td>
                        </tr>
                    [{/if}]
                </tbody>
            </table>
        [{/if}]
        [{/block}]

    [{block name="unzer_cancellation"}]
    <br><br>
        [{if $aCancellations}]
            <h3>[{oxmultilang ident="OSCUNZER_CANCELLATIONS"}]</h3>
            <table>
                <tr>
                    <td class="listheader">[{oxmultilang ident="GENERAL_DATE"}]</td>
                    <td class="listheader">[{oxmultilang ident="OSCUNZER_CANCEL_ID"}]</td>
                    <td class="listheader">[{oxmultilang ident="OSCUNZER_CHARGE_CANCELAMOUNT"}]</td>
                    [{if $blCancelReasonReq}]
                        <td class="listheader">[{oxmultilang ident="OSCUNZER_CHARGE_CANCELREASON"}]</td>
                    [{/if}]
                </tr>
                [{foreach from=$aCancellations item="oUnzerCancel"}]
                    <tr>
                        <td>[{$oUnzerCancel.cancelDate|escape}]</td>
                        <td>[{$oUnzerCancel.cancellationId|escape}]</td>
                        <td>[{$oUnzerCancel.cancelledAmount|escape|string_format:"%.2f"}] [{$uzrCurrency}]</td>
                        [{if $blCancelReasonReq}]
                            <td>[{$oUnzerCancel.cancelReason|escape}]</td>
                        [{/if}]
                    </tr>
                [{/foreach}]
            </table>
        [{/if}]
    [{/block}]
</div>

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
<script>
/* handle Unzer forms for refund */
let handleUnzerForm = function(formElement) {
    if(formElement.id.indexOf('uzr_') === 0) { // make absolutely sure to start with "uzr_"
        let paymentId = formElement.id.slice(4);
        let amountId = 'amount_' + paymentId; // f.e. "uzr_s-chg-1"
        let inAmount = document.getElementById(amountId);

        if (null !== inAmount &&
            window.confirm('[{oxmultilang ident="OSCUNZER_CANCEL_ALERT"}]' + ' ' + inAmount.value)) {
            return true;
        }
        return false;
    }
    // if it is not a form we want to process, let it proceed
    return true;
};

/* apply submit listener */
document.addEventListener('DOMContentLoaded', function () {
    let forms = document.querySelectorAll('form[id^="uzr_"]');
    for(var i = 0; i < forms.length; i++) {
        forms[i].addEventListener('submit', function() {
            return handleUnzerForm(this);
        });
    }
}, false);
</script>