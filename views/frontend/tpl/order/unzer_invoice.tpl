<form id="payment-form" action="[{$oViewConf->getSelfActionLink()}]" class="unzerUI form" novalidate>
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="fnc" value="validatePayment">
    <input type="hidden" name="cl" value="unzer_dispatcher">
    <input type="hidden" name="paymentid" value="[{$paymentid}]">
    <button class="unzerUI primary button fluid" id="submit-button" type="submit">[{oxmultilang ident="PAY"}]</button>
</form>

<script>
    let unzerInstance = new unzer("[{$oViewConf->getUnzerPubKey()}]");
</script>
