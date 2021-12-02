<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\Model\Payments\AliPay;
use OxidSolutionCatalysts\Unzer\Model\Payments\Bancontact;
use OxidSolutionCatalysts\Unzer\Model\Payments\Card;
use OxidSolutionCatalysts\Unzer\Model\Payments\CardRecurring;
use OxidSolutionCatalysts\Unzer\Model\Payments\EPS;
use OxidSolutionCatalysts\Unzer\Model\Payments\GiroPay;
use OxidSolutionCatalysts\Unzer\Model\Payments\Ideal;
use OxidSolutionCatalysts\Unzer\Model\Payments\Installment;
use OxidSolutionCatalysts\Unzer\Model\Payments\Invoice;
use OxidSolutionCatalysts\Unzer\Model\Payments\InvoiceSecured;
use OxidSolutionCatalysts\Unzer\Model\Payments\PayPal;
use OxidSolutionCatalysts\Unzer\Model\Payments\PIS;
use OxidSolutionCatalysts\Unzer\Model\Payments\PrePayment;
use OxidSolutionCatalysts\Unzer\Model\Payments\Przelewy24;
use OxidSolutionCatalysts\Unzer\Model\Payments\Sepa;
use OxidSolutionCatalysts\Unzer\Model\Payments\SepaSecured;
use OxidSolutionCatalysts\Unzer\Model\Payments\Sofort;
use OxidSolutionCatalysts\Unzer\Model\Payments\UnzerPayment as AbstractUnzerPayment;
use OxidSolutionCatalysts\Unzer\Model\Payments\WeChatPay;

class UnzerPaymentLoader
{
    public const UNZERCLASSNAMEMAPPING = [
        'oscunzer_alipay' => AliPay::class,
        'oscunzer_bancontact' => Bancontact::class,
        'oscunzer_card' => Card::class,
        'oscunzer_cardrecurring' => CardRecurring::class,
        'oscunzer_eps' => EPS::class,
        'oscunzer_giropay' => GiroPay::class,
        'oscunzer_ideal' => Ideal::class,
        'oscunzer_installment' => Installment::class,
        'oscunzer_invoice' => Invoice::class,
        'oscunzer_invoice-secured' => InvoiceSecured::class,
        'oscunzer_paypal' => PayPal::class,
        'oscunzer_pis' => PIS::class,
        'oscunzer_prepayment' => PrePayment::class,
        'oscunzer_przelewy24' => Przelewy24::class,
        'oscunzer_sepa' => Sepa::class,
        'oscunzer_sepa-secured' => SepaSecured::class,
        'oscunzer_sofort' => Sofort::class,
        'oscunzer_wechatpay' => WeChatPay::class,
    ];

    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function getUnzerPayment(PaymentModel $payment): AbstractUnzerPayment
    {
        return oxNew(self::UNZERCLASSNAMEMAPPING[$payment->getId()], $payment, $this->session);
    }
}