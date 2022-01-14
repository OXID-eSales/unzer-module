[{include file="modules/osc/unzer/unzer_assets.tpl"}]

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
[{capture assign="unzerInstallmentJS"}]

        var submitBasketForm = document.getElementById("orderConfirmAgbBottom");
        var divHidden = submitBasketForm.querySelector('.hidden');

        let hiddenInputPaymentTypeId = divHidden.querySelector('paymentData');
        hiddenInputPaymentTypeId = document.createElement('input');
        hiddenInputPaymentTypeId.setAttribute('type', 'hidden');
        hiddenInputPaymentTypeId.setAttribute('name', 'paymentData');
        divHidden.appendChild(hiddenInputPaymentTypeId);

        // Create an Unzer instance with your public key
        let unzerInstance = new unzer('[{$unzerpub}]');

        let InstallmentSecured = unzerInstance.InstallmentSecured();

        InstallmentSecured.create({
            containerId: 'unzer-installment', // required
            amount: [{$totalgross}], // required
            currency: '[{$uzrcurrency}]', // required
            effectiveInterest: 4.5, // required TODO
           // orderDate: '2019-04-18', // optional
        })
            .then(function(data){
                // if successful, notify the user that the list of installments was fetched successfully
                // in case you were using a loading element during the fetching process,
                // you can remove it inside this callback function
            })
            .catch(function(error) {
                // sent an error message to the user (fetching installment list failed)
            });



        let continueButton = document.getElementById('continue-button');

        InstallmentSecured.addEventListener('installmentSecuredEvent', function(e) {
            if (e.action === 'validate') {
                if (e.success) {
                    continueButton.removeAttribute('disabled')
                } else {
                    continueButton.setAttribute('disabled', 'true')
                }
            }

            if (e.action === 'change-step') {
                if (e.currentStep === 'plan-list') {
                    continueButton.setAttribute('style', 'display: none')
                } else {
                    continueButton.setAttribute('style', 'display: block')
                }
            }
        });

        // Handling the form's submission.
        let form = document.getElementById('payment-form-installment');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            InstallmentSecured.createResource()
                .then(function(data) {
                    form.submit();
                })
                .catch(function(error) {
                    $('#error-holder').html(error.message)
                });
        });
    [{/capture}]
[{oxscript add=$unzerInstallmentJS}]
