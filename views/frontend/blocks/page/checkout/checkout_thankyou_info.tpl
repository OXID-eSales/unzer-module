[{$smarty.block.parent}]
[{if $order->oxorder__oxpaymenttype->value == "oscunzer_invoice-secured" || $order->oxorder__oxpaymenttype->value == "oscunzer_invoice" || $order->oxorder__oxpaymenttype->value == "oscunzer_prepayment"}]
    <div>[{$oViewConf->getSessionPaymentInfo()}]</div>
[{/if}]