[{if $payment->oxuserpayments__oxpaymentsid->value == "oscunzer_invoice-secured" || $payment->oxuserpayments__oxpaymentsid->value == "oscunzer_invoice" || $payment->oxuserpayments__oxpaymentsid->value == "oscunzer_prepayment"}]
    [{$oViewConf->getSessionPaymentInfo()|strip_tags}]
[{/if}]

[{$smarty.block.parent}]
