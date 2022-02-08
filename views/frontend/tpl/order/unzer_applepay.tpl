[{include file="modules/osc/unzer/unzer_assets.tpl"}]

[{if false}]
<script>
    [{/if}]
    [{capture assign="unzerApplePayJS"}]
[{*    [{strip}]*}]
    var $errorHolder = $('#error-holder');

    var unzerInstance = new unzer('[{$oView->publicApiKey()}]');
    var unzerApplePayInstance = unzerInstance.ApplePay();

    $('#orderConfirmAgbBottom').submit(function (e) {
      e.preventDefault();
      setupApplePaySession();
    })

    function startApplePaySession (applePayPaymentRequest) {
      if (window.ApplePaySession && ApplePaySession.canMakePayments()) {
        var session = new ApplePaySession(6, applePayPaymentRequest);
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
        handleError('This device does not support Apple Pay!');
      }
    }

    function applePayAuthorizedCallback (event, session) {
      // Get payment data from event. "event.payment" also contains contact information, if they were set via Apple Pay.
      var paymentData = event.payment.token.paymentData;
      var $form = $('form[id="payment-form"]');
      var formObject = QueryStringToObject($form.serialize());

      // Create an Unzer instance with your public key
      unzerApplePayInstance.createResource(paymentData)
        .then(function (createdResource) {
          formObject.typeId = createdResource.id;
          // Hand over the type ID to your backend.
          $.post('./Controller.php', JSON.stringify(formObject), null, 'json')
            .done(function (result) {
              // Handle the transaction respone from backend.
              var status = result.transactionStatus;
              if (status === 'success' || status === 'pending') {
                session.completePayment({ status: window.ApplePaySession.STATUS_SUCCESS });
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

    function merchantValidationCallback (session, event) {
      $.post('./merchantvalidation.php', JSON.stringify({ 'merchantValidationUrl': event.validationURL }), null, 'json')
        .done(function (validationResponse) {
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

    function onCancelCallback (event) {
      handleError('Canceled by user');
    }

    [{assign var="currency" value=$oView->getActCurrency()}]
    [{assign var="total" value=$oxcmp_basket->getPrice()}]
    [{assign var="deliveryCost" value=$oxcmp_basket->getDeliveryCost()}]

    // Get called when pay button is clicked. Prepare ApplePayPaymentRequest and call `startApplePaySession` with it.
    function setupApplePaySession () {
      var applePayPaymentRequest = {
        countryCode: '[{$oViewConf->getActLanguageAbbr()|upper}]',
        currencyCode: '[{$currency->name}]',
        total: {
          label: 'Unzer GmbH',
          amount: [{$total->getPrice()}]
        },
        supportedNetworks: ['amex', 'visa', 'masterCard', 'discover'],
        merchantCapabilities: ['supports3DS', 'supportsEMV', 'supportsCredit', 'supportsDebit'],
        requiredShippingContactFields: ['postalAddress', 'name', 'phone', 'email'],
        requiredBillingContactFields: ['postalAddress', 'name', 'phone', 'email'],
        lineItems: [
        [{foreach from=$oxcmp_basket->getDiscounts() item="oDiscount"}]
            {'label': '[{$oDiscount->sDiscount}]','type': 'final','amount': '[{$oDiscount->dDiscount*-1}]'},
        [{/foreach}]
        [{if $oViewConf->getShowVouchers() && $oxcmp_basket->getVoucherDiscValue()}]
            [{foreach from=$oxcmp_basket->getVouchers() item="oVoucher"}]
              {'label': '[{oxmultilang ident="COUPON"}] ([{oxmultilang ident="NUMBER"}] [{$oVoucher->sVoucherNr}])','type': 'final','amount': '[{$oVoucher->dVoucherdiscount*-1}]'},
            [{/foreach}]
        [{/if}]
        [{if $deliveryCost && ($oxcmp_basket->getBasketUser() || $oViewConf->isFunctionalityEnabled('blCalculateDelCostIfNotLoggedIn'))}]
            [{if $oViewConf->isFunctionalityEnabled('blShowVATForDelivery') }]
                [{assign var="dShippingVatValue" value=$deliveryCost->getVatValue()}]
                {'label': '[{oxmultilang ident="SHIPPING_NET"}]','type': 'final','amount': '[{$deliveryCost->getNettoPrice()}]'},
                [{if $dShippingVatValue}]
                    {'label': '[{if $oxcmp_basket->isProportionalCalculationOn()}][{oxmultilang ident="BASKET_TOTAL_PLUS_PROPORTIONAL_VAT" suffix="COLON"}][{else}][{oxmultilang ident="VAT_PLUS_PERCENT_AMOUNT" args=$deliveryCost->getVat()}][{/if}]','type': 'final','amount': '[{$dShippingVatValue}]'},
                [{/if}]
            [{else}]
                {'label': '[{oxmultilang ident="SHIPPING_COST"}]','type': 'final','amount': '[{$deliveryCost->getBruttoPrice()}]'},
        [{/if}]
        [{/if}]
        ]
      };

      startApplePaySession(applePayPaymentRequest);
    }

    // Updates the error holder with the given message.
    function handleError (message) {
      $errorHolder.html(message);
    }

    // Translates query string to object
    function QueryStringToObject (queryString) {
      var pairs = queryString.slice().split('&');
      var result = {};

      pairs.forEach(function (pair) {
        pair = pair.split('=');
        result[pair[0]] = decodeURIComponent(pair[1] || '');
      });
      return JSON.parse(JSON.stringify(result));
    }

    // abort current payment session.
    function abortPaymentSession (session) {
      session.completePayment({ status: window.ApplePaySession.STATUS_FAILURE });
      session.abort();
    }
[{*    [{/strip}]*}]
    [{/capture}]

    [{if false}]
</script>
[{/if}]
[{oxscript add=$unzerApplePayJS}]