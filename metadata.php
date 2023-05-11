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
use OxidSolutionCatalysts\Unzer\Controller\ApplePayCallbackController;
use OxidSolutionCatalysts\Unzer\Controller\DispatcherController;
use OxidSolutionCatalysts\Unzer\Controller\InstallmentController;
use OxidSolutionCatalysts\Unzer\Controller\OrderController;
use OxidSolutionCatalysts\Unzer\Controller\PaymentController;
use OxidSolutionCatalysts\Unzer\Core\Config;
use OxidSolutionCatalysts\Unzer\Core\ShopControl;
use OxidSolutionCatalysts\Unzer\Core\ViewConfig;
use OxidSolutionCatalysts\Unzer\Model\PaymentGateway;
use OxidSolutionCatalysts\Unzer\Model\Article;
use OxidSolutionCatalysts\Unzer\Model\Order;
use OxidSolutionCatalysts\Unzer\Model\Payment;
use OxidSolutionCatalysts\Unzer\Module;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;

$sMetadataVersion = '2.1';

/**
 * Module information.
 */
$aModule = [
    'id' => Module::MODULE_ID,
    'title' => [
        'de' => 'Unzer Payment für OXID',
        'en' => 'Unzer Payment for OXID',
    ],
    'description' => [
        'de' => 'Vor dem Start bitte lesen und prüfen:
            <ul>
                <li><a href="https://docs.unzer.com/online-payments/go-live-checklist/" target="_blank">Go-live checklist</a></li>
                <li><a href="https://insights.unzer.com/" target="_blank">Prüfen Sie Ihren Account und Ihre Zahlarten direkt bei Unzer</a></li>
            </ul>',
        'en' => 'Please read and check before you start:
            <ul>
                <li><a href="https://docs.unzer.com/online-payments/go-live-checklist/" target="_blank">Go-live checklist</a></li>
                <li><a href="https://insights.unzer.com/" target="_blank">Check your account and your payments at unzer</a></li>
            </ul>',
    ],
    'thumbnail' => 'logo.svg',
    'version' => '1.1.0-rc.5',
    'author' => 'OXID eSales AG',
    'url' => 'https://www.oxid-esales.com',
    'email' => 'info@oxid-esales.com',
    'extend' => [
        \OxidEsales\Eshop\Application\Controller\PaymentController::class => PaymentController::class,
        \OxidEsales\Eshop\Core\ViewConfig::class => ViewConfig::class,
        \OxidEsales\Eshop\Core\Config::class => Config::class,
        \OxidEsales\Eshop\Application\Model\Payment::class => Payment::class,
        \OxidEsales\Eshop\Application\Controller\OrderController::class => OrderController::class,
        \OxidEsales\Eshop\Application\Model\PaymentGateway::class => PaymentGateway::class,
        \OxidEsales\Eshop\Application\Model\Order::class => Order::class,
        \OxidEsales\Eshop\Core\ShopControl::class => ShopControl::class,
        \OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration::class => ModuleConfiguration::class,
        \OxidEsales\Eshop\Application\Controller\Admin\OrderMain::class => OrderMain::class,
        \OxidEsales\Eshop\Application\Model\Article::class => Article::class,
    ],
    'controllers' => [
        'unzer_admin_order' => AdminOrderController::class,
        'unzer_dispatcher' => DispatcherController::class,
        'unzer_installment' => InstallmentController::class,
        'unzer_applepay_callback' => ApplePayCallbackController::class,
    ],
    'templates' => [
        // admin
        'oscunzer_order.tpl' => 'osc/unzer/views/admin/tpl/oscunzer_order.tpl',

        // frontend
        'modules/osc/unzer/unzer_assets.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_assets.tpl',
        'modules/osc/unzer/unzer_card.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_card.tpl',
        'modules/osc/unzer/unzer_eps_charge.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_eps_charge.tpl',
        'modules/osc/unzer/unzer_installment.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_installment.tpl',
        'modules/osc/unzer/unzer_installment_confirm.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_installment_confirm.tpl',
        'modules/osc/unzer/unzer_installment_confirm_flow.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_installment_confirm_flow.tpl',
        'modules/osc/unzer/unzer_installment_confirm_wave.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_installment_confirm_wave.tpl',
        'modules/osc/unzer/unzer_invoice.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_invoice.tpl',
        'modules/osc/unzer/unzer_applepay.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_applepay.tpl',
        'modules/osc/unzer/unzer_sepa.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_sepa.tpl',
        'modules/osc/unzer/unzer_sepa_secured.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_sepa_secured.tpl',
        'modules/osc/unzer/unzer_ideal.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_ideal.tpl',
        'modules/osc/unzer/unzer_shippingAndPayment_flow.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_shippingAndPayment_flow.tpl',
        'modules/osc/unzer/unzer_shippingAndPayment_wave.tpl' => 'osc/unzer/views/frontend/tpl/order/unzer_shippingAndPayment_wave.tpl',
        'modules/osc/unzer/message/js-errors.tpl' => 'osc/unzer/views/frontend/tpl/message/js-errors.tpl',
        'modules/osc/unzer/payment/applepay_availibility_check.tpl' => 'osc/unzer/views/frontend/tpl/payment/applepay_availibility_check.tpl',
        'modules/osc/unzer/payment/payment_unzer.tpl' => 'osc/unzer/views/frontend/tpl/payment/payment_unzer.tpl',
        'modules/osc/unzer/order/applepay_button.tpl' => 'osc/unzer/views/frontend/tpl/order/applepay_button.tpl',
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
            'template' => 'page/checkout/order.tpl',
            'block' => 'checkout_order_btn_submit_bottom',
            'file' => 'views/frontend/blocks/page/checkout/checkout_order_btn_submit_bottom.tpl'
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
            'file' => 'views/frontend/blocks/email/plain/email_plain_order_cust_paymentinfo.tpl'
        ],
        [
            'template' => 'email/html/order_cust.tpl',
            'block' => 'email_html_order_cust_paymentinfo',
            'file' => 'views/frontend/blocks/email/html/email_html_order_cust_paymentinfo.tpl'
        ],
    ],
    'settings' => [
        [
            'group' => 'unzerenvironment',
            'name' => 'UnzerSystemMode',
            'type' => 'select',
            'value' => '0',
            'constraints' => '0|1'
        ],
        [
            'group' => 'unzerenvironment',
            'name' => 'UnzerDebug',
            'type' => 'bool',
            'value' => '0',
        ],
        [
            'group' => 'unzermerchant',
            'name' => 'sandbox-UnzerPublicKey',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzermerchant',
            'name' => 'sandbox-UnzerPrivateKey',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzermerchant',
            'name' => 'production-UnzerPublicKey',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzermerchant',
            'name' => 'production-UnzerPrivateKey',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzermerchant',
            'name' => 'registeredWebhook',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzercard',
            'name' => 'UnzerOption_oscunzer_card',
            'type' => 'select',
            'value' => '0',
            'constraints' => '0|1'
        ],
        [
            'group' => 'unzerpaypal',
            'name' => 'UnzerOption_oscunzer_paypal',
            'type' => 'select',
            'value' => '0',
            'constraints' => '0|1'
        ],
        [
            'group' => 'unzerapplepay',
            'name' => 'UnzerOption_oscunzer_applepay',
            'type' => 'select',
            'value' => '0',
            'constraints' => '0|1'
        ],
        [
            'group' => 'unzerapplepay',
            'name' => 'applepay_merchant_capabilities',
            'type' => 'aarr',
            'value' => ModuleSettings::APPLE_PAY_MERCHANT_CAPABILITIES
        ],
        [
            'group' => 'unzerapplepay',
            'name' => 'applepay_networks',
            'type' => 'aarr',
            'value' => ModuleSettings::APPLE_PAY_NETWORKS
        ],
        [
            'group' => 'unzerapplepay',
            'name' => 'applepay_label',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerapplepay',
            'name' => 'sandbox-applepay_merchant_identifier',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerapplepay',
            'name' => 'sandbox-applepay_merchant_cert',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerapplepay',
            'name' => 'sandbox-applepay_merchant_cert_key',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerapplepay',
            'name' => 'production-applepay_merchant_identifier',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerapplepay',
            'name' => 'production-applepay_merchant_cert',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerapplepay',
            'name' => 'production-applepay_merchant_cert_key',
            'type' => 'str',
            'value' => ''
        ],
        // unzer invoice keypairs
        [
            'group' => 'unzerinvoice',
            'name' => 'sandbox-UnzerPublicKeyB2CEUR',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerinvoice',
            'name' => 'sandbox-UnzerPrivateKeyB2CEUR',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerinvoice',
            'name' => 'sandbox-UnzerPublicKeyB2BEUR',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerinvoice',
            'name' => 'sandbox-UnzerPrivateKeyB2BEUR',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerinvoice',
            'name' => 'sandbox-UnzerPublicKeyB2CCHF',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerinvoice',
            'name' => 'sandbox-UnzerPrivateKeyB2CCHF',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerinvoice',
            'name' => 'sandbox-UnzerPublicKeyB2BCHF',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerinvoice',
            'name' => 'sandbox-UnzerPrivateKeyB2BCHF',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerinvoice',
            'name' => 'production-UnzerPublicKeyB2CEUR',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerinvoice',
            'name' => 'production-UnzerPrivateKeyB2CEUR',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerinvoice',
            'name' => 'production-UnzerPublicKeyB2BEUR',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerinvoice',
            'name' => 'production-UnzerPrivateKeyB2BEUR',
            'type' => 'str',
            'value' => ''
        ],

        [
            'group' => 'unzerinvoice',
            'name' => 'production-UnzerPublicKeyB2CCHF',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerinvoice',
            'name' => 'production-UnzerPrivateKeyB2CCHF',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerinvoice',
            'name' => 'production-UnzerPublicKeyB2BCHF',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerinvoice',
            'name' => 'production-UnzerPrivateKeyB2BCHF',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => 'unzerother',
            'name' => 'UnzerjQuery',
            'type' => 'bool',
            'value' => '0',
        ],
        // this options are invisible because of missing group
        [
            'group' => '',
            'name' => 'registeredWebhookId',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => '',
            'name' => 'sandboxApplePayPaymentKeyId',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => '',
            'name' => 'sandboxApplePayPaymentCertificateId',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => '',
            'name' => 'productionApplePayPaymentKeyId',
            'type' => 'str',
            'value' => ''
        ],
        [
            'group' => '',
            'name' => 'productionApplePayPaymentCertificateId',
            'type' => 'str',
            'value' => ''
        ],
    ],
    'events' => [
        'onActivate' => '\OxidSolutionCatalysts\Unzer\Core\Events::onActivate',
        'onDeactivate' => '\OxidSolutionCatalysts\Unzer\Core\Events::onDeActivate',
    ],
];
