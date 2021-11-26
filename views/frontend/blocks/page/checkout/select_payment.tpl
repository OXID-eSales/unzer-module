[{if !$paymentmethod->isUnzerPayment() || ($paymentmethod->isUnzerPayment() && $paymentmethod->isUnzerPaymentTypeAllowed())}]
    [{$smarty.block.parent}]
[{/if}]