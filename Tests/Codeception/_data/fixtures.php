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
    ]
];
