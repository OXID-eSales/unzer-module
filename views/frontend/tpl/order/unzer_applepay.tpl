[{include file="modules/osc/unzer/unzer_assets.tpl"}]

[{* TODO remove script tag. Only for programming *}]
[{if false}]
<script>
    [{/if}]
    [{capture assign="unzerApplePayJS"}]
[{*        [{strip}]*}]
    const $errorHolder = $('#error-holder');

    const unzerInstance = new unzer('[{$unzerpub}]');
    const unzerApplePayInstance = unzerInstance.ApplePay();
    const $form = $('#orderConfirmAgbBottom');

    $form.submit(function (e) {
        e.preventDefault();

        const agbCheck = $('[name=ord_agb]');
        if (agbCheck && !agbCheck.is(':checked')) {
            handleError()
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
            handleError('This device does not support Apple Pay!');
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
                    const status = data.transactionStatus;

                    if (status === 'success' || status === 'pending') {
                        session.completePayment({status: window.ApplePaySession.STATUS_SUCCESS});
                        window.location.href = data.redirectUrl;
                    } else {
                        abortPaymentSession(session);
                        session.abort();
                        window.location.href = '[{$oViewConf->getSelfLink()}]cl=payment&payerror=2'
                    }
                }).fail(function (error) {
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
        }).done(function (data) {
            try {
                session.completeMerchantValidation(data.validationResponse);
            } catch (e) {
                handleError(e.message);
            }

        }).fail(function (error) {
            handleError(JSON.stringify(error.statusText));
            session.abort();
        });
    }

    function onCancelCallback() {
        handleError('Canceled by user');
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
                amount: '[{$total->getPrice()|number_format:2}]'
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
                [{foreach from=$oxcmp_basket->getDiscounts() item="oDiscount"}]
                [{assign var="discount" value=$oDiscount->dDiscount*-1}]
                {
                    label: '[{$oDiscount->sDiscount}]',
                    type: 'final',
                    amount: '[{$discount|number_format:2}]'
                },
                [{/foreach}]
                [{if $oViewConf->getShowVouchers() && $oxcmp_basket->getVoucherDiscValue()}]
                [{foreach from=$oxcmp_basket->getVouchers() item="oVoucher"}]
                [{assign var="voucherDiscount" value=$oVoucher->dVoucherdiscount*-1}]
                {
                    label: '[{oxmultilang ident="COUPON"}] ([{oxmultilang ident="NUMBER"}] [{$oVoucher->sVoucherNr}])',
                    type: 'final',
                    amount: '[{$voucherDiscount|number_format:2}]'
                },
                [{/foreach}]
                [{/if}]
                [{if $deliveryCost && ($oxcmp_basket->getBasketUser() || $oViewConf->isFunctionalityEnabled('blCalculateDelCostIfNotLoggedIn'))}]
                [{if $oViewConf->isFunctionalityEnabled('blShowVATForDelivery') }]
                [{assign var="dShippingVatValue" value=$deliveryCost->getVatValue()}]
                {
                    label: '[{oxmultilang ident="SHIPPING_NET"}]',
                    type: 'final',
                    amount: '[{$deliveryCost->getNettoPrice()|number_format:2}]'
                },
                [{if $dShippingVatValue}]
                {
                    label: '[{if $oxcmp_basket->isProportionalCalculationOn()}][{oxmultilang ident="BASKET_TOTAL_PLUS_PROPORTIONAL_VAT" suffix="COLON"}][{else}][{oxmultilang ident="VAT_PLUS_PERCENT_AMOUNT" args=$deliveryCost->getVat()}][{/if}]',
                    type: 'final',
                    amount: '[{$dShippingVatValue|number_format:2}]'
                },
                [{/if}]
                [{else}]
                {
                    label: '[{oxmultilang ident="SHIPPING_COST"}]',
                    type: 'final',
                    amount: '[{$deliveryCost->getBruttoPrice()|number_format:2}]'
                },
                [{/if}]
                [{/if}]
            ]
        };

        startApplePaySession(applePayPaymentRequest);
    }

    function handleError(message) {
        if (message) {
            console.error(message);
        }

        $('.js-unzer-error-holder').html('[{oxmultilang ident="oscunzer_APPLEPAY_ERROR"}]').show(0, function () {
            $(this).focus();
            $('html, body').animate({scrollTop: 0}, "slow");
        });
    }

    function abortPaymentSession(session) {
        session.completePayment({status: window.ApplePaySession.STATUS_FAILURE});
        session.abort();
    }
[{*        [{/strip}]*}]
    [{/capture}]

    [{if false}]
</script>
[{/if}]
[{oxscript add=$unzerApplePayJS}]