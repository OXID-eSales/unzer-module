[{include file="modules/osc/unzer/unzer_assets.tpl"}]
[{if $oView->getPaymentSaveSetting()}]
    <div class="savedpayment">
        <form id="payment-saved-cards" class="unzerUI form" novalidate>
            [{foreach from=$unzerPaymentType item="setting" key="type"}]
            [{if $type == 'paypal'}]

            <table class="table">
                <thead>
                <tr>
                    <th scope="col">[{oxmultilang ident="EMAIL"}]</th>
                    <th scope="col">[{oxmultilang ident="OSCUNZER_BRAND"}]</th>
                    <th scope="col"></th>
                </tr>
                </thead>
                <tbody>
                [{assign var="counter" value=0}]

                [{foreach from=$setting item="paymentType" key=paymenttypeid }]
                    <tr>
                        <th scope="row">[{$paymentType.email}]</th>
                        <td>[{$type}]</td>
                        <td>
                            <input type="radio" class="paymenttypeid" name="paymenttypeid" value="[{$paymenttypeid}]" style="-webkit-appearance: radio">
                        </td>
                    </tr>
                    [{/foreach}]


                </tbody>
            </table>


            [{/if}]
            [{/foreach}]
            [{if $oView->getPaymentSaveSetting()}]
            <div id="payment-sepa-confirm">
                <div class="oscunzersavepayment" id="oscunzersavepayment_unzer">
                    <input id="oscunzersavepayment" type="checkbox" name="oscunzersavepayment" value="0" style="-webkit-appearance: checkbox">
                    <label for="oscunzersavepayment">
                        [{oxmultilang ident="OSCUNZER_SAVE_PAYMENT"}]
                    </label>
                </div>
            </div>
            [{/if}]
        </form>
    </div>


    [{if false}]
    <script>
        [{/if}]
            [{capture assign="unzerPaypalJS"}]
        $( '#orderConfirmAgbBottom' ).submit(function( event ) {
            if(!$( '#orderConfirmAgbBottom' ).hasClass("submitable")){
                event.preventDefault();
                $( "#payment-saved-cards" ).submit();
            }
        });

        // Handling payment form submission
        $( "#payment-saved-cards" ).submit(function( event ) {
            event.preventDefault();
            let selectedPaymentTypeId = $('input[name=paymenttypeid]:checked').val();
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
            let hiddenInput2 = $(document.createElement('input'))
                .attr('type', 'hidden')
                .attr('name', 'oscunzersavepayment')
                .val($('#oscunzersavepayment').is(':checked') ? '1' : '0');
            $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput2);
            $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput3);

            $('#orderConfirmAgbBottom').addClass("submitable");
            $("#orderConfirmAgbBottom").submit();
        });
        [{/capture}]
            [{if false}]
    </script>
    [{/if}]
    [{oxscript add=$unzerPaypalJS}]
    [{/if}]
