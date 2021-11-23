[{block name="unzer_sepajs"}]
    [{oxscript include="https://static.unzer.com/v1/unzer.js"}]
    [{oxstyle include="https://static.unzer.com/v1/unzer.css"}]
[{/block}]

<div id="payment-form">
    <div id="sepa-secured-IBAN" class="field">
        <!-- The IBAN field UI Element will be inserted here -->
    </div>
    <div class="field" id="error-holder" style="color: #9f3a38"></div>
</div>

[{capture assign="unzerSepaDirectJS"}]

    var submitBasketForm = document.getElementById("orderConfirmAgbBottom");
    var divHidden = submitBasketForm.querySelector('.hidden');

    let hiddenInputPaymentTypeId = divHidden.querySelector('paymentData');
    hiddenInputPaymentTypeId = document.createElement('input');
    hiddenInputPaymentTypeId.setAttribute('type', 'hidden');
    hiddenInputPaymentTypeId.setAttribute('name', 'paymentData');
    divHidden.appendChild(hiddenInputPaymentTypeId);

    let hiddenSepaConf = divHidden.querySelector('sepaConfirmation');
    hiddenSepaConf = document.createElement('input');
    hiddenSepaConf.setAttribute('type', 'hidden');
    hiddenSepaConf.setAttribute('name', 'sepaConfirmation');
    divHidden.appendChild(hiddenSepaConf);


    let sepaMandateCheckbox = document.getElementById("oscunzersepaagreement");
    sepaMandateCheckbox.addEventListener('change', (event) => {
    if (event.currentTarget.checked) {
    hiddenSepaConf.setAttribute('value', '1');
    } else {
    hiddenSepaConf.setAttribute('value', '0');
    }
    });

    // Create an Unzer instance with your public key
    let unzerInstance = new unzer('[{$unzerpub}]');

    // Create a SEPA Direct Debit Secured instance and render the form
    let SepaDirectDebitSecured = unzerInstance.SepaDirectDebitSecured();
    SepaDirectDebitSecured.create('sepa-direct-debit-secured', {
    containerId: 'sepa-secured-IBAN'
    });
    var form = document.getElementById('payment-form-sepa');

    form.addEventListener('keyup', function(event) {
    event.preventDefault();
    SepaDirectDebitSecured.createResource()
    .then(function(data) {
    hiddenInputPaymentTypeId.setAttribute('value', JSON.stringify(data) );
    })
    .catch(function(error) {
    hiddenInputPaymentTypeId.setAttribute('value', 'validatePayment' );
    })
    });
    [{/capture}]
[{oxscript add=$unzerSepaDirectJS}]