[{assign var="payment" value=$oView->getPayment()}]
[{if $payment->getId() === 'oscunzer_applepay'}]
    [{include file="modules/osc/unzer/order/applepay_button.tpl"}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]