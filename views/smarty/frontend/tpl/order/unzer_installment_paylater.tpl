[{include file="@osc-unzer/frontend/tpl/order/unzer_assets"}]

[{assign var="invadr" value=$oView->getInvoiceAddress()}]
[{assign var="isCompany" value=false}]

[{if ($oxcmp_user->oxuser__oxcompany->value || ($invadr && $invadr->oxaddress__oxcompany->value))}]
    [{assign var="isCompany" value=true}]
[{/if}]

[{if $isCompany}]
    [{assign var="customerType" value="B2B"}]
[{else}]
    [{assign var="customerType" value="B2C"}]
[{/if}]

<form id="payment-form-installment" class="unzerUI form unzerUI-PaylaterInstallment__form" novalidate>
    <br />
    <div id="unzer-installment">
        <!-- The Installment Secured field UI Element will be inserted here -->
    </div>

    <div class="field" id="error-holder" style="color: #9f3a38"> </div>
    <button id="continue-button" class="unzerUI primary button fluid" type="submit" style="display: none" disabled>
        [{oxmultilang ident="OSCUNZER_INSTALLMENT_CONTINUE"}]
    </button>
</form>

[{assign var="total" value=$oxcmp_basket->getPrice()}]

[{assign var=totalgross value=$oxcmp_basket->getPrice()}]
[{assign var=uzrcurrency value=$currency->name}]

[{capture assign="unzerInstallmentJS"}]

    // Create an Unzer instance with your public key
    let unzerInstance = new unzer('[{$unzerpub}]', {locale: "[{$unzerLocale}]"});

    let InstallmentSecured = unzerInstance.PaylaterInstallment();

    InstallmentSecured.create({
        containerId: 'unzer-installment',
        amount: [{$totalgross->getPrice()}],
        currency: '[{$uzrcurrency}]',
        country:'[{$oView->getUserCountryIso()}]',
        threatMetrixId:'[{$unzerThreatMetrixSessionID}]'

    });

    $( '#orderConfirmAgbBottom' ).submit(function( event ) {
        if(!$( '#orderConfirmAgbBottom' ).hasClass("submitable")){
            event.preventDefault();
            $( '#payment-form-installment' ).submit();
        }
    });

    // Handling payment form submission
    $( "#payment-form-installment" ).submit(function( event ) {
        event.preventDefault();
        //  if($('.unzerUI-installment-secured__selected-rate').length){
        InstallmentSecured.createResource()
            .then(function(data) {
                let hiddenInput = $(document.createElement('input'))
                    .attr('type', 'hidden')
                    .attr('name', 'paymentData')
                    .val(JSON.stringify(data));
                console.log(data);
                $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput);
                let hiddenCustomerType = $(document.createElement('input'))
                    .attr('type', 'hidden')
                    .attr('name', 'unzer_customer_type')
                    .val('[{$customerType}]');
                $('#orderConfirmAgbBottom').find(".hidden").append(hiddenCustomerType);

                $( '#orderConfirmAgbBottom' ).addClass("submitable");
                $( "#orderConfirmAgbBottom" ).submit();
            })
            .catch(function(error) {
                console.log('here1')
                $('#error-holder').html(error.message);
                $('html, body').animate({
                    scrollTop: $("#orderPayment").offset().top - 150
                }, 350);
            });
        //}
    });
[{/capture}]
[{oxscript add=$unzerInstallmentJS}]
