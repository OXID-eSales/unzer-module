[{include file="modules/osc/unzer/unzer_assets.tpl"}]

<form id="payment-form" class="unzerUI form" novalidate>
    <div id="unzer-ideal" class="field"></div>
    <div class="field" id="error-holder" style="color: #9f3a38"> </div>
</form>

[{capture assign="unzerIDealJS"}]

        $( '#orderConfirmAgbBottom' ).submit(function( event ) {
            if(!$( '#orderConfirmAgbBottom' ).hasClass("submitable")){
                event.preventDefault();
                $( '#payment-form' ).submit();
            }
        });

        // Create an Unzer instance with your public key
        let unzerInstance = new unzer('[{$unzerpub}]', {locale: "[{$unzerLocale}]"});

        // Create an iDeal instance and render the iDeal form
        let IDeal = unzerInstance.Ideal();
        IDeal.create('ideal', {
            containerId: 'unzer-ideal'
        });

        // Handling payment form submission
        $( "#payment-form" ).submit(function( event ) {
            event.preventDefault();
            // Creating a IDeal resource
            IDeal.createResource()
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
[{oxscript add=$unzerIDealJS}]
