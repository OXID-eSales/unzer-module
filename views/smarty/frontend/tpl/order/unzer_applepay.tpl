[{include file="@osc-unzer/frontend/tpl/order/unzer_assets.tpl"}]

[{capture assign="unzerApplePayJS"}]
    [{if false }]<script>[{/if}]

    const unzerInstance = new unzer('[{$unzerpub}]');
    const unzerApplePayInstance = unzerInstance.ApplePay();
    const form = document.getElementById('orderConfirmAgbBottom');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        setupApplePaySession();
    });

    function startApplePaySession(applePayPaymentRequest) {
        if (window.ApplePaySession && ApplePaySession.canMakePayments()) {
            const session = new ApplePaySession(6, applePayPaymentRequest);

            session.onvalidatemerchant = function (event) {
                merchantValidationCallback(session, event);
            };

            session.onpaymentauthorized = function (event) {
                applePayAuthorizedCallback(event, session);
            };

            session.oncancel = function (event) {
                onCancelCallback(event);
            };
            session.begin();
        } else {
            console.error("Apple Pay is not supported on this device or browser.");
            handleError({ message: 'This device does not support Apple Pay!', error: new Error('Apple Pay not supported') });
        }
    }

    function applePayAuthorizedCallback(event, session) {
        let shopSelfUrlEscaped = '[{$oViewConf->getSelfActionLink()}]';
        let shopSelfUrl = shopSelfUrlEscaped.replace(/&amp;/g, '&');
        try {
            const paymentData = event.payment.token.paymentData;
            unzerApplePayInstance.createResource(paymentData)
                .then(function (result) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'paymentData';
                    hiddenInput.value = JSON.stringify(result);
                    form.appendChild(hiddenInput);
                    const formData = new FormData(form);
                    fetch(shopSelfUrl, {
                        method: 'POST',
                        mode: 'no-cors',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Fetch failed: ${response.statusText}`);
                        }
                        return response.text();
                    })
                    .then(responseText => {
                        session.completePayment({status: window.ApplePaySession.STATUS_SUCCESS});
                        window.location.href = shopSelfUrl + '&cl=thankyou';
                    })
                    .catch(error => {
                        console.error("Error during fetch operation:", error);
                        handleError({message: error.message});
                        abortPaymentSession(session);
                        window.location.href = shopSelfUrl + '&cl=payment&payerror=2';
                    });
                })
                .catch(error => {
                    console.error("Error creating Apple Pay resource:", error);
                    handleError({message: error.message});
                    abortPaymentSession(session);
                });
        } catch (error) {
            console.error("Unhandled exception in Apple Pay callback:", error);
            handleError({message: error.message});
            abortPaymentSession(session);
        }
    }

    function merchantValidationCallback(session, event) {
        let shopSelfUrlEscaped = '[{$oViewConf->getSelfActionLink()}]';
        let shopSelfUrl = shopSelfUrlEscaped.replace(/&amp;/g, '&');

        let formData = new FormData();
        formData.append('cl', 'unzer_applepay_callback');
        formData.append('fnc', 'validateMerchant');
        formData.append('merchantValidationUrl', event.validationURL);
        formData.append('stoken', '[{$oViewConf->getSessionChallengeToken()}]');

        fetch(shopSelfUrl, {
            method: 'POST',
            mode: 'no-cors',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error("HTTP error: " + response.statusText);
            }
            return response.json();
        })
        .then(function (data) {
            if (data.validationResponse && data.validationResponse.merchantSessionIdentifier) {
                session.completeMerchantValidation(data.validationResponse);
            } else {
                throw new Error("Invalid validation response: Missing required fields (merchantSessionIdentifier).");
            }
        })
        .catch(function (error) {
            handleError({ message: error.message, error });
            session.abort();
        });
    }

    function onCancelCallback(event) {
        handleError({ message: 'Payment process canceled by user.' });
    }

    [{assign var="currency" value=$oView->getActCurrency()}]
    [{assign var="total" value=$oxcmp_basket->getPrice()}]
    [{assign var="deliveryCost" value=$oxcmp_basket->getDeliveryCost()}]

    function setupApplePaySession() {
        const applePayPaymentRequest = {
            countryCode: '[{$oView->getUserCountryIso()}]',
            currencyCode: '[{$currency->name}]',
            total: {
                label: '[{$oView->getApplePayLabel()}]',
                amount: [{$total->getPrice()}]
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
            requiredShippingContactFields: [],
            requiredBillingContactFields: [],
            lineItems: [
                    [{if !$oxcmp_basket->getDiscounts()}]
                        {
                            label: '[{oxmultilang ident="TOTAL_NET"}]',
                            type: 'final',
                            amount: [{$oxcmp_basket->getNettoSum()}]
                        },
                        [{foreach from=$oxcmp_basket->getProductVats(false) item=vat key=key}]
                            {
                                label: '[{oxmultilang ident="VAT_PLUS_PERCENT_AMOUNT" args=$key}]',
                                type: 'final',
                                amount: [{$vat}]
                            },
                        [{/foreach}]
                        {
                            label: '[{oxmultilang ident="TOTAL_GROSS" args=$key}]',
                            type: 'final',
                            amount: [{$oxcmp_basket->getBruttoSum()}]
                        },
                    [{else}]
                        [{if $oxcmp_basket->isPriceViewModeNetto()}]
                            {
                                label: '[{oxmultilang ident="TOTAL_NET"}]',
                                type: 'final',
                                amount: [{$oxcmp_basket->getNettoSum()}]
                            },
                        [{else}]
                            {
                                label: '[{oxmultilang ident="TOTAL_GROSS"}]',
                                type: 'final',
                                amount: [{$oxcmp_basket->getBruttoSum()}]
                            },
                        [{/if}]
                        [{assign var="discounts" value=$oxcmp_basket->getDiscounts()}]
                        [{foreach from=discounts item="oDiscount"}]
                            [{assign var="discount" value=$oDiscount->dDiscount*-1}]
                            {
                                label: '[{$oDiscount->sDiscount}]',
                                type: 'final',
                                amount: [{$discount}]
                            },
                        [{/foreach}]
                        [{if !$oxcmp_basket->isPriceViewModeNetto()}]
                            {
                                label: '[{oxmultilang ident="TOTAL_NET"}]',
                                type: 'final',
                                amount: [{$oxcmp_basket->getNettoSum()}]
                            },
                        [{/if}]
                        [{foreach from=$oxcmp_basket->getProductVats(false) item=vat key=key}]
                            {
                                label: '[{oxmultilang ident="VAT_PLUS_PERCENT_AMOUNT" args=$key}]',
                                type: 'final',
                                amount: [{$vat}]
                            },
                        [{/foreach}]
                        [{if $oxcmp_basket->isPriceViewModeNetto()}]
                            {
                                label: '[{oxmultilang ident="TOTAL_GROSS" args=$key}]',
                                type: 'final',
                                amount: [{$oxcmp_basket->getBruttoSum()}]
                            },
                        [{/if}]
                    [{/if}]

                    [{if $oViewConf->getShowVouchers() && $oxcmp_basket->getVoucherDiscValue()}]
                        [{foreach from=$oxcmp_basket->getVouchers() item="oVoucher"}]
                            [{assign var="voucherDiscount" value=$oVoucher->dVoucherdiscount*-1}]
                            {
                                label: '[{oxmultilang ident="COUPON"}] ([{oxmultilang ident="NUMBER"}] [{$oVoucher->sVoucherNr}])',
                                type: 'final',
                                amount: [{$voucherDiscount}]
                            },
                        [{/foreach}]
                    [{/if}]

                    [{if $deliveryCost && ($oxcmp_basket->getBasketUser() || $oViewConf->isFunctionalityEnabled('blCalculateDelCostIfNotLoggedIn'))}]
                        [{if $oViewConf->isFunctionalityEnabled('blShowVATForDelivery') }]
                            [{assign var="dShippingVatValue" value=$deliveryCost->getVatValue()}]
                            {
                                label: '[{oxmultilang ident="SHIPPING_NET"}]',
                                type: 'final',
                                amount: [{$deliveryCost->getNettoPrice()}]
                            },
                            [{if $dShippingVatValue}]
                                {
                                    label: '[{if $oxcmp_basket->isProportionalCalculationOn()}][{oxmultilang ident="BASKET_TOTAL_PLUS_PROPORTIONAL_VAT" suffix="COLON"}][{else}][{oxmultilang ident="VAT_PLUS_PERCENT_AMOUNT" args=$deliveryCost->getVat()}][{/if}]',
                                    type: 'final',
                                    amount: [{$dShippingVatValue}]
                                },
                            [{/if}]
                        [{else}]
                            {
                                label: '[{oxmultilang ident="SHIPPING_COST"}]',
                                type: 'final',
                                amount: [{$deliveryCost->getBruttoPrice()}]
                            },
                        [{/if}]
                    [{/if}]

                    [{assign var="paymentCost" value=$oxcmp_basket->getPaymentCost()}]
                    [{if $paymentCost && $paymentCost->getPrice()}]
                        [{if $oViewConf->isFunctionalityEnabled('blShowVATForPayCharge')}]
                            {
                                label: '[{if $paymentCost->getPrice() >= 0}][{ oxmultilang ident="SURCHARGE" }][{else}][{ oxmultilang ident="DEDUCTION" }][{oxmultilang ident="PAYMENT_METHOD"}][{/if}]',
                                type: 'final',
                                amount: [{$paymentCost->getNettoPrice()}]
                            },
                            [{if $paymentCost->getVatValue()}]
                                {
                                    label: '[{if $oxcmp_basket->isProportionalCalculationOn()}][{oxmultilang ident="BASKET_TOTAL_PLUS_PROPORTIONAL_VAT"}][{else}][{oxmultilang ident="SURCHARGE_PLUS_PERCENT_AMOUNT" args=$paymentCost->getVat()}][{/if}]',
                                    type: 'final',
                                    amount: [{$paymentCost->getVatValue()}]
                                },
                            [{/if}]
                        [{else}]
                            {
                                label: '[{if $paymentCost->getPrice() >= 0}][{ oxmultilang ident="SURCHARGE" }][{else}][{ oxmultilang ident="DEDUCTION" }][{oxmultilang ident="PAYMENT_METHOD"}][{/if}]',
                                type: 'final',
                                amount: [{$paymentCost->getBruttoPrice()}]
                            },
                        [{/if}]
                    [{/if}]
                    [{if $oViewConf->getShowGiftWrapping()}]
                        [{assign var="wrappingCost" value=$oxcmp_basket->getWrappingCost()}]
                        [{if $wrappingCost && $wrappingCost->getPrice() > 0}]
                            [{if $oViewConf->isFunctionalityEnabled('blShowVATForWrapping')}]
                                {
                                    label: '[{oxmultilang ident="BASKET_TOTAL_WRAPPING_COSTS_NET"}]',
                                    type: 'final',
                                    amount: [{$wrappingCost->getNettoPrice()}]
                                },
                                [{if $oxcmp_basket->getWrappCostVat()}]
                                    {
                                        label: '[{oxmultilang ident="PLUS_VAT"}]',
                                        type: 'final',
                                        amount: [{$wrappingCost->getVatValue()}]
                                    },
                                [{/if}]
                            [{else}]
                                {
                                    label: '[{oxmultilang ident="GIFT_WRAPPING"}]',
                                    type: 'final',
                                    amount: [{$wrappingCost->getBruttoPrice()}]
                                },
                            [{/if}]
                        [{/if}]
                        [{assign var="giftCardCost" value=$oxcmp_basket->getGiftCardCost()}]
                        [{if $giftCardCost && $giftCardCost->getPrice() > 0 }]
                            [{if $oViewConf->isFunctionalityEnabled('blShowVATForWrapping') }]
                                {
                                    label: '[{oxmultilang ident="BASKET_TOTAL_GIFTCARD_COSTS_NET"}]',
                                    type: 'final',
                                    amount: [{$giftCardCost->getNettoPrice()}]
                                },
                                {
                                    label: '[{if $oxcmp_basket->isProportionalCalculationOn()}][{oxmultilang ident="BASKET_TOTAL_PLUS_PROPORTIONAL_VAT"}][{else}][{oxmultilang ident="VAT_PLUS_PERCENT_AMOUNT" args=$giftCardCost->getVat()}][{/if}]',
                                    type: 'final',
                                    amount: [{$giftCardCost->getVatValue()}]
                                },
                            [{else}]
                                {
                                    label: '[{oxmultilang ident="GREETING_CARD"}]',
                                    type: 'final',
                                    amount: [{$giftCardCost->getBruttoPrice()}]
                                }
                            [{/if}]
                        [{/if}]
                    [{/if}]
                ]

        };
        startApplePaySession(applePayPaymentRequest);
    }

    function handleError({ html = '[{oxmultilang ident="oscunzer_APPLEPAY_ERROR"}]', message, error } = {}) {
        console.error("Error occurred:", message, error);

        const errorElement = document.querySelector('.js-unzer-error-holder');
        if (errorElement) {
            errorElement.innerHTML = html;
            errorElement.style.display = 'block';
            errorElement.focus();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function abortPaymentSession(session) {
        session.completePayment({ status: window.ApplePaySession.STATUS_FAILURE });
        session.abort();
    }

[{if false}]</script>[{/if}]
    [{/capture}]

[{oxscript add=$unzerApplePayJS}]
