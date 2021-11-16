[{block name="unzer_sepajs"}]
    [{oxscript include="https://static.unzer.com/v1/unzer.js"}]

[{/block}]
[{block name="unzer_sepa_css"}]
    [{oxstyle include="https://static.unzer.com/v1/unzer.css"}]
[{/block}]

<div id="payment-form-sepa">
    <div id="sepa-IBAN" class="field">
        <!-- The IBAN field UI Element will be inserted here -->
    </div>
    <div class="field" id="error-holder" style="color: #9f3a38"></div>
</div>

[{capture assign="unzerSepaDirectJS"}]
    var submitBasketForm = document.getElementById("orderConfirmAgbBottom");
    var divHidden = submitBasketForm.querySelector('.hidden');

    let hiddenInputPaymentTypeId = divHidden.querySelector('paymentTypeId');
    hiddenInputPaymentTypeId = document.createElement('input');
    hiddenInputPaymentTypeId.setAttribute('type', 'hidden');
    hiddenInputPaymentTypeId.setAttribute('name', 'paymentData');
    divHidden.appendChild(hiddenInputPaymentTypeId);

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
    hiddenInputPaymentTypeId.setAttribute('value', JSON.stringify(data) );
    })
    .catch(function(error) {
    hiddenInputPaymentTypeId.setAttribute('value', 'validatePayment' );
    })
    });
[{/capture}]
[{oxscript add=$unzerSepaDirectJS}]
