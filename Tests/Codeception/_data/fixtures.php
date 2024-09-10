<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

return [
    'admin_user' => [
        'username' => 'admin@myoxideshop.com',
        'password' => 'admin0303'
    ],
    // This product is available in ce|pe|ee demodata
    'product' => [
        'id' => 'dc5ffdf380e15674b56dd562a7cb6aec',
        'title' => 'Kuyichi leather belt JEVER',
        'bruttoprice_single' => '29.90',
        'nettoprice_single' => '25.13',
        'shipping_cost' => '3.90',
        'currency' => '€'
    ],

    // User for testing
    'client' => [
        "username" => "unzeruser@oxid-esales.dev",
        "password" => "useruser",
    ],

    // User for testing secured payment
    'secured_client' => [
        "username" => "unzersecureuser@oxid-esales.dev",
        "password" => "useruser",
    ],

    //User for testing Alipay
    'alipay_client' => [
        "username" => "keychain",
        "password" => "123",
    ],

    //User for testing Wechatpay
    'wechatpay_client' => [
        "username" => "keychain",
        "password" => "123",
    ],

    // Payment data for SEPA
    'sepa_payment' => [
        "IBAN" => "DE89370400440532013000",
    ],

    // Payment data for Credit card using Mastercard
    'mastercard_payment' => [
        "cardnumber" => "5453010000059543",
        "cardholder" => "Marc Muster",
        "CVC" => "123",
        "3DSpassword" => "secret3"
    ],

    // Payment data for Credit card using Visa
    'visa_payment' => [
        "cardnumber" => "4711100000000000",
        "cardholder" => "Marc Muster",
        "CVC" => "123",
        "3DSpassword" => "secret3"
    ],

    // Payment data for Credit card using Mastercard
    'maestro_payment' => [
        "cardnumber" => "6799851000000032",
        "cardholder" => "Marc Muster",
        "CVC" => "123",
        "3DSpassword" => "secret3"
    ],

    // Payment data for Giropay
    'giropay_payment' => [
        "bank_number" => "12345679",
        "account_number" => "0000000300",
        "IBAN" => "DE46940594210000012345",
        "BIC" => "TESTDETT421",
        "USER" => "chiptanscatest2",
        "USER_PIN" => "12345",
        "USER_TAN" => "123456",
    ],

    // Payment data for PayPal
    'paypal_payment' => [
        "username" => "paypal-buyer@unzer.com",
        "password" => "unzer1234",
    ],

    // Payment data for Sofort
    'sofort_payment' => [
        "bank_number" => "00000",
        "account_number" => "0000000300",
        "USER_PIN" => "123456",
        "USER_TAN" => "12345",
    ],

    // Payment data for iDEAL
    'ideal_payment' => [
        "account_bankname" => "ING_TEST",
        "bank_number" => "00000",
        "account_number" => "123456",
        "USER_PIN" => "123456",
        "USER_TAN" => '12345',
        "option" => "INGBNL2A"
    ],

    // Payment data for PIS
    'pis_payment' => [
        "bank_number" => "88888888",
        "account_number" => "0000000300",
        "USER_PIN" => "123456",
        "USER_TAN" => "2357",
    ],

    // Payment data for EPS
    'eps_payment' => [
        "username" => "1003993",
        "password" => "rX/'PvZzIW?&",
        "option" => "STZZATWWXXX",
    ],
];
