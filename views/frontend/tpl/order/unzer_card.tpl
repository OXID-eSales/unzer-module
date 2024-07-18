[{include file="modules/osc/unzer/unzer_assets.tpl"}]
[{if $unzerPaymentType}]
    <div class="savedpayment">
        [{foreach from=$unzerPaymentType item="setting" key="type"}]
            [{if $type != 'paypal' && $type != 'sepa'}]
                [{assign var="savedCardsCount" value=$setting|@count}]
                <form id="payment-saved-cards" class="unzerUI form" novalidate>
                    <input type="hidden" name="savedCardsCount" value="[{$savedCardsCount}]">
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">[{oxmultilang ident="OSCUNZER_CARD_NUMBER"}]</th>
                            <th scope="col">[{oxmultilang ident="OSCUNZER_EXPIRY_DATE"}]</th>
                            <th scope="col">[{oxmultilang ident="OSCUNZER_BRAND"}]</th>
                            <th scope="col"></th>
                        </tr>
                        </thead>
                        <tbody>
                            [{assign var="counter" value=0}]
                            [{foreach from=$setting item="paymentType" key=paymenttypeid }]
                                <tr>
                                    <th scope="row">[{$paymentType.number}]</th>
                                    <td>[{$paymentType.expiryDate}]</td>
                                    <td>[{$type}]</td>

                                    <td>
                                        <input type="radio" class="paymenttypeid" name="paymenttypeid" value="[{$paymenttypeid}]" style="-webkit-appearance: radio">
                                    </td>
                                </tr>
                            [{/foreach}]
                        </tbody>
                    </table>
                </form>
            [{/if}]
        [{/foreach}]
    </div>
[{/if}]

[{if $unzerPaymentType != false }]
<br>
    <label id="addNewCardCheckboxLabel">
        <input type="checkbox" name="newccard" id="newccard" value="show"  style="-webkit-appearance: checkbox">[{oxmultilang ident="OSCUNZER_NEW_CARD"}]
    </label>
[{/if}]
<div id="newcc" style="display:none;">
    <form id="payment-form-card" class="unzerUI form" novalidate>
        <div class="field">
            <div id="card-element-id-number" class="unzerInput">
                <!-- Card number UI Element will be inserted here. -->
            </div>
        </div>
        <div class="two fields">
            <div class="field ten wide">
                <div id="card-element-id-expiry" class="unzerInput">
                    <!-- Card expiry date UI Element will be inserted here. -->
                </div>
            </div>
            <div class="field six wide">
                <div id="card-element-id-cvc" class="unzerInput">
                    <!-- Card CVC UI Element will be inserted here. -->
                </div>
            </div>

        </div>
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
    <style>
        .unzerUI.form .error.message {
            line-height: 2rem;
        }
    </style>
</div>

[{if false}]
    <script>
[{/if}]
[{capture assign="unzerCardJS"}]

    $('input[name="newccard"]').on('change', function() {
        if ($(this).prop('checked')) {
            $('#orderConfirmAgbBottom').addClass('new-card-selected');
        } else {
            $('#orderConfirmAgbBottom').removeClass('new-card-selected');
        }
    });

    $('#orderConfirmAgbBottom').submit(function(event) {

        if ($(this).hasClass('new-card-selected') && !$(this).hasClass("submitable")) {
            event.preventDefault();
            $("#payment-form-card").submit();
        } else if (!$(this).hasClass("submitable")) {
            event.preventDefault();
            $("#payment-saved-cards").submit();
        }
        $('#orderConfirmAgbBottom').removeClass('new-card-selected');
    });

    // Create an Unzer instance with your public key
    let unzerInstance = new unzer('[{$unzerpub}]', {locale: "[{$unzerLocale}]"});
    let newCardCheckbox = $('input[name="newccard"]');
    let savedCardsTableElement = $('#payment-saved-cards');
    let cardsCount = 0;
    if (savedCardsTableElement.length) {
        cardsCount = parseInt($('input[name=savedCardsCount]').attr('value'), 10);
    }
    if (newCardCheckbox.length === 0 || cardsCount === 0) {
         let hiddenInput4 = $(document.createElement('input'))
                        .attr('type', 'hidden')
                        .attr('name', 'is_saved_payment_in_action')
                        .val(0);
        $('#orderConfirmAgbBottom').addClass('new-card-selected');
        $('#newcc').show();
        if (cardsCount === 0) {
            $('#addNewCardCheckboxLabel').hide();
        }
        Card = unzerInstance.Card();
        addPaymentElements(Card);
    } else {
        $('input[name="newccard"]').on('change', function() {
            Card = unzerInstance.Card();
            if ($(this).prop('checked')) {
                // Create a Card instance and render the input fields
                addPaymentElements(Card);
            } else {
                removeCardElements();
                if (Card && Card.destroy) {
                    Card.destroy();
                }
            }
        });
    }

    $( "#payment-form-card" ).submit(function( event ) {
        event.preventDefault();
        if (Card) {
            Card.createResource()
                .then(function(result) {
                    let hiddenInput = $(document.createElement('input'))
                        .attr('type', 'hidden')
                        .attr('name', 'paymentData')
                        .val(JSON.stringify(result));
                    let hiddenInput2 = $(document.createElement('input'))
                        .attr('type', 'hidden')
                        .attr('name', 'oscunzersavepayment')
                        .val($('#oscunzersavepayment').is(':checked') ? '1' : '0');

                    $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput2);
                    $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput);

                    $('#orderConfirmAgbBottom' ).addClass("submitable");
                    $('#orderConfirmAgbBottom').submit();
                })
                .catch(function(error) {
                    $('html, body').animate({
                        scrollTop: $("#orderPayment").offset().top - 150
                    }, 350);
                    $('#orderConfirmAgbBottom').addClass('new-card-selected');
                })
        }
        $('#orderConfirmAgbBottom').addClass('new-card-selected');
    });
    $( "#payment-saved-cards" ).submit(function( event ) {
        event.preventDefault();
        let selectedPaymentTypeId = $('input[name=paymenttypeid]:checked').val();
        let paymentData = {
            id: selectedPaymentTypeId
        };
        let paymentDataString = JSON.stringify(paymentData);

        let hiddenInput3 = $(document.createElement('input'))
            .attr('type', 'hidden')
            .attr('name', 'paymentData')
            .val(paymentDataString);
        $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput3);

        let hiddenInput4 = $(document.createElement('input'))
                        .attr('type', 'hidden')
                        .attr('name', 'is_saved_payment_in_action')
                        .val(1);

        $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput4);
        $('#orderConfirmAgbBottom' ).addClass("submitable");
        $('#orderConfirmAgbBottom').submit();
    });

    $('input[name="newccard"]').on('change', function() {
        if ($(this).prop('checked')) {
            // Hide saved payment methods and deselect radio buttons
            $('.savedpayment').fadeOut();
            $('input[name="paymenttypeid"]').prop('checked', false);

            // Neue Kreditkarte anzeigen
            $('#newcc').fadeIn();
        } else {
            // Gespeicherte Zahlungsarten anzeigen
            $('.savedpayment').fadeIn();

            // Neue Kreditkarte ausblenden
            $('#newcc').fadeOut();
        }
    });
    function removeCardElements() {
        // Clear the contents of the Card-Element containers
        $('#card-element-id-number').empty();
        $('#card-element-id-expiry').empty();
        $('#card-element-id-cvc').empty();
    }
    function addPaymentElements(Card) {
        // Clear the contents of the Card-Element containers
        Card.create('number', {
            containerId: 'card-element-id-number',
            onlyIframe: false
        });
        Card.create('expiry', {
            containerId: 'card-element-id-expiry',
            onlyIframe: false
        });
        Card.create('cvc', {
            containerId: 'card-element-id-cvc',
            onlyIframe: false
        });

    }
[{/capture}]
[{if false}]
    </script>
[{/if}]
[{oxscript add=$unzerCardJS}]
