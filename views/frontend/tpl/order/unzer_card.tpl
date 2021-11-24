[{block name="unzer_cardjs"}]
    [{oxscript include="https://static.unzer.com/v1/unzer.js"}]
    [{/block}]
[{block name="unzer_card_css"}]
    [{oxstyle include="https://static.unzer.com/v1/unzer.css"}]
    [{/block}]
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
    <div class="field">
        <button
                id="submit-button"
                class="unzerUI primary button fluid"
                type="submit"
        >
            Pay
        </button>
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

    var form = document.getElementById('payment-form-card');
    form.addEventListener('submit', function(event) {
    event.preventDefault();
    Card.createResource()
    .then(function(result) {
    hiddenInputPaymentTypeId.setAttribute('value', JSON.stringify(result) );
    })
    .catch(function(error) {
    hiddenInputPaymentTypeId.setAttribute('value', JSON.stringify(error) );
    })
    });

    [{/capture}]
[{oxscript add=$unzerCardJS}]