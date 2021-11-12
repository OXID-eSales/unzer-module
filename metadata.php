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
        \OxidEsales\Eshop\Application\Controller\PaymentController::class       => \OxidSolutionCatalysts\Unzer\Controller\PaymentController::class,
        \OxidEsales\Eshop\Core\ViewConfig::class                                => \OxidSolutionCatalysts\Unzer\Core\ViewConfig::class,
        \OxidEsales\Eshop\Application\Model\Payment::class                      => \OxidSolutionCatalysts\Unzer\Model\Payment::class,
        \OxidEsales\Eshop\Application\Controller\OrderController::class         => \OxidSolutionCatalysts\Unzer\Controller\OrderController::class,
    ],
    'controllers' => [
        'unzer_admin_order' => \OxidSolutionCatalysts\Unzer\Controller\Admin\AdminOrderController::class,
        'unzer_dispatcher'  =>   \OxidSolutionCatalysts\Unzer\Controller\DispatcherController::class,
    ],
    'templates' => [
        // admin
        'oscunzer_order.tpl' => 'osc/unzer/views/admin/tpl/oscunzer_order.tpl',

        // frontend
        'modules/osc/unzer/unzer_alipay.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_alipay.tpl',
        'modules/osc/unzer/unzer_bankcontact.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_bankcontact.tpl',
        'modules/osc/unzer/unzer_banktransfer.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_banktransfer.tpl',
        'modules/osc/unzer/unzer_card.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_card.tpl',
        'modules/osc/unzer/unzer_card_recurring.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_card_recurring.tpl',
        'modules/osc/unzer/unzer_eps_charge.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_eps_charge.tpl',
        'modules/osc/unzer/unzer_giro.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_giro.tpl',
        'modules/osc/unzer/unzer_installment.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_installment.tpl',
        'modules/osc/unzer/unzer_invoice_securred.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_invoice_securred.tpl',
        'modules/osc/unzer/unzer_paypal.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_paypal.tpl',
        'modules/osc/unzer/unzer_paypal_recurring.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_paypal_recurring.tpl',
        'modules/osc/unzer/unzer_prepayment.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_prepayment.tpl',
        'modules/osc/unzer/unzer_sepa.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_sepa.tpl',
        'modules/osc/unzer/unzer_sepa_secured.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_sepa_secured.tpl',
        'modules/osc/unzer/unzer_sofort.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_sofort.tpl',
        'modules/osc/unzer/unzer_wechat.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_wechat.tpl',
    ],
    'blocks' => [
        //admin
        [
            'template' => 'payment_main.tpl',
            'block'    => 'admin_payment_main_form',
            'file'     => 'views/admin/blocks/admin_payment_main_form.tpl',
        ],
        //frontend
        [
            'template' => 'page/checkout/order.tpl',
            'block' => 'shippingAndPayment',
            'file' => 'views/frontend/blocks/unzer_select_payment.tpl'
        ],
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
