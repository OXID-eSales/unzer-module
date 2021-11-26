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
        \OxidEsales\Eshop\Application\Model\PaymentGateway::class               => \OxidSolutionCatalysts\Unzer\Model\PaymentGateway::class,
        \OxidEsales\Eshop\Application\Model\Order::class                        => \OxidSolutionCatalysts\Unzer\Model\Order::class
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
        'modules/osc/unzer/unzer_bancontact.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_bancontact.tpl',
        'modules/osc/unzer/unzer_pis.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_pis.tpl',
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
        [
            'template' => 'page/checkout/order.tpl',
            'block' => 'checkout_order_errors',
            'file' => 'views/frontend/blocks/checkout_order_errors.tpl'
        ],
        //email
        [
            'template' => 'email/plain/order_cust.tpl',
            'block' => 'email_plain_order_cust_paymentinfo',
            'file' => 'views/frontend/blocks/email/unzer_email_plain_order_cust_paymentinfo.tpl'
        ],
        [
            'template' => 'email/html/order_cust.tpl',
            'block' => 'email_html_order_cust_paymentinfo',
            'file' => 'views/frontend/blocks/email/unzer_email_html_order_cust_paymentinfo.tpl'
        ],
    ],
    'settings' => [
        [
            'group' => 'merchant',
            'name' => 'sandbox-UnzerPublicKey',
            'type' => 'str'
        ],
        [
            'group' => 'merchant',
            'name' => 'sandbox-UnzerPrivateKey',
            'type' => 'str'
        ],
        [
            'group' => 'merchant',
            'name' => 'sandbox-UnzerApiKey',
            'type' => 'str'
        ],
        [
            'group' => 'merchant',
            'name' => 'production-UnzerPublicKey',
            'type' => 'str'
        ],
        [
            'group' => 'merchant',
            'name' => 'production-UnzerPrivateKey',
            'type' => 'str'
        ],
        [
            'group' => 'merchant',
            'name' => 'production-UnzerApiKey',
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
            'name' => 'UnzerDebug',
            'type' => 'bool',
            'value' => '0',
        ],
    ],
    'events' => [
        'onActivate'    => '\OxidSolutionCatalysts\Unzer\Core\Events::onActivate',
        'onDeactivate'  => '\OxidSolutionCatalysts\Unzer\Core\Events::onDeActivate',
    ],
];
