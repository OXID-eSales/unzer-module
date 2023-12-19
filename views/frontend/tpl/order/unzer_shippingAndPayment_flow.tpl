[{assign var="sPaymentID" value=$payment->getId()}]
[{assign var="unzerpub" value=$oViewConf->getUnzerPubKey()}]
<div class="row">
    <div class="col-xs-12 col-md-6" id="orderShipping">
        <form action="[{$oViewConf->getSslSelfLink()}]" method="post">
            <div class="hidden">
                [{$oViewConf->getHiddenSid()}]
                <input type="hidden" name="cl" value="payment">
                <input type="hidden" name="fnc" value="">
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        [{oxmultilang ident="SHIPPING_CARRIER"}]
                        <button type="submit"
                                class="btn btn-xs btn-warning pull-right submitButton largeButton"
                                title="[{oxmultilang ident="EDIT"}]">
                            <i class="fa fa-pencil"></i>
                        </button>
                    </h3>
                </div>
                <div class="panel-body">
                    [{assign var="oShipSet" value=$oView->getShipSet()}]
                    [{$oShipSet->oxdeliveryset__oxtitle->value}]
                </div>
            </div>
        </form>
    </div>
    <div class="col-xs-12 col-md-6" id="orderPayment">
        <div>
            <div class="panel panel-default">
                <form class="panel-heading" action="[{$oViewConf->getSslSelfLink()}]" method="post">
                    <h3 class="panel-title">
                        [{oxmultilang ident="PAYMENT_METHOD"}]
                        [{$oViewConf->getHiddenSid()}]
                        <input type="hidden" name="cl" value="payment">
                        <input type="hidden" name="fnc" value="">
                        <button type="submit"
                                class="btn btn-xs btn-warning pull-right submitButton largeButton"
                                title="[{oxmultilang ident="EDIT"}]">
                            <i class="fa fa-pencil"></i>
                        </button>
                    </h3>
                </form>
                <div class="panel-body">
                    [{$payment->oxpayments__oxdesc->value}]
                    [{if $sPaymentID == "oscunzer_card"}]
                        [{include file="modules/osc/unzer/unzer_card.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_eps"}]
                        [{include file="modules/osc/unzer/unzer_eps_charge.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_ideal"}]
                        [{include file="modules/osc/unzer/unzer_ideal.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_installment"}]
                        [{include file="modules/osc/unzer/unzer_installment.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_invoice"}]
                        [{include file="modules/osc/unzer/unzer_invoice.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_paypal"}]
                        [{include file="modules/osc/unzer/unzer_paypal.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_applepay"}]
                        [{include file="modules/osc/unzer/unzer_applepay.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_sepa"}]
                        [{include file="modules/osc/unzer/unzer_sepa.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_sepa-secured"}]
                        [{include file="modules/osc/unzer/unzer_sepa_secured.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_paypal"}]
                        [{include file="modules/osc/unzer/unzer_paypal.tpl"}]
                    [{elseif $sPaymentID == "oscunzer_installment_paylater"}]
                        [{include file="modules/osc/unzer/unzer_installment_paylater.tpl"}]
                    [{/if}]
                </div>
            </div>
        </div>
    </div>
</div>
