[{assign var="sPaymentID" value=$payment->getId()}]
[{assign var="unzerpub" value=$oViewConf->getUnzerPubKey()}]
<div class="row">
    <div class="col-12 col-md-6" id="orderShipping">
        <form action="[{$oViewConf->getSslSelfLink()}]" method="post">
            <div class="hidden">
                [{$oViewConf->getHiddenSid()}]
                <input type="hidden" name="cl" value="payment">
                <input type="hidden" name="fnc" value="">
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        [{oxmultilang ident="SHIPPING_CARRIER"}]
                        <button type="submit"
                                class="btn btn-sm btn-warning float-right submitButton largeButton edit-button"
                                title="[{oxmultilang ident="EDIT"}]">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                    </h3>
                </div>
                <div class="card-body">
                    [{assign var="oShipSet" value=$oView->getShipSet()}]
                    [{$oShipSet->oxdeliveryset__oxtitle->value}]
                </div>
            </div>
        </form>
    </div>
    <div class="col-12 col-md-6" id="orderPayment">
        <div>
            <div class="card">
                <form action="[{$oViewConf->getSslSelfLink()}]" method="post">
                    <div class="card-header">
                        <h3 class="card-title">
                            [{oxmultilang ident="PAYMENT_METHOD"}]
                            [{$oViewConf->getHiddenSid()}]
                            <input type="hidden" name="cl" value="payment">
                            <input type="hidden" name="fnc" value="">
                            <button type="submit"
                                    class="btn btn-sm btn-warning float-right submitButton largeButton edit-button"
                                    title="[{oxmultilang ident="EDIT"}]">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </h3>
                    </div>
                </form>
                <div class="card-body">
                    [{$payment->oxpayments__oxdesc->value}]
                    [{if $sPaymentID == "oscunzer_card"}]
                        [{include file="@osc-unzer/frontend/tpl/order/unzer_card.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_eps"}]
                        [{include file="@osc-unzer/frontend/tpl/order/unzer_eps_charge.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_ideal"}]
                        [{include file="@osc-unzer/frontend/tpl/order/unzer_ideal.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_installment"}]
                        [{include file="@osc-unzer/frontend/tpl/order/unzer_installment.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_invoice"}]
                        [{include file="@osc-unzer/frontend/tpl/order/unzer_invoice.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_paypal"}]
                        [{include file="@osc-unzer/frontend/tpl/order/unzer_paypal.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_applepay"}]
                        [{include file="@osc-unzer/frontend/tpl/order/unzer_applepay.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_sepa"}]
                        [{include file="@osc-unzer/frontend/tpl/order/unzer_sepa.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_sepa-secured"}]
                        [{include file="@osc-unzer/frontend/tpl/order/unzer_sepa_secured.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_installment_paylater"}]
                        [{include file="@osc-unzer/frontend/tpl/order/unzer_installment_paylater.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_paypal"}]
                        [{include file="@osc-unzer/frontend/tpl/order/unzer_paypal.tpl"}]
                    [{/if}]
                </div>
            </div>
        </div>
    </div>
</div>
