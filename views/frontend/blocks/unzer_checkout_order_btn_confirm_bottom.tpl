[{assign var="payment" value=$oView->getPayment()}]

[{if $payment->isUnzerPayment()}]
    <form action="[{$oViewConf->getSslSelfLink()}]" method="post" id="orderConfirmAgbBottom" class="form-horizontal">
        <div class="hidden">
            [{$oViewConf->getHiddenSid()}]
            [{$oViewConf->getNavFormParams()}]
            <input type="hidden" name="cl" value="unzer_dispatcher">
            <input type="hidden" name="fnc" value="validatePayment">
            <input type="hidden" name="challenge" value="[{$challenge}]">
            <input type="hidden" name="paymentid" value="[{$payment->getId()}]">
            <input type="hidden" name="sDeliveryAddressMD5" value="[{$oView->getDeliveryAddressMD5()}]">

            [{if $oView->isActive('PsLogin') || !$oView->isConfirmAGBActive()}]
                <input type="hidden" name="ord_agb" value="1">
            [{else}]
                <input type="hidden" name="ord_agb" value="0">
            [{/if}]
            <input type="hidden" name="oxdownloadableproductsagreement" value="0">
            <input type="hidden" name="oxserviceproductsagreement" value="0">
        </div>

        <div class="card bg-light cart-buttons">
            <div class="card-body">

                [{block name="checkout_order_btn_submit_bottom"}]
                    <button type="submit" class="btn btn-lg btn-primary float-right submitButton nextStep largeButton">
                        <i class="fa fa-check"></i> [{oxmultilang ident="SUBMIT_ORDER"}]
                    </button>
                [{/block}]

            </div>
        </div>
    </form>
[{else}]
    [{$smarty.block.parent}]
[{/if}]