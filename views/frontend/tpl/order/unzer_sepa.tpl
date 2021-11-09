<form id="payment-form">
    <div id="sepa-secured-IBAN" class="field">
        <!-- The IBAN field UI Element will be inserted here -->
    </div>
    <div id="customer" class="field">
        <!-- The customer form UI element will be inserted here -->
    </div>
    <div class="field" id="error-holder" style="color: #9f3a38"></div>
    <button class="unzerUI primary button fluid" id="submit-button" type="submit">[{oxmultilang ident="PAY"}]</button>
</form>


[{capture assign="unzerSepaDirectJS"}]
    <script type="text/javascript">
        [{capture name="javaScript"}]
        // Create an Unzer instance with your public key
        let unzerInstance = new unzer([{$unzerPublicKey}]);

        // Create a SEPA Direct Debit Secured instance and render the form
        let SepaDirectDebitSecured = unzerInstance.SepaDirectDebitSecured();
        SepaDirectDebitSecured.create('sepa-direct-debit-secured', {
            containerId: 'sepa-secured-IBAN'
        });

        // Creat a customer instance and render the form
        let Customer = unzerInstance.Customer();
        Customer.create({
            containerId: 'customer'
        });

        // Handle payment form submission.
        let form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            let sepaDirectDebitSecuredPromise = SepaDirectDebitSecured.createResource();
            let customerPromise = Customer.createCustomer();
            Promise.all([sepaDirectDebitSecuredPromise, customerPromise])
                .then(function(values) {
                    let paymentType = values[0];
                    let customer = values[1];
                    let hiddenInputPaymentTypeId = document.createElement('input');
                    hiddenInputPaymentTypeId.setAttribute('type', 'hidden');
                    hiddenInputPaymentTypeId.setAttribute('name', 'paymentTypeId');
                    hiddenInputPaymentTypeId.setAttribute('value', paymentType.id);
                    form.appendChild(hiddenInputPaymentTypeId);

                    let hiddenInputCustomerId = document.createElement('input');
                    hiddenInputCustomerId.setAttribute('type', 'hidden');
                    hiddenInputCustomerId.setAttribute('name', 'customerId');
                    hiddenInputCustomerId.setAttribute('value', customer.id);
                    form.appendChild(hiddenInputCustomerId);

                    form.setAttribute('method', 'POST');
                    form.setAttribute('action', [{$sClUrl}]);

                    // Submitting the form
                    form.submit();
                })
                .catch(function(error) {
                    $('#error-holder').html(error.message)
                })
        });
        [{/capture}]
    </script>
    [{/capture}]
[{oxscript add=$unzerSepaDirectJS}]
