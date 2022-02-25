[{include file="modules/osc/unzer/unzer_assets.tpl"}]

[{* TODO remove script tag. Only for programming *}]
[{if false}]
<script>
    [{/if}]
    [{capture assign="unzerApplePayJS"}]
    [{*    [{strip}]*}]
    var $errorHolder = $('#error-holder');

    var unzerInstance = new unzer('[{$unzerpub}]');
    var unzerApplePayInstance = unzerInstance.ApplePay();

    $('#orderConfirmAgbBottom').submit(function (e) {
        e.preventDefault();
        setupApplePaySession();
    });

    function startApplePaySession(applePayPaymentRequest) {
        if (window.ApplePaySession && ApplePaySession.canMakePayments()) {
            var session = new ApplePaySession(6, applePayPaymentRequest);
            session.onvalidatemerchant = function (event) {
                console.log('geht rin 1')
                merchantValidationCallback(session, event);
            };

            session.onpaymentauthorized = function (event) {
                console.log('geht rin 2')
                applePayAuthorizedCallback(event, session);
            };

            session.oncancel = function (event) {
                console.log('geht rin 3')
                onCancelCallback(event);
            };

            console.log('geht rin 4')
            session.begin();
        } else {
            handleError('This device does not support Apple Pay!');
        }
    }

    function applePayAuthorizedCallback(event, session) {
        /* Get payment data from event. "event.payment" also contains contact information, if they were set via Apple Pay. */
        var paymentData = event.payment.token.paymentData;
        var $form = $('form[id="payment-form"]');
        var formObject = QueryStringToObject($form.serialize());

        /* Create an Unzer instance with your public key */
        unzerApplePayInstance.createResource(paymentData)
            .then(function (createdResource) {
                formObject.typeId = createdResource.id;
                /* Hand over the type ID to your backend. */
                $.post('./Controller.php', JSON.stringify(formObject), null, 'json')
                    .done(function (result) {
                        /* Handle the transaction respone from backend. */
                        var status = result.transactionStatus;
                        if (status === 'success' || status === 'pending') {
                            session.completePayment({status: window.ApplePaySession.STATUS_SUCCESS});
                            window.location.href = '<?php echo RETURN_CONTROLLER_URL; ?>';
                        } else {
                            window.location.href = '<?php echo FAILURE_URL; ?>';
                            abortPaymentSession(session);
                            session.abort();
                        }
                    })
                    .fail(function (error) {
                        handleError(error.statusText);
                        abortPaymentSession(session);
                    });
            })
            .catch(function (error) {
                handleError(error.message);
                abortPaymentSession(session);
            });
    }

    function merchantValidationCallback(session, event) {
        $.post('[{$oViewConf->getSelfActionLink()}]', {
            cl: 'unzer_applepay_callback',
            fnc: 'validateMerchant',
            merchantValidationUrl: event.validationURL
        }).done(function (validationResponse) {
            try {
                session.completeMerchantValidation(validationResponse);
            } catch (e) {
                alert(e.message);
            }

        })
            .fail(function (error) {
                handleError(JSON.stringify(error.statusText));
                session.abort();
            });
    }

    function onCancelCallback(event) {
        handleError('Canceled by user');
    }

    [{assign var="currency" value=$oView->getActCurrency()}]
    [{assign var="total" value=$oxcmp_basket->getPrice()}]
    [{assign var="deliveryCost" value=$oxcmp_basket->getDeliveryCost()}]

    function setupApplePaySession() {
        var applePayPaymentRequest = {
            countryCode: '[{$oViewConf->getActLanguageAbbr()|upper}]',
            currencyCode: '[{$currency->name}]',
            total: {
                label: '[{$oView->getApplePayLabel()}]',
                amount: [{$total->getPrice()|number_format:2}]
            },
            merchantCapabilities: [
                'supports3DS',
                [{foreach from=$oView->getSupportedApplePayMerchantCapabilities() item="capability" name="applePayMerchantCapabilities"}]
                '[{$capability}]'[{if !$smarty.foreach.applePayMerchantCapabilities.last}],[{/if}]
                [{/foreach}]
            ],
            supportedNetworks: [
                [{foreach from=$oView->getSupportedApplePayNetworks() item="network" name="applePayNetworks"}]
                '[{$network}]'[{if !$smarty.foreach.applePayNetworks.last}],[{/if}]
                [{/foreach}]
            ],
            requiredShippingContactFields: [
                [{foreach from=$oView->getRequiredApplePayShippingFields() item="field" name="applePayRequiredShippingFields"}]
                '[{$field}]'[{if !$smarty.foreach.applePayRequiredShippingFields.last}],[{/if}]
                [{/foreach}]
            ],
            requiredBillingContactFields: [
                [{foreach from=$oView->getRequiredApplePayBillingFields() item="field" name="applePayRequiredBillingFields"}]
                '[{$field}]'[{if !$smarty.foreach.applePayRequiredBillingFields.last}],[{/if}]
                [{/foreach}]
            ],
            lineItems: [
                [{foreach from=$oxcmp_basket->getDiscounts() item="oDiscount"}]
                [{assign var="discount" value=$oDiscount->dDiscount*-1}]
                {
                    'label': '[{$oDiscount->sDiscount}]',
                    'type': 'final',
                    'amount': [{$discount|number_format:2}]
                },
                [{/foreach}]
                [{if $oViewConf->getShowVouchers() && $oxcmp_basket->getVoucherDiscValue()}]
                [{foreach from=$oxcmp_basket->getVouchers() item="oVoucher"}]
                [{assign var="voucherDiscount" value=$oVoucher->dVoucherdiscount*-1}]
                {
                    'label': '[{oxmultilang ident="COUPON"}] ([{oxmultilang ident="NUMBER"}] [{$oVoucher->sVoucherNr}])',
                    'type': 'final',
                    'amount': [{$voucherDiscount|number_format:2}]
                },
                [{/foreach}]
                [{/if}]
                [{if $deliveryCost && ($oxcmp_basket->getBasketUser() || $oViewConf->isFunctionalityEnabled('blCalculateDelCostIfNotLoggedIn'))}]
                [{if $oViewConf->isFunctionalityEnabled('blShowVATForDelivery') }]
                [{assign var="dShippingVatValue" value=$deliveryCost->getVatValue()}]
                {
                    'label': '[{oxmultilang ident="SHIPPING_NET"}]',
                    'type': 'final',
                    'amount': [{$deliveryCost->getNettoPrice()|number_format:2}]
                },
                [{if $dShippingVatValue}]
                {
                    'label': '[{if $oxcmp_basket->isProportionalCalculationOn()}][{oxmultilang ident="BASKET_TOTAL_PLUS_PROPORTIONAL_VAT" suffix="COLON"}][{else}][{oxmultilang ident="VAT_PLUS_PERCENT_AMOUNT" args=$deliveryCost->getVat()}][{/if}]',
                    'type': 'final',
                    'amount': [{$dShippingVatValue|number_format:2}]
                },
                [{/if}]
                [{else}]
                {
                    'label': '[{oxmultilang ident="SHIPPING_COST"}]',
                    'type': 'final',
                    'amount': [{$deliveryCost->getBruttoPrice()|number_format:2}]
                },
                [{/if}]
                [{/if}]
            ]
        };

        startApplePaySession(applePayPaymentRequest);
    }

    /* Updates the error holder with the given message. */
    function handleError(message) {
        $errorHolder.html(message);
    }

    /* Translates query string to object */
    function QueryStringToObject(queryString) {
        var pairs = queryString.slice().split('&');
        var result = {};

        pairs.forEach(function (pair) {
            pair = pair.split('=');
            result[pair[0]] = decodeURIComponent(pair[1] || '');
        });
        return JSON.parse(JSON.stringify(result));
    }

    /* abort current payment session. */
    function abortPaymentSession(session) {
        session.completePayment({status: window.ApplePaySession.STATUS_FAILURE});
        session.abort();
    }
    [{*    [{/strip}]*}]
    [{/capture}]

    [{if false}]
</script>
[{/if}]
[{oxscript add=$unzerApplePayJS}]