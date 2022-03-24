<dl>
    <dt>
        <input id="payment_[{$sPaymentID}]" type="radio" name="paymentid" value="[{$sPaymentID}]" [{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}]checked[{/if}]>
        <label for="payment_[{$sPaymentID}]"><b>[{$paymentmethod->oxpayments__oxdesc->value}]</b></label>
    </dt>
    <dd class="payment-option[{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}] activePayment[{/if}]">
        <div class="clearfix"></div>
        [{block name="checkout_payment_longdesc"}]
            [{if $paymentmethod->oxpayments__oxlongdesc->value|strip_tags|trim}]
                <div class="desc">
                    [{$paymentmethod->oxpayments__oxlongdesc->getRawValue()}]
                </div>
            [{/if}]
        [{/block}]
    </dd>
</dl>