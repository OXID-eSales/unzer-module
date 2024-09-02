[{include file="@osc-unzer/frontend/tpl/order/unzer_assets.tpl"}]


    <div class="savedpayment">
        <form id="payment-saved-cards" class="unzerUI form" novalidate>
            [{if $oView->getPaymentSaveSetting()}]
                [{foreach from=$unzerPaymentType item="setting" key="type"}]
                    [{if $unzerPaymentType != false}]
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

                    [{/if}]
                [{/foreach}]
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
            [{/if}]
        </form>
    </div>

[{if false}]<script>[{/if}]

[{capture assign="unzerPaypalJS"}]
    $(document).ready(function() {
        const submitButton = $('.submitButton.nextStep');
        const radioButtons = $('input[type="radio"].paymenttypeid');
        const overrideCheckbox1 = $('#oscunzersavepayment');
        const overrideCheckbox2 = $('#newccard');

        function updateSubmitButtonState() {
            const isRadioSelected = radioButtons.is(':checked');
            const isAnyCheckboxChecked = (overrideCheckbox1.length && overrideCheckbox1.is(':checked')) ||
                                         (overrideCheckbox2.length && overrideCheckbox2.is(':checked'));

            if (isRadioSelected || isAnyCheckboxChecked) {
                submitButton.removeClass('disabled');
            } else {
                submitButton.addClass('disabled');
            }
        }

        if (radioButtons.length > 0) {
            submitButton.addClass('disabled');
            radioButtons.on('change', updateSubmitButtonState);
            if (overrideCheckbox1.length) {
                overrideCheckbox1.on('change', updateSubmitButtonState);
            }
            if (overrideCheckbox2.length) {
                overrideCheckbox2.on('change', updateSubmitButtonState);
            }
        }

        submitButton.on("click", function(event) {
            if ($(this).hasClass('disabled')) {
                event.preventDefault();
            } else {
                $(this).addClass('disabled');
            }
        });

        overrideCheckbox2.on('change', function() {
            if ($(this).prop('checked')) {
                $('.savedpayment').fadeOut();
                radioButtons.prop('checked', false);
                $('#newcc').fadeIn();
                addPaymentElements(Card);
            } else {
                $('.savedpayment').fadeIn();
                $('#newcc').fadeOut();
                removeCardElements();
                if (Card && Card.destroy) {
                    Card.destroy();
                }
            }
        });

        $('#orderConfirmAgbBottom').submit(function(event) {
            if (!$('#orderConfirmAgbBottom').hasClass("submitable")) {
                event.preventDefault();
                $("#payment-saved-cards").submit();
            }
        });

        $("#payment-saved-cards").submit(function(event) {
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

            let hiddenInput4 = $(document.createElement('input'))
                .attr('type', 'hidden')
                .attr('name', 'is_saved_payment_in_action')
                .val(1);

            $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput4);
            $('#orderConfirmAgbBottom').addClass("submitable");
            $("#orderConfirmAgbBottom").submit();
        });
    });
[{/capture}]


[{if false}]</script>[{/if}]
[{oxscript add=$unzerPaypalJS}]
