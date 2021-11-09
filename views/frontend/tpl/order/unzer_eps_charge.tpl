<form id="payment-form" class="unzerUI form" novalidate>
    <div id="example-eps" class="field"></div>
    <div class="field" id="error-holder" style="color: #9f3a38"> </div>
    <button class="unzerUI primary button fluid" id="submit-button" type="submit">[{oxmultilang ident="PAY"}]</button>
</form>


[{capture assign="unzerEPSJS"}]
    <script type="text/javascript">
        [{capture name="javaScript"}]
        // Create an Unzer instance with your public key
        let unzerInstance = new unzer([{$unzerPublicKey}]);

        // Create an EPS instance and render the EPS form
        let EPS = unzerInstance.EPS();
        EPS.create('eps', {
            containerId: 'example-eps'
        });

        // Handling payment form submission
        let form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            // Creating a EPS resource
            EPS.createResource()
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
                    $('#error-holder').html(error.message)
                })
        });
        [{/capture}]
    </script>
    [{/capture}]
[{oxscript add=$unzerEPSJS}]
