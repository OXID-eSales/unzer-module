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

[{capture assign="unzerCardJS"}]
    <script type="text/javascript">
        [{capture name="javaScript"}]
        // Create an Unzer instance with your public key
        let unzerInstance = new unzer([{$unzerPublicKey}]);

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

        // General event handling
        let formFieldValid = {};
        let payButton = document.getElementById("submit-button");
        let $errorHolder = $('#error-holder');

        // Enable pay button initially
        payButton.disabled = true;

        let eventHandlerCardInput = function(e) {
            if (e.success) {
                formFieldValid[e.type] = true;
                $errorHolder.html('')
            } else {
                formFieldValid[e.type] = false;
                $errorHolder.html(e.error)
            }
            payButton.disabled = !(formFieldValid.number && formFieldValid.expiry && formFieldValid.cvc);
        };

        Card.addEventListener('change', eventHandlerCardInput);

        // Handling the form submission
        let form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            // Creating a Card resource
            Card.createResource()
                .then(function(result) {
                    let hiddenInput = document.createElement('input');
                    hiddenInput.setAttribute('type', 'hidden');
                    hiddenInput.setAttribute('name', 'resourceId');
                    hiddenInput.setAttribute('value', result.id);
                    form.appendChild(hiddenInput);
                    form.setAttribute('method', 'POST');
                    form.setAttribute('action', [{$sClUrl}]);

                    // Submitting the form
                    form.submit();
                })
                .catch(function(error) {
                    $errorHolder.html(error.message);
                })
        });
        [{/capture}]
    </script>
    [{/capture}]
[{oxscript add=$unzerCardJS}]
[{*capture assign="unzerSepaDirectJS"}]
    var submitBasketForm = document.getElementById("orderConfirmAgbBottom");
    var submitButton = submitBasketForm.querySelector('.submitButton');
    var divHidden = submitBasketForm.querySelector('.hidden');

    let hiddenInputFnc = divHidden.querySelector('input[name="fnc"]');
    hiddenInputFnc.setAttribute('value', 'validatePayment');

    let hiddenInputCl = divHidden.querySelector('input[name="cl"]');
    hiddenInputCl.setAttribute('value', 'unzer_dispatcher');

    let hiddenInputPaymentTypeId = divHidden.querySelector('paymentTypeId');
    hiddenInputPaymentTypeId = document.createElement('input');
    hiddenInputPaymentTypeId.setAttribute('type', 'hidden');
    hiddenInputPaymentTypeId.setAttribute('name', 'paymentTypeId');
    divHidden.appendChild(hiddenInputPaymentTypeId);

    hiddenInputOxPaymentId = document.createElement('input');
    hiddenInputOxPaymentId.setAttribute('type', 'hidden');
    hiddenInputOxPaymentId.setAttribute('name', 'paymentid');
    hiddenInputOxPaymentId.setAttribute('value', '[{$payment->getId()}]');
    divHidden.appendChild(hiddenInputOxPaymentId);

    submitButton.disabled = true;
    // Create an Unzer instance with your public key
    let unzerInstance = new unzer('[{$unzerpub}]');

    // Create a SEPA Direct Debit instance and render the form
    let SepaDirectDebit = unzerInstance.SepaDirectDebit();
    SepaDirectDebit.create('sepa-direct-debit', {
    containerId: 'sepa-IBAN'
    });
    var form = document.getElementById('payment-form-sepa');

    form.addEventListener('keyup', function(event) {
    event.preventDefault();
    SepaDirectDebit.createResource()
    .then(function(data) {
    submitButton.disabled = false;
    hiddenInputPaymentTypeId.setAttribute('value', JSON.stringify(data) );

    })
    .catch(function(error) {
    submitButton.disabled = true;
    hiddenInputPaymentTypeId.setAttribute('value', 'validatePayment' );
    })
    });
    [{/capture}]
[{oxscript add=$unzerSepaDirectJS*}]