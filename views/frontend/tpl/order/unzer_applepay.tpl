[{include file="modules/osc/unzer/unzer_assets.tpl"}]

    [{capture assign="unzerApplePayJS"}]
        const $errorHolder = $('#error-holder');

        const unzerInstance = new unzer('[{$unzerpub}]', {locale: "[{$unzerLocale}]"});
        const unzerApplePayInstance = unzerInstance.ApplePay();
        const $form = $('#orderConfirmAgbBottom');

        $form.submit(function (e) {
            e.preventDefault();

            let needToConfirm = false;
            let confirmMessage = '';

            const agbCheck = $('[name=ord_agb]:checkbox');
            const intabgileCheck = $('[name=oxdownloadableproductsagreement]:checkbox');
            const serviceCheck = $('[name=oxserviceproductsagreement]:checkbox');
            if (agbCheck.length > 0 && !agbCheck.is(':checked')) {
                needToConfirm = true;
                confirmMessage = {html: '[{oxmultilang ident= 'READ_AND_CONFIRM_TERMS'}]'};
            }
            else if (intabgileCheck.length > 0 && !intabgileCheck.is(':checked')) {
                needToConfirm = true;
                confirmMessage = {html: '[{oxmultilang ident= 'OSCUNZER_MISSING_INTAGIBLE_CONFIRMATION_MESSAGE'}]'};
            }
            else if (serviceCheck.length > 0 && !serviceCheck.is(':checked')) {
                needToConfirm = true;
                confirmMessage = {html: '[{oxmultilang ident= 'OSCUNZER_MISSING_SERVICEAGREEMENT_CONFIRMATION_MESSAGE'}]'};
            }

            if (needToConfirm) {
                handleError(confirmMessage);
                return;
            }
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

                session.oncancel = onCancelCallback

                session.begin();
            } else {
                handleError({message: 'This device does not support Apple Pay!'});
            }
        }

        function applePayAuthorizedCallback(event, session) {
            const paymentData = event.payment.token.paymentData;

            unzerApplePayInstance.createResource(paymentData)
                .then(function (result) {
                    const hiddenInput = $(document.createElement('input'))
                        .attr('type', 'hidden')
                        .attr('name', 'paymentData')
                        .val(JSON.stringify(result));

                    $form.append(hiddenInput);

                    $.post('[{$oViewConf->getSelfActionLink()}]', $form.serialize()).done(function (data) {
                        session.completePayment({status: window.ApplePaySession.STATUS_SUCCESS});
                        window.location.href = '[{$oViewConf->getSelfLink()}]&cl=thankyou';
                    }).fail(function (error) {
                        handleError({message: error.statusText});
                        abortPaymentSession(session);
                        window.location.href = '[{$oViewConf->getSelfLink()}]&cl=payment&payerror=2'
                    });
                })
                .catch(function (error) {
                    const {message} = error;
                    handleError({message});
                    abortPaymentSession(session);
                });
        }

        function merchantValidationCallback(session, event) {
            $.post('[{$oViewConf->getSelfActionLink()}]', {
                cl: 'unzer_applepay_callback',
                fnc: 'validateMerchant',
                merchantValidationUrl: event.validationURL
            }).done(function (data) {
                try {
                    session.completeMerchantValidation(data.validationResponse);
                } catch (e) {
                    handleError({message: e.message});
                }

            }).fail(function (error) {
                handleError({message: JSON.stringify(error.statusText)});
                session.abort();
            });
        }

        function onCancelCallback() {
            handleError({message: 'Canceled by user'});
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
                        [{foreach from=$oxcmp_basket->getDiscounts() item="oDiscount"}]
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
                                },
                            [{/if}]
                        [{/if}]
                    [{/if}]
                ]
            };

            startApplePaySession(applePayPaymentRequest);
        }

        function handleError({html = '[{oxmultilang ident="oscunzer_APPLEPAY_ERROR"}]', message} = {html: '[{oxmultilang ident="oscunzer_APPLEPAY_ERROR"}]'}) {
            [{if $oViewConf->isUnzerDebugMode()}]
                if (message) {
                    console.error(message);
                }
            [{/if}]

            $('.js-unzer-error-holder').html(html).show(0, function () {
                $(this).focus();
                $('html, body').animate({scrollTop: 0}, "slow");
            });
        }

        function abortPaymentSession(session) {
            session.completePayment({status: window.ApplePaySession.STATUS_FAILURE});
            session.abort();
        }
    [{/capture}]
[{oxscript add=$unzerApplePayJS}]