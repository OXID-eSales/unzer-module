[{assign var="isUnzerPayment" value=$listitem->oxorder__oxpaymenttype->value|stripos:"oscunzer"}]
[{if $isUnzerPayment !== false}]
    [{$smarty.block.parent}]
    <script type="text/javascript">
        var elements = document.getElementsByClassName("order_no");
        var unzer_order = elements[elements.length-1].getElementsByTagName("a");
        unzer_order[0].innerHTML = "[{ listitem.oxorder__oxordernr.value }] ([{ translate({ ident: 'OSCUNZER_TRANSACTION_ORDERNR', suffix: 'COLON' }) }] [{ listitem.oxorder__oxunzerordernr.value }])";
    </script>
[{else}]
    [{$smarty.block.parent}]
[{/if}]
