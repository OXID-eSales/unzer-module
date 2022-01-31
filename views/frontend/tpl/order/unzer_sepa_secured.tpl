[{include file="modules/osc/unzer/unzer_assets.tpl"}]

<form id="payment-form-sepa-secured">
    <div id="sepa-secured-IBAN" class="field">
        <!-- The IBAN field UI Element will be inserted here -->
    </div>

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

    <div class="field" id="error-holder" style="color: #9f3a38"> </div>

</form>

[{capture assign="unzerSepaDirectSecurredJS"}]

    $( '#orderConfirmAgbBottom' ).submit(function( event ) {
        if(!$( '#orderConfirmAgbBottom' ).hasClass("submitable")){
            event.preventDefault();
            $( "#payment-form-sepa-secured" ).submit();
        }
    });

    // Create an Unzer instance with your public key
    let unzerInstance = new unzer('[{$unzerpub}]');

    // Create a SEPA Direct Debit Secured instance and render the form
    let SepaDirectDebitSecured = unzerInstance.SepaDirectDebitSecured();
    SepaDirectDebitSecured.create('sepa-direct-debit-secured', {
        containerId: 'sepa-secured-IBAN'
    });

    // Handling payment form submission
    $( "#payment-form-sepa-secured" ).submit(function( event ) {
        event.preventDefault();
        // Creating a SEPA resource
        SepaDirectDebitSecured.createResource()
        .then(function(result) {

            let hiddenInput = $(document.createElement('input'))
            .attr('type', 'hidden')
            .attr('name', 'paymentData')
            .val(JSON.stringify(result));
            $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput);

            let hiddenInput2 = $(document.createElement('input'))
            .attr('type', 'hidden')
            .attr('name', 'sepaConfirmation')
            .val($('#oscunzersepaagreement').is(':checked') ? '1' : '0');
            $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput2);

            $( '#orderConfirmAgbBottom' ).addClass("submitable");
            $( "#orderConfirmAgbBottom" ).submit();
        })
        .catch(function(error) {
            $('#error-holder').html(error.message);
            $('html, body').animate({
            scrollTop: $("#orderPayment").offset().top - 150
            }, 350);
        })
    });
[{/capture}]
[{oxscript add=$unzerSepaDirectSecurredJS}]