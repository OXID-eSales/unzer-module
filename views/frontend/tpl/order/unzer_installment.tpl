[{block name="unzer_installment_css"}]
    [{oxscript include="https://static.unzer.com/v1/unzer.js"}]
    [{/block}]
[{block name="unzer_installment_css"}]
    [{oxstyle include="https://static.unzer.com/v1/unzer.css"}]
    [{/block}]

<form id="payment-form-installment" class="unzerUI form unzerUI-installmentsecured__form" novalidate>
    <div id="unzer-installment">
        <!-- The Installment Secured field UI Element will be inserted here -->
    </div>
    <div class="field" id="error-holder" style="color: #9f3a38"> </div>
    <button id="continue-button" class="unzerUI primary button fluid" type="submit" style="display: none" disabled>
        Continue
    </button>
</form>
[{assign var=totalgross value=$oxcmp_basket->getBruttoSum()}]
[{assign var=uzrcurrency value='EUR'}]
[{assign var=installrate value=$oViewConf->getUnzerInstallmentRate()}]

[{capture assign="unzerInstallmentJS"}]

        // Create an Unzer instance with your public key
        let unzerInstance = new unzer('[{$unzerpub}]');

        let InstallmentSecured = unzerInstance.InstallmentSecured();

        InstallmentSecured.create({
            containerId: 'unzer-installment',
            amount: [{$totalgross}],
            currency: '[{$uzrcurrency}]',
            effectiveInterest: [{$installrate}]
        });

        $( '#orderConfirmAgbBottom' ).submit(function( event ) {
            if(!$( '#orderConfirmAgbBottom' ).hasClass("submitable")){
                event.preventDefault();
                $( '#payment-form-installment' ).submit();
            }
        });

        // Handling payment form submission
        $( "#payment-form-installment" ).submit(function( event ) {
            event.preventDefault();
            if($('.unzerUI-installment-secured__selected-rate').length){
                InstallmentSecured.createResource()
                .then(function(data) {
                    let hiddenInput = $(document.createElement('input'))
                    .attr('type', 'hidden')
                    .attr('name', 'paymentData')
                    .val(JSON.stringify(data));

                    $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput);
                    $( '#orderConfirmAgbBottom' ).addClass("submitable");
                    $( "#orderConfirmAgbBottom" ).submit();
                })
                .catch(function(error) {
                    $('#error-holder').html(error.message);
                    $('html, body').animate({
                    scrollTop: $("#orderPayment").offset().top - 150
                    }, 350);
                });
            }else{
                $('html, body').animate({
                scrollTop: $("#orderPayment").offset().top - 150
                }, 350);
            }
        });
[{/capture}]
[{oxscript add=$unzerInstallmentJS}]
