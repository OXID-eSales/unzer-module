<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

return [
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

    //User for testing Aipay
    'alipay_client' => [
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
        "CVC" => "123",
        "3DSpassword" => "secret3"
    ],

    // Payment data for Credit card using Visa
    'visa_payment' => [
        "cardnumber" => "4012001037461114",
        "CVC" => "123",
        "3DSpassword" => "secret3"
    ],

    // Payment data for Credit card using Mastercard
    'maestro_payment' => [
        "cardnumber" => "6799851000000032",
        "CVC" => "123",
        "3DSpassword" => "secret3"
    ],
];
