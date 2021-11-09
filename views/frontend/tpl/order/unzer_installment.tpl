<form id="payment-form-installmentsecured" class="unzerUI form unzerUI-installmentsecured__form" novalidate>
    <div id="example-installment-secured">
        <!-- The Installment Secured field UI Element will be inserted here -->
    </div>
    <div class="field" id="error-holder" style="color: #9f3a38"> </div>
    <button id="continue-button" class="unzerUI primary button fluid" type="submit" style="display: none" disabled>
        Continue
    </button>
</form>

[{capture assign="unzerInstallmentJS"}]
    <script type="text/javascript">
        [{capture name="javaScript"}]
        // Create an Unzer instance with your public key
        let unzerInstance = new unzer('<?php echo UNZER_PAPI_PUBLIC_KEY; ?>');

        let InstallmentSecured = unzerInstance.InstallmentSecured();

        InstallmentSecured.create({
            containerId: 'example-installment-secured', // required
            amount: 119.0, // required
            currency: 'EUR', // required
            effectiveInterest: 4.5, // required
            orderDate: '2019-04-18', // optional
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
        let form = document.getElementById('payment-form-installmentsecured');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            InstallmentSecured.createResource()
                .then(function(data) {
                    let hiddenInput = document.createElement('input');
                    hiddenInput.setAttribute('type', 'hidden');
                    hiddenInput.setAttribute('name', 'paymentTypeId');
                    hiddenInput.setAttribute('value', data.id);
                    form.appendChild(hiddenInput);
                    form.setAttribute('method', 'POST');
                    form.setAttribute('action', '<?php echo CONTROLLER_URL; ?>');

                    form.submit();
                })
                .catch(function(error) {
                    $('#error-holder').html(error.message)
                });
        });
        [{/capture}]
    </script>
    [{/capture}]
[{oxscript add=$unzerInstallmentJS}]
