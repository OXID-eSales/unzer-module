[{capture assign="applePayAvailabilityCheck"}]
    [{strip}]
        $(function() {
            if(!window.ApplePaySession || !ApplePaySession.canMakePayments()) {
                $('[name="paymentid"][value="[{$sPaymentID}]"]').closest('.well').remove();
            }
        });
    [{/strip}]
[{/capture}]
[{oxscript add=$applePayAvailabilityCheck}]