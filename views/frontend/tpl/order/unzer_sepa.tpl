[{include file="modules/osc/unzer/unzer_assets.tpl"}]
[{if $savedPaymentTypes != false }]
<div class="savedpayment">
    [{foreach from=$savedPaymentTypes item="setting" key="type"}]
    [{if $type == 'sepa'}]
    <form id="payment-saved-cards" class="unzerUI form" novalidate>
        <table class="table">
            <thead>
            <tr>
                <th scope="col">[{oxmultilang ident="OSCUNZER_HOLDER"}]</th>
                <th scope="col">[{oxmultilang ident="OSCUNZER_IBAN"}]</th>
                <th scope="col"></th>
            </tr>
            </thead>
            <tbody>

            [{foreach from=$setting item="paymentType" }]
                <tr>
                    <th scope="row">[{$paymentType.holder}]</th>
                    <td>[{$paymentType.iban}]</td>
                    <td>
                        <input type="radio" class="paymenttypeid" name="paymenttypeid" value="[{$paymentType.id}]"
                               style="-webkit-appearance: radio">
                    </td>
                </tr>
                [{/foreach}]


            </tbody>
        </table>
        <div id="payment-sepa-confirm">
            <div class="sepaagreement" id="sepaagree_unzer">
                <input id="oscunzersepaagreement" type="checkbox" name="oscunzersepaagreement" value="0"  style="-webkit-appearance: checkbox">
                <label for="oscunzersepaagreement">
                    [{oxifcontent ident="oscunzersepamandateconfirmation" object="oCont"}]
                    [{$oCont->oxcontents__oxcontent->value}]
                    [{/oxifcontent}]
                </label>
            </div>
        </div>
    </form>
    [{/if}]
    [{/foreach}]
</div>
[{/if}]
[{if $savedPaymentTypes.sepa != false }]
<br>
    <label>
        <input type="checkbox" name="newccard" id="newccard" value="show"  style="-webkit-appearance: checkbox"> Neue IBAN
    </label>
    [{/if}]
<div id="newsepa" style="display:none;">
    <form id="payment-form-sepa">
        <br/>
        <div id="sepa-IBAN" class="field">
            <!-- The IBAN field UI Element will be inserted here -->
        </div>
        <br/>
        <div id="payment-sepa-confirm">
            <div class="sepaagreement" id="sepaagree_unzer">
                <input id="oscunzersepaagreement" type="checkbox" name="oscunzersepaagreement" value="0">
                <label for="oscunzersepaagreement">
                    [{oxifcontent ident="oscunzersepamandateconfirmation" object="oCont"}]
                    [{$oCont->oxcontents__oxcontent->value}]
                    [{/oxifcontent}]
                </label>
            </div>
        </div>
        [{if $oView->getPaymentSaveSetting()}]
        <div class="oscunzersavepayment" id="oscunzersavepayment_unzer">
            <input id="oscunzersavepayment" type="checkbox" name="oscunzersavepayment" value="0"
                   style="-webkit-appearance: checkbox">
            <label for="oscunzersavepayment">
                [{oxmultilang ident="OSCUNZER_SAVE_PAYMENT"}]
            </label>
        </div>
        [{/if}]
        <div class="field" id="error-holder" style="color: #9f3a38"></div>

    </form>
</div>
[{if false}]
<script>
    [{/if}]
        [{capture assign="unzerSepaDirectJS"}]
    $('input[name="newccard"]').on('change', function() {
        if ($(this).prop('checked')) {
            console.log('checked new card');
            $('#orderConfirmAgbBottom').addClass('new-card-selected');
        } else {
            console.log('not checked new card');
            $('#orderConfirmAgbBottom').removeClass('new-card-selected');
        }
    });

    $('#orderConfirmAgbBottom').submit(function(event) {

        if ($(this).hasClass('new-card-selected') && !$(this).hasClass("submitable")) {
            console.log('submit new card form');
            event.preventDefault();

            $("#payment-form-sepa").submit();
        } else if (!$(this).hasClass("submitable")) {
            console.log('submit saved cards form');
            event.preventDefault();

            $("#payment-saved-cards").submit();
        }
        $('#orderConfirmAgbBottom').removeClass('new-card-selected');
    });

    // Create an Unzer instance with your public key
    let unzerInstance = new unzer('[{$unzerpub}]', {locale: "[{$unzerLocale}]"});
    if ($('input[name="newccard"]').length === 0) {
        $('#orderConfirmAgbBottom').addClass('new-card-selected');
        $('#newsepa').show();
        let SepaDirectDebit = unzerInstance.SepaDirectDebit();

        SepaDirectDebit.create('sepa-direct-debit', {
            containerId: 'sepa-IBAN'
        });
    } else {
        $('input[name="newccard"]').on('change', function() {
            SepaDirectDebit = unzerInstance.SepaDirectDebit();

            if ($(this).prop('checked')) {

                SepaDirectDebit.create('sepa-direct-debit', {
                    containerId: 'sepa-IBAN'
                });
            } else {
                removeCardElements();
                console.log('remove');
            }
        });
    }


    // Create a SEPA Direct Debit instance and render the form
    let SepaDirectDebit = unzerInstance.SepaDirectDebit();
    SepaDirectDebit.create('sepa-direct-debit', {
        containerId: 'sepa-IBAN'
    });

    // Handling payment form submission
    $("#payment-form-sepa").submit(function (event) {
        event.preventDefault();
        // Creating a SEPA resource
        if (SepaDirectDebit) {
            SepaDirectDebit.createResource()
                .then(function (result) {

                    let hiddenInput = $(document.createElement('input'))
                        .attr('type', 'hidden')
                        .attr('name', 'paymentData')
                        .val(JSON.stringify(result));
                    $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput);

                    let hiddenInput1 = $(document.createElement('input'))
                        .attr('type', 'hidden')
                        .attr('name', 'sepaConfirmation')
                        .val($('.sepaagreement #oscunzersepaagreement').is(':checked') ? '1' : '0');
                    $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput1);
                    console.log(hiddenInput1);
                    let hiddenInput2 = $(document.createElement('input'))
                        .attr('type', 'hidden')
                        .attr('name', 'oscunzersavepayment')
                        .val($('#oscunzersavepayment').is(':checked') ? '1' : '0');
                    $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput2);

                    $('#orderConfirmAgbBottom').addClass("submitable");
                    $("#orderConfirmAgbBottom").submit();
                })
                .catch(function (error) {
                    $('#error-holder').html(error.message);
                    $('html, body').animate({
                        scrollTop: $("#orderPayment").offset().top - 150
                    }, 350);
                })
        }
    });
    $( "#payment-saved-cards" ).submit(function( event ) {
        console.log('submit saved cards event');

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
        let hiddenInput1 = $(document.createElement('input'))
            .attr('type', 'hidden')
            .attr('name', 'sepaConfirmation')
            .val($('#oscunzersepaagreement').is(':checked') ? '1' : '0');
        $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput1);

        let hiddenInput2 = $(document.createElement('input'))
            .attr('type', 'hidden')
            .attr('name', 'oscunzersavepayment')
            .val($('#oscunzersavepayment').is(':checked') ? '1' : '0');
        $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput2);
        $('#orderConfirmAgbBottom' ).addClass("submitable");
        $('#orderConfirmAgbBottom').submit();
    });
    $('input[name="newccard"]').on('change', function() {
        if ($(this).prop('checked')) {
            // Gespeicherte Zahlungsarten ausblenden und Radiobuttons abwählen
            $('.savedpayment').fadeOut();
            $('input[name="paymenttypeid"]').prop('checked', false);

            // Neue Kreditkarte anzeigen
            $('#newsepa').fadeIn();
        } else {
            // Gespeicherte Zahlungsarten anzeigen
            $('.savedpayment').fadeIn();

            // Neue Kreditkarte ausblenden
            $('#newsepa').fadeOut();
        }
    });
    function removeCardElements() {
        // Clear the contents of the Card-Element containers
        $('#sepa-IBAN').empty();
    }
    [{/capture}]
        [{oxscript add=$unzerSepaDirectJS}]
