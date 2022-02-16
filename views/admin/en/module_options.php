<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

$sLangName = "English";

$aLang = [
    "charset" => "UTF-8",
    'SHOP_MODULE_GROUP_merchant' => 'Access Data',
    'SHOP_MODULE_GROUP_environment' => 'Environment',
    'SHOP_MODULE_GROUP_paymentoptions' => 'Payment settings',
    'SHOP_MODULE_GROUP_applePay' => 'Apple Pay',
    'SHOP_MODULE_GROUP_other' => 'Other',
    'SHOP_MODULE_sandbox-UnzerPublicKey' => 'Sandbox Public-Key',
    'SHOP_MODULE_sandbox-UnzerPrivateKey' => 'Sandbox Private-Key',
    'SHOP_MODULE_sandbox-UnzerApiKey' => 'Sandbox Api-Key',
    'SHOP_MODULE_production-UnzerPublicKey' => 'Production Public-Key',
    'SHOP_MODULE_production-UnzerPrivateKey' => 'Production Private-Key',
    'SHOP_MODULE_production-UnzerApiKey' => 'Production Api-Key',
    'HELP_SHOP_MODULE_UnzerApiKey' => 'Username for HTTP-Basic auth',
    'SHOP_MODULE_UnzerSystemMode' => 'Mode',
    'SHOP_MODULE_UnzerSystemMode_0' => 'Sandbox',
    'SHOP_MODULE_UnzerSystemMode_1' => 'Production',
    'SHOP_MODULE_UnzerOption_oscunzer_card' => 'CreditCard',
    'SHOP_MODULE_UnzerOption_oscunzer_card_0' => 'direct Capture',
    'SHOP_MODULE_UnzerOption_oscunzer_card_1' => 'Authorize & later Capture',
    'SHOP_MODULE_UnzerOption_oscunzer_paypal' => 'PayPal',
    'SHOP_MODULE_UnzerOption_oscunzer_paypal_0' => 'direct Capture',
    'SHOP_MODULE_UnzerOption_oscunzer_paypal_1' => 'Authorize & later Capture',
    'SHOP_MODULE_UnzerOption_oscunzer_applepay' => 'Apple Pay',
    'SHOP_MODULE_UnzerOption_oscunzer_applepay_0' => 'direct Capture',
    'SHOP_MODULE_UnzerOption_oscunzer_applepay_1' => 'Authorize & later Capture',
    'SHOP_MODULE_UnzerjQuery' => 'Include jQuery via the module',
    'SHOP_MODULE_UnzerOption_oscunzer_installment_rate' => 'Installment rate',
    'HELP_SHOP_MODULE_UnzerOption_oscunzer_installment_rate' => 'Installmentrate per 100 in %, i.e.: "4.5"',
    'HELP_SHOP_MODULE_UnzerSystemMode' => 'Here you can switch between Sandbox (demo mode) and Production mode',
    'SHOP_MODULE_UnzerDebug' => 'Enable debug mode',
    'HELP_SHOP_MODULE_UnzerDebug' => 'In active debug mode, log files are written to the /log/unzer directory.',
    'HELP_SHOP_MODULE_UnzerLogLevel' => 'The log level determines which events are logged in the log file. Possible values are Debug (all), Warning (warnings and errors), or Error (only errors).',
    'SHOP_MODULE_WEBHOOK' => 'Registered Webhook',
    'SHOP_MODULE_REGISTER_WEBHOOK' => 'Register Webhook',
    'SHOP_MODULE_DELETE_WEBHOOK' => 'Delete Webhook',
    'SHOP_MODULE_WEBHOOK_NO_UNZER' => 'In order to register a webhook, the Unzer keys must be stored',
    'SHOP_MODULE_applepay_networks' => 'Supported payment types',
    'SHOP_MODULE_applepay_networks_supportsCredit' => 'Credit Card',
    'HELP_SHOP_MODULE_applepay_networks' => 'Payment methods supported by the merchant. The value "supports3DS" is always sent to Apple Pay. The rest is optional.',
    'SHOP_MODULE_applepay_networks_supportsDebit' => 'Debit Card',
    'SHOP_MODULE_applepay_networks_supportsEMV' => 'China Union Pay transactions',
    'SHOP_MODULE_applepay_merchant_capabilities' => 'Supported credit cards',
    'HELP_SHOP_MODULE_applepay_merchant_capabilities' => 'Credit cards supported by the merchant. If credit card is supported, you must select at least one of the credit card types',
    'SHOP_MODULE_applepay_merchant_capabilities_amex' => 'American Express',
    'SHOP_MODULE_applepay_merchant_capabilities_cartesBancaires' => 'Cartes Bancaires',
    'SHOP_MODULE_applepay_merchant_capabilities_chinaUnionPay' => 'China Union Pay',
    'SHOP_MODULE_applepay_merchant_capabilities_discover' => 'Discover',
    'SHOP_MODULE_applepay_merchant_capabilities_eftpos' => 'EFTPOS',
    'SHOP_MODULE_applepay_merchant_capabilities_electron' => 'Visa Electron',
    'SHOP_MODULE_applepay_merchant_capabilities_elo' => 'Elo',
    'SHOP_MODULE_applepay_merchant_capabilities_interac' => 'Interac',
    'SHOP_MODULE_applepay_merchant_capabilities_jcb' => 'JCB',
    'SHOP_MODULE_applepay_merchant_capabilities_mada' => 'Mada',
    'SHOP_MODULE_applepay_merchant_capabilities_maestro' => 'Maestro',
    'SHOP_MODULE_applepay_merchant_capabilities_masterCard' => 'Mastercard',
    'SHOP_MODULE_applepay_merchant_capabilities_privateLabel' => 'Private Label',
    'SHOP_MODULE_applepay_merchant_capabilities_visa' => 'Visa',
    'SHOP_MODULE_applepay_merchant_capabilities_vPay' => 'V-Pay',
    'SHOP_MODULE_applepay_label' => 'Company',
    'HELP_SHOP_MODULE_applepay_label' => 'If no value is entered, the company name stored in the basic settings is used instead',
];
