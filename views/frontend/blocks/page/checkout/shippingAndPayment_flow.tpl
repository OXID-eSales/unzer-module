[{assign var="payment" value=$oView->getPayment()}]
[{if $oViewConf->getjQueryImport()}]
    <script
            src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
            crossorigin="anonymous"></script>
[{/if}]
[{if $payment->isUnzerPayment()}]
    [{include file="modules/osc/unzer/unzer_shippingAndPayment_flow.tpl"}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]