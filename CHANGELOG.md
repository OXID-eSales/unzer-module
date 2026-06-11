# Change Log for Unzer Checkout for OXID

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [2.2.7] - 2026-06-11

### Fixed

- [0007948](https://bugs.oxid-esales.com/view.php?id=7948): Fix method name collision ViewConfig::getModuleSettings() with other modules

## [2.2.6] - 2026-04-10

### Security

- Migrate TmpOrder serialization to FlexibleSerializer with `allowed_classes` restriction (prevents PHP object injection)
- Add SSRF protection: URL whitelist for Apple Pay merchantValidationUrl (ApplePayCallbackController)
- Remove double URL decoding in ApplePayCallbackController::validateMerchant()
- Add CSRF protection (checkSessionChallenge) to AccountSavedPaymentController::deletePayment()
- Add owner verification check to AccountSavedPaymentController::deletePayment() (prevents IDOR)
- Fix DOM-XSS: replace innerHTML/jQuery .html() with textContent/.text() for error messages in payment templates
- Add request body size limit (1 MB) to DispatcherController webhook endpoint
- Replace MD5 with SHA-256 for transaction ID generation (Transaction::prepareTransactionOxid)
- Add SECURITY.md documenting known security considerations and intentionally unfixed items

## [2.2.5] - 2026-02-19

### FIXED

- [0007892](https://bugs.oxid-esales.com/view.php?id=7892): fix wrong exception handling

## [2.2.4] - 2026-01-16

### FIXED

- correct verification of the controller method (thankyou-controller)

## [2.2.3] - 2025-11-27

### FIXED
- [0007827](https://bugs.oxid-esales.com/view.php?id=7827): fix errormessage-handling & add two new translations, one message to explain error API.320.100.135 from bugtracker #7827


## [2.2.2] - 2025-11-11

### FIXED
- [0007832](https://bugs.oxid-esales.com/view.php?id=7832): fix, 0007832: Display problems in the shop admin if the admin_order_list_item block has already been expanded by other module

## [2.2.1] - 2025-09-19

### FIXED
- [0007808](https://bugs.oxid-esales.com/view.php?id=7808): fix, even if the credit card payment was cancelled (when entering the secure code), an order confirmation is sent
- fix Maintenance-Mode in module deactivation (OXID7.3)
- move some parts in the admin-order-tpl, to prevent maintenance for non-unzer-orders
- fix Backend-Option-Translations for Invoice-Keys
- for UnzerSystemMode use select-Option like in OXID6
- [0007826](https://bugs.oxid-esales.com/view.php?id=7826): fix, paylater-invoice must not require both EUR and CHF webhooks, checks for Invoice and Installment Eligibility now currency independent

## [2.2.0] - 2025-06-06

### NEW
- New Paymentmethod Unzer installment (Paylater)
- If a customer interrupt the order in the checkout for any reason, the order is still saved using a temporary order and Unzer's webhook + scheduled cleanup  temporary order
- add confirm dialog when collecting money in admin and disable the collect button until site is reloaded
- UNZER-456: add the requirement not to sending the payment to unzer, if the minimum payment amount is not reached
- set Smarty-Tpl-Check-Methods as deprecated

### FIXED
- refactor of the correct use of the appropriate credentials depending on the payment method used
- [0007553](https://bugs.oxid-esales.com/view.php?id=7553) revert this task because, it is possible to have different billing and delivery addresses for invoice purchases (Paylater)
- [0007586](https://bugs.oxid-esales.com/view.php?id=7586) - Fix: Unable to finish the checkout process using the Apple Pay as the payment method
- [0007638](https://bugs.oxid-esales.com/view.php?id=7638) - Fix: Sometimes duplicate order-positions in Backend, and dublicate ordermails ...
- Discounts with time restrictions may not be invalidated directly in the checkout...
- provided additional Order-Number is searchable
- increase line spacing for the error messages
- Save Payment Data for registered Users
- fix inconsistent data for Paylater payment methods
- use Module-Logger also in UnzerPayment
- Avoid checking AppleCert if ApplePay is off
- Fix Save Module Config
- Show the remaining amount instead of the full amount in the transaction history if an order was e.g. partially collected
- Inform the customer in frontend, if the payment was cancelled by customer during checkout
- fix system crashes sporadically [not always replicatable] on card payment on return from the 3d-auth page with this in log
- fix show prePayment-BankInformations on Thankyou-Page
- save ApplePay-Certificates in any case of change ModuleConfiguration
- fix provide ShopMetadata for UnzerPayment
- fix show multiple vouchers separately
- fix: only OK, NOT_FINISHED or CANCEL in oxorder->oxtranstatus allowed
- fix showing correct amount when cancelling in admin
- Order of credit card data input fields optimized
- Add Descriptor for PrePayment on ThankYou-Page
- remove Option "Capture Later"-Option for ApplePay
- [0007795](https://bugs.oxid-esales.com/view.php?id=7795): Fix select several saved credit cards in the checkout
- [0007796](https://bugs.oxid-esales.com/view.php?id=7796): Fix the style adjusted in the frontend user account - Saved Payments

## [2.1.4] - 2023-11-23

### FIXED
- [0007503](https://bugs.oxid-esales.com/view.php?id=7503) When ordering via the Unzer module, the OXID standard field OXORDER__OXTRANSID remains empty
- [0007526](https://bugs.oxid-esales.com/view.php?id=7526) Order would be saved only, if everything is correct. In all other cases redirect to checkout
- [0007509](https://bugs.oxid-esales.com/view.php?id=7509) Order would be saved only, if everything is correct. In all other cases redirect to checkout
- [0007524](https://bugs.oxid-esales.com/view.php?id=7524) catch Error if Unzer-API not working and redirect to Checkout
- [0007527](https://bugs.oxid-esales.com/view.php?id=7527) prevent clicking the buy-now-button several times
- [0007544](https://bugs.oxid-esales.com/view.php?id=7544) Add Error handling When unsupported Credit Card is used (e.g. Amex)
- [0007553](https://bugs.oxid-esales.com/view.php?id=7553) The billing and delivery address must be identical for invoice purchases (Paylater)
- [0007546](https://bugs.oxid-esales.com/view.php?id=7546): We provide an additional Order Number to Unzer for identify the Order in OXID-Backend and Unzer-Insights

- apple pay session init only when eligible
- compatibility-issue against other modules that also extend the moduleconfiguration 
- Adjust payment date when the payment has been completed
- Prepayment - Adjust payment date when the payment has been completed
- change information for Unzer-Metadata

## [2.0.0] - only DEV

### NEW
- Update module for OXID 7.0
- split from Version 1.1.3

## [1.1.3] - 2023-11-14

- [0007526](https://bugs.oxid-esales.com/view.php?id=7526) Order would be saved only, if everything is correct. In all other cases redirect to checkout
- [0007509](https://bugs.oxid-esales.com/view.php?id=7509) Order would be saved only, if everything is correct. In all other cases redirect to checkout
- [0007524](https://bugs.oxid-esales.com/view.php?id=7524) catch Error if Unzer-API not working and redirect to Checkout
- [0007527](https://bugs.oxid-esales.com/view.php?id=7527) prevent clicking the buy-now-button several times
- [0007544](https://bugs.oxid-esales.com/view.php?id=7544) Add Error handling When unsupported Credit Card is used (e.g. Amex)
- [0007553](https://bugs.oxid-esales.com/view.php?id=7553) The billing and delivery address must be identical for invoice purchases (Paylater)
- [0007546](https://bugs.oxid-esales.com/view.php?id=7546): We provide an additional Order Number to Unzer for identify the Order in OXID-Backend and Unzer-Insights
- Prepayment - Adjust payment date when the payment has been completed
- change information for Unzer-Metadata
- Unzer Invoice (Paylater): Display bank details for invoice

## [1.1.2] - 2023-08-18

### FIXED
- compatibility-issue against other modules that also extend the moduleconfiguration
- [0007503](https://bugs.oxid-esales.com/view.php?id=7503) When ordering via the Unzer module, the OXID standard field OXORDER__OXTRANSID remains empty
- Adjust payment date when the payment has been completed

## [1.1.1] - 2023-06-19

### FIXED
- apple pay session init only when eligible

## [1.1.0] - 2023-06-02

### NEW
- New Payment PayLater
### FIXED
- Webhooks cleanup, registration is now based on key (context)
- Fixed ApplePay admin settings not saving the merchant certificate properly.
- New country restrictions based on the Unzer documentation
  - ALIPAY: DE, AT, BE, IT, ES, NL
  - Unzer Invoice (Paylater): DE, AT, CH, NL
  - Prepayment: all Countries
  - SEPA Direct Debit: DE, AT
  - Sofort: DE, AT, BE, IT, ES, NL
  - WeChat: AT, BE, DK, FI, FR, DE, ES, GB, GR, HU, IE, IS, IT, LI, LU, MT, NL, NO, PT, SE
- New currency restrictions based on the Unzer documentation
  - ALIPAY: AUD, CAD, CHF, CNY, EUR, GBP, HKD, NZD, SGD, USD
  - ApplePay: AUD, CHF, CZK, DKK, EUR, GBP, NOK, PLN, SEK, USD, HUF, RON, BGN, HRK, ISK
  - Bancontact: EUR
  - EPS: EUR
  - Giropay: EUR
  - IDEAL: EUR
  - Przelewy24: PLZ
  - Sofort: EUR
  - SEPA Direct Debit: EUR
  - Unzer Invoice (Paylater): EUR, CHF
  - Prepayment: EUR
  - WeChat Pay: CHF, CNY, EUR, GBP, USD
- Cleanup payment methods in database configuration
- Correct Customer-Details for Unzer (e.g. OXID-customerId)
- [0007453](https://bugs.oxid-esales.com/view.php?id=7453) Unzer prohibits changes in settings of other modules
- [0007454](https://bugs.oxid-esales.com/view.php?id=7454) Unzer and Paypal cannot be activated at the same time
- [0007436](https://bugs.oxid-esales.com/view.php?id=7436) add option to reverese a prepayment-transaction from the backend
- [0007430](https://bugs.oxid-esales.com/view.php?id=7430) Update Basket version to V2
- [0007429](https://bugs.oxid-esales.com/view.php?id=7429) Customer details - adjust addresses
- [0007432](https://bugs.oxid-esales.com/view.php?id=7432) Basket Details - Discount is missed in the Basket
- [0007447](https://bugs.oxid-esales.com/view.php?id=7447) Markup due to negative discount per shopping cart leads to maintainance mode
- [0007439](https://bugs.oxid-esales.com/view.php?id=7439) Chargeback transactions do not appear in the backend
- [0007442](https://bugs.oxid-esales.com/view.php?id=7442) Reversal after partial reversal

### CHANGES
Unzer has **deprecated** following payment methods, which have been removed from the definitions:
- Installment / Ratenzahlung
- Unzer Direct Debit Secured/ SEPA Lastschrift (abgesichert durch Unzer)
- Bank transfer


## [1.0.1] - 2022-12-03

### FIXED
- Optimized webhook saving in the config
- Update github-actions

## [1.0.0] - 2022-07-28

- initial release for OXID>=v6.3 and as part of EE-Compilation v.6.5.0
