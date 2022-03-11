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

<div id="liste">
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
                </tr>
                [{foreach from=$oUnzerTransactions item="oUnzerTransaction"}]
                    [{assign var="transaction_type_id" value=$oUnzerTransaction->getUnzerAction()|escape}]
                    <tr>
                        <td>[{$oUnzerTransaction->getUnzerCreated()|escape}]</td>
                        <td>[{$oUnzerTransaction->getUnzerShortId()|escape}]</td>
                        <td>[{$oUnzerTransaction->getUnzerTraceId()|escape}]</td>
                        <td>[{$oUnzerTransaction->getUnzerCustomerId()|escape}]</td>
                        <td>[{'OSCUNZER_TRANSACTION_TYPEID_'|cat:$transaction_type_id|oxmultilangassign}]</td>
                        <td>[{$oUnzerTransaction->getUnzerTypeId()|escape}]</td>
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
                            <tr>
                                <td>[{$oUnzerShipment.shipingDate|escape}]</td>
                                <td>[{$oUnzerShipment.shipId|escape}]</td>
                                <td>[{$oUnzerShipment.amount|escape}]</td>
                                <td>[{$oUnzerShipment.invoiceid|escape}]</td>
                            </tr>
                        [{/foreach}]
                    </tbody>
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
                        <tbody>
                            <tr>
                                <td><input type="text" name="amount" value="[{$AuthAmountRemaining}]"> [{$AuthCur}]</td>
                                <td><button type="submit">[{oxmultilang ident="OSCUNZER_CHARGE_COLLECT"}]</button></td>
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
                        <td class="listheader">[{oxmultilang ident="OSCUNZER_CHARGE_CANCELREASON"}]</td>
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
                            <td>[{$oUnzerCharge.cancelledAmount|escape|string_format:"%.2f"}] [{$uzrCurrency}]</td>

                            <td>
                                <select name="reason" id="reason_[{$oUnzerCharge.chargeId}]" [{if !$oUnzerCharge.cancellationPossible}]disabled[{/if}]>
                                    [{if !$blCancelReasonReq}]<option value="">[{oxmultilang ident="OSCUNZER_REASON_NONE"}]</option>[{/if}]
                                    <option value="CANCEL">[{oxmultilang ident="OSCUNZER_REASON_CANCEL"}]</option>
                                    <option value="RETURN">[{oxmultilang ident="OSCUNZER_REASON_RETURN"}]</option>
                                    <option value="CREDIT">[{oxmultilang ident="OSCUNZER_REASON_CREDIT"}]</option>
                                </select>
                            </td>
                            <td><input type="text" name="amount" id="amount_[{$oUnzerCharge.chargeId}]" value="[{$oUnzerCharge.chargedAmount|string_format:"%.2f"}]" [{if !$oUnzerCharge.cancellationPossible}]disabled[{/if}]> [{$uzrCurrency}]</td>
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
                    <td class="listheader">[{oxmultilang ident="OSCUNZER_CHARGE_CANCELREASON"}]</td>
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
</div>

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
