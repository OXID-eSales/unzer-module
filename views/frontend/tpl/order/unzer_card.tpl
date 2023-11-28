[{include file="modules/osc/unzer/unzer_assets.tpl"}]

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
</form>
<style>
    .unzerUI.form .field.error .compact.error.message:not(:empty), .unzerUI.form .fields.error .field .compact.error.message:not(:empty){
        line-height: 100%;
    }
</style>

[{if false}]
<script>
[{/if}]
[{capture assign="unzerCardJS"}]
    $( '#orderConfirmAgbBottom' ).submit(function( event ) {
        if(!$( '#orderConfirmAgbBottom' ).hasClass("submitable")){
            event.preventDefault();
            $( "#payment-form-card" ).submit();
        }
        $( '#orderConfirmAgbBottom .submitButton' ).prop('disabled', true);
    });

    // Create an Unzer instance with your public key
    let unzerInstance = new unzer('[{$unzerpub}]', {locale: "[{$unzerLocale}]"});

    // Create a Card instance and render the input fields
    let Card = unzerInstance.Card();
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

    $( "#payment-form-card" ).submit(function( event ) {
    event.preventDefault();
    Card.createResource()
        .then(function(result) {
            let hiddenInput = $(document.createElement('input'))
            .attr('type', 'hidden')
            .attr('name', 'paymentData')
            .val(JSON.stringify(result));
            $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput);
            $( '#orderConfirmAgbBottom' ).addClass("submitable");
            $('#orderConfirmAgbBottom').submit();
        })
        .catch(function(error) {
            $('html, body').animate({
            scrollTop: $("#orderPayment").offset().top - 150
            }, 350);

            errorField = $("#card-element-id-number");
            errorField.find('div').first().addClass('error');
            errorEl = errorField.find('div.error.message');
            errorEl.css('display', 'inline-block');
            errorEl.text(error.message);

            $( '#orderConfirmAgbBottom .submitButton' ).prop('disabled', false);
        })
    });

    [{/capture}]
[{if false}]
</script>
[{/if}]
[{oxscript add=$unzerCardJS}]