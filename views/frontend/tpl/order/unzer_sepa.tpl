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
<div id="payment-form-sepa-confirm">
    <input id="oscunzersepaagreement" type="checkbox" name="oscunzersepaagreement" value="1">
    [{oxscript add="$('#oscunzersepaagreement').click(function(){ $('input[name=oscunzersepaagreement]').val($(this).is(':checked') ? '1' : '0');});"}]
    <a href="javascript:void(0)" data-toggle="modal" data-target="#sepaconfirmmodal">
        Sepa-Einzug bestätigen
    </a>
</div>

<div class="modal fade" id="sepaconfirmmodal" role="dialog">
    <div class="modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <span class="h4 modal-title" id="sepa-confirm-modallabel">
                    Sepa-Bestätigung
                </span>
            </div>
            <div class="modal-body">
                [{assign var=sMerchantName value=$oView->getShopCompanyName()}]
                [{oxifcontent ident="oscunzersepamandatetext" object="oContent"}]
                [{$oContent->oxcontents__oxcontent->value}]
                [{/oxifcontent}]
            </div>
        </div>
    </div>
</div>

[{capture assign="unzerSepaDirectJS"}]
    var span = document.getElementsByClassName("close")[0];
    var modalbackdrop = document.getElementsByClassName("modal-backdrop") ;

    var modal = document.getElementById("sepaconfirmmodal");
    span.onclick = function() {
    modal.style.display = "none";
    modalbackdrop.style.display = "none";
    }
    window.onclick = function(event) {
    if (event.target == modal) {
    modal.style.display = "none";
    modalbackdrop.style.display = "none";
    }
    }
    var submitBasketForm = document.getElementById("orderConfirmAgbBottom");
    var divHidden = submitBasketForm.querySelector('.hidden');

    let hiddenInputPaymentTypeId = divHidden.querySelector('paymentTypeId');
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
