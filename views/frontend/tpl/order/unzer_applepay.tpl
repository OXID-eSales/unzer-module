[{include file="modules/osc/unzer/unzer_assets.tpl"}]

[{if false}]
<script>
    [{/if}]
    [{capture assign="unzerApplePayJS"}]
    var $errorHolder = $('#error-holder');

    var unzerInstance = new unzer('[{$oView->publicApiKey()}]');
    var unzerApplePayInstance = unzerInstance.ApplePay();

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

    // Get called when pay button is clicked. Prepare ApplePayPaymentRequest and call `startApplePaySession` with it.
    function setupApplePaySession () {
      var applePayPaymentRequest = {
        countryCode: 'DE',
        currencyCode: 'EUR',
        total: {
          label: 'Unzer gmbh',
          amount: 12.99
        },
        supportedNetworks: ['amex', 'visa', 'masterCard', 'discover'],
        merchantCapabilities: ['supports3DS', 'supportsEMV', 'supportsCredit', 'supportsDebit'],
        requiredShippingContactFields: ['postalAddress', 'name', 'phone', 'email'],
        requiredBillingContactFields: ['postalAddress', 'name', 'phone', 'email'],
        lineItems: [
          {
            'label': 'Bag Subtotal',
            'type': 'final',
            'amount': '10.00'
          },
          {
            'label': 'Free Shipping',
            'amount': '0.00',
            'type': 'final'
          },
          {
            'label': 'Estimated Tax',
            'amount': '2.99',
            'type': 'final'
          }
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
    [{/capture}]

    [{if false}]
</script>
[{/if}]
[{oxscript add=$unzerApplePayJS}]