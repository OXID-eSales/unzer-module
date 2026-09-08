# Change Log for Unzer Checkout for OXID

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

### Added

- New module setting "Refund automatically when an order is cancelled" (`UnzerAutomatedRefundOnCancel`, module configuration, new group "Cancellation and refund", **off by default**). Cancelling a paid order in the backend never touched the payment at unzer: the order was flagged as cancelled while the charge stayed as it was, so the money remained with the merchant until somebody remembered to refund it in the order view. Adyen refunds on cancellation and Amazon Pay does so behind a setting, which made unzer and PayPal the exception - both are pulled along now, with the same wording and the same default in both modules. With the option on, cancelling refunds exactly the **remaining** amount - charged minus everything already cancelled, read from the payment at unzer (`Amount::getCharged() - Amount::getCanceled()`, rounded to two decimals) - because a cancellation cancels the whole order and an amount that already went back must not be sent twice. Nothing left to refund means nothing happens, and so does a payment that was only authorized and never charged: reverting an authorization is a different operation (`doUnzerAuthorizationCancel()`) and stays with the order view. The refund goes through the existing `Service\Payment::doUnzerCancel()` **without** a charge id, which cancels the charged payment as a whole and books the cancellation into `oscunzertransaction` exactly like the refund in the order view does - no second code path to the unzer API, and the reason code is `CANCEL`, the first of the three the order view offers, which secured payment methods require. A refund that fails must not undo the cancellation: the order is cancelled by then, so the failure is logged, reported to the merchant (`OSCUNZER_CANCEL_REFUND_FAILED`) and left at that - `doUnzerCancel()` answers with an `UnzerApiException` instead of throwing, and that case is handled just like an unexpected one - in this line its return type is `Exception|bool|UnzerApiException`, so the message is read off any `Throwable` rather than off the api exception alone, and the order is annotated as the module's own order model the signature asks for. `Controller\Admin\OrderList::cancelOrder()` passes the refunded amount into the existing cancellation mail, so the customer gets **one** mail naming order and refunded amount instead of a cancellation mail plus a refund mail - the case `Core\RefundMailService::sendCancelMail()` was built for; the help text of `UnzerCancelMailRecipient` no longer claims that a cancellation never refunds. Files touched: `metadata.php`, `src/Controller/Admin/OrderList.php`, `src/Service/ModuleSettings.php`, `views/admin_twig/{de,en}/module_options.php`, `views/admin_smarty/{de,en}/module_options.php`, `views/admin_twig/{de,en}/oscunzer_lang.php`, `views/admin_smarty/{de,en}/oscunzer_lang.php` (the module ships its admin language files for both template engines), `src/Module.php` (version).
- [0007989](https://bugs.oxid-esales.com/view.php?id=7989): Confirmation mails for refunds and cancellations triggered in the backend. Two new module settings in the module configuration (group "Confirmation mails") decide who is notified, separately per event: `UnzerRefundMailRecipient` and `UnzerCancelMailRecipient`, each with `0` no mail (default), `1` customer, `2` shop owner, `3` both. Defaults are `0`, so updating the module does not start sending mail to existing customers unannounced. The refund mail is sent from `Controller\Admin\AdminOrderController::doUnzerCancel()` once `Service\Payment::doUnzerCancel()` reported success — that method returns `true` on success and an `UnzerApiException` on failure, so a failed refund cannot produce a mail; the amount is the one the merchant entered, the currency comes from the order. The cancellation mail is sent by the new `Controller\Admin\OrderList::cancelOrder()` override after the order was cancelled; an order cancellation moves no money in this module, so that mail confirms the cancellation only and points out that a refund, if any, is confirmed separately. Unknown or missing setting values are normalised when read (`Service\ModuleSettings::getRefundMailRecipient()` / `getCancelMailRecipient()` map anything but `1`/`2`/`3` to "no mail"), and `Core\RefundMailService::getRecipientMode()` treats a settings service that cannot be resolved as "no mail" as well, so neither a broken value nor a missing module configuration can start sending mail or surface as an error page in the backend. Rendering switches the admin mode off and back on, because these mails are triggered from the backend but use frontend templates and frontend language files - without that, core idents such as `ORDER_NUMBER` render as "ERROR: Translation for ORDER_NUMBER not found!" in the customer's mail. New `Core\Email` (chain extension) and `Core\RefundMailService`, which is the only place deciding whether and to whom a mail goes out. Templates are shipped for both engines: `views/twig/frontend/tpl/email/{html,plain}/{refund,cancel}.html.twig` and `views/smarty/frontend/tpl/email/{html,plain}/{refund,cancel}.tpl`, addressed engine-neutrally as `@osc-unzer/frontend/tpl/email/html/refund` and so on. Both classes are deliberately free of trigger logic so they can move to the central payment base module later; the same feature is being rolled out to PayPal (0007984), Amazon Pay (0007985), Adyen (0007986) and Stripe (0007987).
- Follow-up on the confirmation mails above, after the first customer feedback: the customer facing texts named the payment provider inside the sentence ("we have issued a refund for you via Unzer." and "Unzer credits the amount to the payment method you used at Unzer."). For a provider that covers several payment methods that is too specific to be correct — the amount goes back to the card, bank account or wallet the customer actually paid with, not to "Unzer" as the customer reads it. `OSCUNZER_REFUND_MAIL_INTRO` and `OSCUNZER_REFUND_MAIL_NOTE` are therefore provider neutral now and word for word identical across all payment modules that offer refunds (PayPal, Amazon Pay, Adyen, Stripe, Unzer), so a shop running more than one of them no longer sends differently worded refund mails: "we have issued a refund for you." / "The refund has been credited to the payment method you originally used. When the amount becomes available depends on your payment method and your bank." `OSCUNZER_REFUND_MAIL_NOTE` is rendered by the cancellation mail as well (whenever an amount was refunded along with the cancellation), so that mail follows the same wording without a second ident. The shop owner copy keeps the provider information, but takes it out of the sentence: `OSCUNZER_REFUND_MAIL_INTRO_OWNER` now reads "A refund has been issued for the following order (Unzer Payment Provider)." — the mail is read next to the order in the backend, where knowing which provider moved the money is the point. The shop owner subjects (`OSCUNZER_REFUND_MAIL_SUBJECT_OWNER`, `OSCUNZER_CANCEL_MAIL_SUBJECT_OWNER`) keep their "Unzer:" prefix on purpose: a merchant who runs several payment modules sorts and filters these mails by that prefix, and a trailing parenthesis would not survive a truncated subject line in the inbox list. No code and no template change was needed — the provider names only ever lived in the language files (`translations/de/oscunzer_lang.php`, `translations/en/oscunzer_lang.php`); the mail templates address the idents only. The same wording change was made in the OXID 6.5 module `osc/unzer`.
- Note on the trigger scope: removing or cancelling a single order position is not a trigger here — the module has no order-article extension, so no partial refund originates from that screen. A cancellation of an *authorization* (`doUnzerAuthorizationCancel()`) also sends no mail: no money has been charged at that point.
- `Core\Email::class` added to the `extend` section (the module had no mail extension before) and `Controller\Admin\OrderList` gained a `cancelOrder()` override. Non-Unzer orders (payment id without the `oscunzer` prefix, or a payment that no longer exists) and orders that cannot be loaded pass straight through to the parent implementation, so the cancel behaviour of other payment methods is untouched.

### Fixed

- [0007977](https://bugs.oxid-esales.com/view.php?id=7977): Fix a `TypeError` on the thank-you page (and the resulting missing order-confirmation mail) for orders without an Unzer order number. `ViewConfig::getPrePaymentIban()` / `getPrePaymentBic()` / `getPrePaymentHolder()` / `getPrePaymentDescriptor()` declared a non-nullable `string $unzerOrderNumber` parameter, but `thankyou.html.twig` passes `order.oxorder__oxunzerordernr.value`, which is `null` whenever the order carries no Unzer order number. Passing `null` to the non-nullable parameter threw `TypeError`, which bubbled up as a `Twig\Error\RuntimeError` and aborted the whole thank-you page render — so the page errored out and no confirmation mail was sent. The parameters are now nullable (`?string`) and each method returns `null` early on an empty/`null` order number, so the bank-info block simply renders nothing instead of crashing. The fix lives in the shared `Core/ViewConfig.php`, so it covers both the Twig (Apex) and Smarty template paths. Note: the OXID 6.x variant is not affected — there the equivalent logic lives in `ThankYouController` and already guards the empty order number internally (the template calls the getters without arguments). File touched: `src/Core/ViewConfig.php`.

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
