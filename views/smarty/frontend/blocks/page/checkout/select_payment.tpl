[{if $paymentmethod->isUnzerPayment() && $paymentmethod->isUnzerPaymentTypeAllowed()}]
    [{if $sPaymentID == 'oscunzer_applepay'}]
        [{include file="@osc-unzer/frontend/tpl/payment/applepay_availibility_check.tpl"}]
    [{/if}]
    <div class="well well-sm">
        [{* We include it as template, so that it can be modified in custom themes *}]
        [{include file="@osc-unzer/frontend/tpl/payment/payment_unzer.tpl"}]
    </div>
[{else}]
    [{$smarty.block.parent}]
[{/if}]