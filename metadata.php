<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

/**
 * Module information.
 */
$aModule = [
    'id' => 'osc-unzer',
    'title' => [
        'de' => 'Unzer Payment-Modul für OXID',
        'en' => 'Unzer Payment Module for OXID',
    ],
    'description' => [
        'de' => '',
        'en' => '',
    ],
    'thumbnail'    => 'logo.svg',
    'version' => '1.0.0',
    'author' => 'OXID eSales AG',
    'url' => 'https://www.oxid-esales.com',
    'email' => 'info@oxid-esales.com',
    'extend' => [
    ],
    'controllers' => [
        \OxidEshop\Application\Controller\PaymentController::class
            => \OxidSolutionCatalists\Unzer\Controller\PaymentController::class
    ],
    'templates' => [
    ],
    'blocks' => [
    ],
    'settings' => [
    ],
    'events' => [
    ],
];
