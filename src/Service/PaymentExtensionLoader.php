<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\AliPay;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\Bancontact;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\Card;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\CardRecurring;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\EPS;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\GiroPay;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\Ideal;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\Installment;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\Invoice;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\InvoiceSecured;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\PayPal;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\PIS;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\PrePayment;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\Przelewy24;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\Sepa;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\SepaSecured;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\Sofort;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\UnzerPayment as AbstractUnzerPayment;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\WeChatPay;

class PaymentExtensionLoader
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
    private $unzerSdkLoader;
    private $translator;
    private $unzerService;

    public function __construct(
        Session $session,
        UnzerSDKLoader $unzerSDKLoader,
        Translator $translator,
        Unzer $unzerService
    ) {
        $this->session = $session;
        $this->unzerSdkLoader = $unzerSDKLoader;
        $this->translator = $translator;
        $this->unzerService = $unzerService;
    }

    public function getPaymentExtension(PaymentModel $payment): AbstractUnzerPayment
    {
        return oxNew(
            self::UNZERCLASSNAMEMAPPING[$payment->getId()],
            $this->session,
            $this->unzerSdkLoader->getUnzerSDK(),
            $this->translator,
            $this->unzerService
        );
    }
}
