[{include file="modules/osc/unzer/unzer_assets.tpl"}]

<div id="payment-form">
    <div id="sepa-secured-IBAN" class="field">
        <!-- The IBAN field UI Element will be inserted here -->
    </div>
    <div class="field" id="error-holder" style="color: #9f3a38"></div>
</div>

<div id="payment-sepa-confirm">
    <div class="sepaagreement" id="sepaagree_unzer">
        <input id="oscunzersepaagreement" type="checkbox" name="oscunzersepaagreement" value="1">
        <label for="oscunzersepaagreement">
            [{oxifcontent ident="oscunzersepamandateconfirmation" object="oCont"}]
            [{$oCont->oxcontents__oxcontent->value}]
            [{/oxifcontent}]
        </label>
        [{oxscript add="$('#oscunzersepaagreement').click(function(){ $('input[name=oscunzersepaagreement]').val($(this).is(':checked') ? '1' : '0');});"}]
    </div>
</div>
[{capture assign="unzerSepaDirectSecurredJS"}]

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
    var form = document.getElementById('payment-form');

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
[{oxscript add=$unzerSepaDirectSecurredJS}]