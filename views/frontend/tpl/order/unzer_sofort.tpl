<form id="payment-form" class="unzerUI form" novalidate>
    <div class="field" id="error-holder" style="color: #9f3a38"> </div>
    <button class="unzerUI primary button fluid" id="submit-button" type="submit">[{oxmultilang ident="PAY"}]</button>
</form>

[{include file="modules/osc/unzer/unzer_alipay.tpl"}]
[{capture assign="unzerSofortJS"}]
    <script type="text/javascript">
        [{capture name="javaScript"}]
        // Create an Unzer instance with your public key
        let unzerInstance = new unzer([{$unzerPublicKey}]);

        // Create an Sofort instance
        let Sofort = unzerInstance.Sofort();

        // Handle payment form submission
        let form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            // Creating a Sofort resource
            Sofort.createResource()
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
[{oxscript add=$unzerSofortJS}]
