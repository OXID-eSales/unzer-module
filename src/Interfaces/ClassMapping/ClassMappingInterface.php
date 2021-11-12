<?php

namespace OxidSolutionCatalysts\Unzer\Interfaces\ClassMapping;

use OxidSolutionCatalysts\Unzer\Model\Payments\AliPay;
use OxidSolutionCatalysts\Unzer\Model\Payments\ApplePay;
use OxidSolutionCatalysts\Unzer\Model\Payments\Card;
use OxidSolutionCatalysts\Unzer\Model\Payments\EPS;
use OxidSolutionCatalysts\Unzer\Model\Payments\GiroPay;
use OxidSolutionCatalysts\Unzer\Model\Payments\Ideal;
use OxidSolutionCatalysts\Unzer\Model\Payments\Installment;
use OxidSolutionCatalysts\Unzer\Model\Payments\InvoiceSecured;
use OxidSolutionCatalysts\Unzer\Model\Payments\Invoice;
use OxidSolutionCatalysts\Unzer\Model\Payments\PayPal;
use OxidSolutionCatalysts\Unzer\Model\Payments\PIS;
use OxidSolutionCatalysts\Unzer\Model\Payments\PostFinance;
use OxidSolutionCatalysts\Unzer\Model\Payments\PrePayment;
use OxidSolutionCatalysts\Unzer\Model\Payments\Przelewy24;
use OxidSolutionCatalysts\Unzer\Model\Payments\Sepa;
use OxidSolutionCatalysts\Unzer\Model\Payments\SepaSecured;
use OxidSolutionCatalysts\Unzer\Model\Payments\Sofort;
use UnzerSDK\Resources\PaymentTypes\Wechatpay;

/**
 * Interface ConstantInterface
 */
interface ClassMappingInterface
{
    const UNZERCLASSNAMEMAPPING = [
        'oscunzer_invoice' => Invoice::class,
        'oscunzer_invoice-secured' => InvoiceSecured::class,
        'oscunzer_card' => Card::class,
        'oscunzer_sepa' => Sepa::class,
        'oscunzer_sepa-secured' => SepaSecured::class,
        'oscunzer_sofort' => Sofort::class,
        'oscunzer_giropay' => GiroPay::class,
        'oscunzer_ideal' => Ideal::class,
        'oscunzer_prepayment' => PrePayment::class,
        'oscunzer_banktransfer' => PIS::class,
        'oscunzer_eps' => EPS::class,
        'oscunzer_post-finance' => PostFinance::class,
        'oscunzer_applepay' => ApplePay::class,
        'oscunzer_installment' => Installment::class,
        'oscunzer_paypal' => PayPal::class,
        'oscunzer_przelewy24' => Przelewy24::class,
        'oscunzer_wechatpay' => Wechatpay::class,
        'oscunzer_alipay' => AliPay::class,
    ];
}
