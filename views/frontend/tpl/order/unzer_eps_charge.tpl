[{include file="modules/osc/unzer/unzer_assets.tpl"}]

<form id="payment-form" class="unzerUI form" novalidate>
    <div id="unzer-eps" class="field"></div>
    <div class="field" id="error-holder" style="color: #9f3a38"></div>
</form>

[{capture assign="unzerEPSJS"}]

    $( '#orderConfirmAgbBottom' ).submit(function( event ) {
        if(!$( '#orderConfirmAgbBottom' ).hasClass("submitable")){
            event.preventDefault();
            $( '#payment-form' ).submit();
        }
        $( '#orderConfirmAgbBottom .submitButton' ).prop('disabled', true);
    });

    // Create an Unzer instance with your public key
    let unzerInstance = new unzer('[{$unzerpub}]', {locale: "[{$unzerLocale}]"});

    // Create an EPS instance and render the EPS form
    let EPS = unzerInstance.EPS();
    EPS.create('eps', {
        containerId: 'unzer-eps'
    });

    // Handling payment form submission
    $( "#payment-form" ).submit(function( event ) {
        event.preventDefault();
        // Creating a EPS resource
        EPS.createResource()
        .then(function(result) {
            let hiddenInput = $(document.createElement('input'))
            .attr('type', 'hidden')
            .attr('name', 'paymentData')
            .val(JSON.stringify(result));
            $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput);

            $( '#orderConfirmAgbBottom' ).addClass("submitable");
            $( "#orderConfirmAgbBottom" ).submit();
        })
        .catch(function(error) {
            $('#error-holder').html(error.message)
            $('html, body').animate({
            scrollTop: $("#orderPayment").offset().top - 150
            }, 350);
        })
    });
[{/capture}]
[{oxscript add=$unzerEPSJS}]