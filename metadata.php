<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

/**
 * Metadata version
 */

use OxidSolutionCatalysts\Unzer\Controller\Admin\AdminOrderController;
use OxidSolutionCatalysts\Unzer\Controller\Admin\ModuleConfiguration;
use OxidSolutionCatalysts\Unzer\Controller\Admin\OrderMain;
use OxidSolutionCatalysts\Unzer\Controller\DispatcherController;
use OxidSolutionCatalysts\Unzer\Controller\InstallmentController;
use OxidSolutionCatalysts\Unzer\Controller\OrderController;
use OxidSolutionCatalysts\Unzer\Controller\PaymentController;
use OxidSolutionCatalysts\Unzer\Core\Config;
use OxidSolutionCatalysts\Unzer\Core\ShopControl;
use OxidSolutionCatalysts\Unzer\Core\ViewConfig;
use OxidSolutionCatalysts\Unzer\Model\PaymentGateway;
use OxidSolutionCatalysts\Unzer\Module;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;

$sMetadataVersion = '2.1';

/**
 * Module information.
 */
$aModule = [
    'id' => Module::MODULE_ID,
    'title' => [
        'de' => 'Unzer Payment-Modul für OXID',
        'en' => 'Unzer Payment Module for OXID',
    ],
    'description' => [
        'de' => '',
        'en' => '',
    ],
    'thumbnail' => 'logo.svg',
    'version' => '1.0.0',
    'author' => 'OXID eSales AG',
    'url' => 'https://www.oxid-esales.com',
    'email' => 'info@oxid-esales.com',
    'extend' => [
        \OxidEsales\Eshop\Application\Controller\PaymentController::class => PaymentController::class,
        \OxidEsales\Eshop\Core\ViewConfig::class => ViewConfig::class,
        \OxidEsales\Eshop\Core\Config::class => Config::class,
        \OxidEsales\Eshop\Application\Model\Payment::class => \OxidSolutionCatalysts\Unzer\Model\Payment::class,
        \OxidEsales\Eshop\Application\Controller\OrderController::class => OrderController::class,
        \OxidEsales\Eshop\Application\Model\PaymentGateway::class => PaymentGateway::class,
        \OxidEsales\Eshop\Application\Model\Order::class => \OxidSolutionCatalysts\Unzer\Model\Order::class,
        \OxidEsales\Eshop\Core\ShopControl::class => ShopControl::class,
        \OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration::class => ModuleConfiguration::class,
        \OxidEsales\Eshop\Application\Controller\Admin\OrderMain::class => OrderMain::class,
    ],
    'controllers' => [
        'unzer_admin_order' => AdminOrderController::class,
        'unzer_dispatcher' => DispatcherController::class,
        'unzer_installment' => InstallmentController::class,
    ],
    'templates' => [
        // admin
        'oscunzer_order.tpl' => 'osc/unzer/views/admin/tpl/oscunzer_order.tpl',

        // frontend
        'modules/osc/unzer/unzer_assets.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_assets.tpl',
        'modules/osc/unzer/unzer_card.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_card.tpl',
        'modules/osc/unzer/unzer_eps_charge.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_eps_charge.tpl',
        'modules/osc/unzer/unzer_installment.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_installment.tpl',
        'modules/osc/unzer/unzer_invoice_secured.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_invoice_secured.tpl',
        'modules/osc/unzer/unzer_applepay.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_applepay.tpl',
        'modules/osc/unzer/unzer_sepa.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_sepa.tpl',
        'modules/osc/unzer/unzer_sepa_secured.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_sepa_secured.tpl',
        'modules/osc/unzer/unzer_ideal.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_ideal.tpl',
        'modules/osc/unzer/unzer_shippingAndPayment_flow.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_shippingAndPayment_flow.tpl',
        'modules/osc/unzer/unzer_shippingAndPayment_wave.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_shippingAndPayment_wave.tpl',
        'modules/osc/unzer/unzer_installment_confirm.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_installment_confirm.tpl',
        'modules/osc/unzer/payment/applepay_availibility_check.tpl' => 'osc/unzer/views/frontend/tpl/payment/applepay_availibility_check.tpl',
    ],
    'blocks' => [
        //frontend
        [
            'theme' => 'flow',
            'template' => 'page/checkout/order.tpl',
            'block' => 'shippingAndPayment',
            'file' => 'views/frontend/blocks/page/checkout/shippingAndPayment_flow.tpl'
        ],
        [
            'theme' => 'wave',
            'template' => 'page/checkout/order.tpl',
            'block' => 'shippingAndPayment',
            'file' => 'views/frontend/blocks/page/checkout/shippingAndPayment_wave.tpl'
        ],
        [
            'template' => 'page/checkout/order.tpl',
            'block' => 'checkout_order_errors',
            'file' => 'views/frontend/blocks/page/checkout/checkout_order_errors.tpl'
        ],
        [
            'template' => 'page/checkout/payment.tpl',
            'block' => 'select_payment',
            'file' => 'views/frontend/blocks/page/checkout/select_payment.tpl'
        ],
        //admin
        [
            'template' => 'module_config.tpl',
            'block' => 'admin_module_config_var',
            'file' => 'views/admin/blocks/admin_module_config_var.tpl'
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
            'group' => 'merchant',
            'name' => 'registeredWebhook',
            'type' => 'str',
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
        [
            'group' => 'paymentoptions',
            'name' => 'UnzerOption_oscunzer_card',
            'type' => 'select',
            'value' => '0',
            'constraints' => '0|1'
        ],
        [
            'group' => 'paymentoptions',
            'name' => 'UnzerOption_oscunzer_paypal',
            'type' => 'select',
            'value' => '0',
            'constraints' => '0|1'
        ],
        [
            'group' => 'paymentoptions',
            'name' => 'UnzerOption_oscunzer_applepay',
            'type' => 'select',
            'value' => '0',
            'constraints' => '0|1'
        ],
        [
            'group' => 'paymentoptions',
            'name' => 'UnzerOption_oscunzer_installment_rate',
            'type' => 'str',
            'value' => '4.5'
        ],
        [
            'group' => 'applePay',
            'name' => 'applepay_networks',
            'type' => 'aarr',
            'value' => ModuleSettings::APPLE_PAY_NETWORKS
        ],
        [
            'group' => 'applePay',
            'name' => 'applepay_merchant_capabilities',
            'type' => 'aarr',
            'value' => ModuleSettings::APPLE_PAY_MERCHANT_CAPABILITIES
        ],
        [
            'group' => 'applePay',
            'name' => 'applepay_label',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'other',
            'name' => 'UnzerjQuery',
            'type' => 'bool',
            'value' => '0',
        ]
    ],
    'events' => [
        'onActivate' => '\OxidSolutionCatalysts\Unzer\Core\Events::onActivate',
        'onDeactivate' => '\OxidSolutionCatalysts\Unzer\Core\Events::onDeActivate',
    ],
];
