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
    <div class="field" id="error-holder" style="color: #9f3a38"> </div>
</form>


[{capture assign="unzerCardJS"}]
    var submitBasketForm = document.getElementById("orderConfirmAgbBottom");
    var divHidden = submitBasketForm.querySelector('.hidden');

    let hiddenInputPaymentTypeId = divHidden.querySelector('paymentData');
    hiddenInputPaymentTypeId = document.createElement('input');
    hiddenInputPaymentTypeId.setAttribute('type', 'hidden');
    hiddenInputPaymentTypeId.setAttribute('name', 'paymentData');
    divHidden.appendChild(hiddenInputPaymentTypeId);

    // Create an Unzer instance with your public key
    let unzerInstance = new unzer('[{$unzerpub}]');

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

    let $errorHolder = $('#error-holder');

    $('#orderConfirmAgbBottom').find(".submitButton").click(function(e) {
    e.preventDefault();
    $('#payment-form-card').submit();
    });

    $( "#payment-form-card" ).submit(function( event ) {
    event.preventDefault();
    Card.createResource()
    .then(function(result) {
    hiddenInputPaymentTypeId.setAttribute('value', JSON.stringify(result) );
    $('#orderConfirmAgbBottom').submit();
    })
    .catch(function(error) {
    hiddenInputPaymentTypeId.setAttribute('value', JSON.stringify(error) );
        $('html, body').animate({
        scrollTop: $("#orderPayment").offset().top - 150
        }, 350);
    })
    });

    [{/capture}]
[{oxscript add=$unzerCardJS}]