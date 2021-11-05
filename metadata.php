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
        \OxidEsales\Eshop\Application\Controller\PaymentController::class
            => \OxidSolutionCatalysts\Unzer\Controller\PaymentController::class
    ],
    'controllers' => [
        'unzer_admin_order'              => \OxidSolutionCatalysts\Unzer\Controller\Admin\AdminOrderController::class
    ],
    'templates' => [
        //admin
        'oscunzer_order.tpl' => 'osc/unzer/views/admin/tpl/oscunzer_order.tpl',
    ],
    'blocks' => [
        //admin
        [
            'template' => 'payment_main.tpl',
            'block'    => 'admin_payment_main_form',
            'file'     => 'views/admin/blocks/admin_payment_main_form.tpl',
        ]
    ],
    'settings' => [
        [
            'group' => 'merchant',
            'name' => 'UnzerPublicKey',
            'type' => 'str'
        ],
        [
            'group' => 'merchant',
            'name' => 'UnzerPrivateKey',
            'type' => 'str'
        ],
        [
            'group' => 'merchant',
            'name' => 'UnzerApiKey',
            'type' => 'str'
        ],
        [
            'group' => 'environment',
            'name' => 'UnzerSystemMode',
            'type' => 'select',
            'value' => '0',
            'constraints' => '0|1'
        ],
        [
            'group' => 'environment',
            'name' => 'UnzerLogLevel',
            'type' => 'select',
            'value' => '0',
            'constraints' => '0|1|2'
        ],
    ],
    'events' => [
        'onActivate'    => '\OxidSolutionCatalysts\Unzer\Core\Events::onActivate',
        'onDeactivate'  => '\OxidSolutionCatalysts\Unzer\Core\Events::onDeActivate',
    ],
];
