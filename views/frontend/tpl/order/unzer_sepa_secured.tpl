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
    // Create an Unzer instance with your public key
    let unzerInstance = new unzer('[{$unzerpub}]');

    // Create a SEPA Direct Debit Secured instance and render the form
    let SepaDirectDebit = unzerInstance.SepaDirectDebit();
    SepaDirectDebit.create('sepa-direct-debit', {
        containerId: 'sepa-secured-IBAN'
    });

    // Creat a customer instance and render the form
    let Customer = unzerInstance.Customer();
    Customer.create({
        containerId: 'customer'
    });
    let sepaDirectDebitPromise = SepaDirectDebit.createResource();
    let customerPromise = Customer.createCustomer();
    Promise.all([sepaDirectDebitPromise])
    .then(function(values) {
    let paymentType = values[0];

    var submitBasketForm = document.getElementById("orderConfirmAgbBottom");
    var divHidden = submitBasketForm.querySelector('.hidden');

    let hiddenInputPaymentTypeId = document.createElement('input');
    hiddenInputPaymentTypeId.setAttribute('type', 'hidden');
    hiddenInputPaymentTypeId.setAttribute('name', 'paymentTypeId');
    hiddenInputPaymentTypeId.setAttribute('value', paymentType.id);
    divHidden.appendChild(hiddenInputPaymentTypeId);

    })
    .catch(function(error) {
    $('#error-holder').html(error.message)
    });




[{/capture}]
[{oxscript add=$unzerSepaDirectJS}]
