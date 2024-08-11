[{include file="modules/osc/unzer/unzer_assets.tpl"}]


    <div class="savedpayment">
        <form id="payment-saved-cards" class="unzerUI form" novalidate>
            [{if $oView->getPaymentSaveSetting() and $lastSavedPayPalPaymentType}]

                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">[{oxmultilang ident="EMAIL"}]</th>
                        <th scope="col">[{oxmultilang ident="OSCUNZER_BRAND"}]</th>
                        <th scope="col"></th>
                    </tr>
                    </thead>
                    <tbody>
                     <tr>
                        <th scope="row">[{$lastSavedPayPalPaymentType.email}]</th>
                        <td>[{oxmultilang ident="OSCUNZER_PAYMENT_METHOD_PAYPAL"}]</td>
                        <td>
                            <input type="radio" class="paymenttypeid" name="paymenttypeid" value="[{$lastSavedPayPalPaymentType.id}]" style="-webkit-appearance: radio">
                        </td>
                        </tr>
                    </tbody>
                </table>
            [{/if}]

            [{if $oView->getPaymentSaveSetting()}]
            <div id="payment-sepa-confirm">
                <div class="oscunzersavepayment" id="oscunzersavepayment_unzer">
                    <input id="oscunzersavepayment" type="checkbox" name="oscunzersavepayment" value="0" style="-webkit-appearance: checkbox">
                    <label for="oscunzersavepayment">
                        [{oxmultilang ident="OSCUNZER_SAVE_PAYMENT_PAYPAL"}]
                    </label>
                </div>
            </div>
            [{/if}]
        </form>
    </div>

[{capture assign="unzerPaypalJS"}]
[{if false}]<script>[{/if}]

        let orderConfirmAgbBottom = $('#orderConfirmAgbBottom');
        let savedCardsTableEl = $('#payment-saved-cards');

        orderConfirmAgbBottom.submit(function( event ) {
            if(!orderConfirmAgbBottom.hasClass("submitable")){
                event.preventDefault();
                savedCardsTableEl.submit();
            }
        });

        // Handling payment form submission
        savedCardsTableEl.submit(function( event ) {
            event.preventDefault();
            let selectedPaymentTypeId = $('input[name=paymenttypeid]:checked').val();
            if (selectedPaymentTypeId) {
                let paymentData = {
                    id: selectedPaymentTypeId,
                    resources: {
                        typeId: selectedPaymentTypeId
                    }
                };
                let paymentDataString = JSON.stringify(paymentData);
                let hiddenInput3 = $(document.createElement('input'))
                    .attr('type', 'hidden')
                    .attr('name', 'paymentData')
                    .val(paymentDataString);
                let hiddenInput4 = $(document.createElement('input'))
                    .attr('type', 'hidden')
                    .attr('name', 'is_saved_payment_in_action')
                    .val(1);
                orderConfirmAgbBottom.find(".hidden")
                    .append(hiddenInput3)
                    .append(hiddenInput4);
            }
            let hiddenInput2 = $(document.createElement('input'))
                .attr('type', 'hidden')
                .attr('name', 'oscunzersavepayment')
                .val($('#oscunzersavepayment').is(':checked') ? '1' : '0');

            orderConfirmAgbBottom.find(".hidden")
                .append(hiddenInput2)
            orderConfirmAgbBottom.addClass("submitable")
                .submit();
        });

 [{if false}]</script>[{/if}]
    [{/capture}]
    [{oxscript add=$unzerPaypalJS}]
