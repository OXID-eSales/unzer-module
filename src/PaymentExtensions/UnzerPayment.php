<?php

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use Exception;
use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidEsales\Facts\Facts;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\Unzer as UnzerService;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use UnzerSDK\Unzer;

abstract class UnzerPayment
{
    public const CONTROLLER_URL = "order";
    public const RETURN_CONTROLLER_URL = "order";
    public const FAILURE_URL = "";
    public const PENDING_URL = "order&fnc=unzerExecuteAfterRedirect&uzrredirect=1";
    public const SUCCESS_URL = "thankyou";

    /** @var PaymentModel */
    protected $payment;

    /** @var Session */
    protected $session;

    /** @var Unzer */
    protected $unzerSDK;

    /** @var Translator */
    protected $translator;

    /** @var UnzerService */
    protected $unzerService;

    /** @var string */
    protected $unzerOrderId;

    /** @var string */
    protected $paymentMethod;

    /** @var bool */
    protected $isRecurring = false;

    /** @var array */
    protected $allowedCurrencies = [];

    public function __construct(
        PaymentModel $payment,
        Session $session,
        Unzer $unzerSDK,
        Translator $translator,
        UnzerService $unzerService
    ) {
        $this->payment = $payment;
        $this->session = $session;
        $this->unzerSDK = $unzerSDK;
        $this->translator = $translator;
        $this->unzerService = $unzerService;

        $this->unzerOrderId = 'o' . str_replace(['0.', ' '], '', microtime(false));
    }

    public function getPaymentCurrencies(): array
    {
        return $this->allowedCurrencies;
    }

    public function isRecurringPaymentType(): bool
    {
        return $this->isRecurring;
    }

    public function getUnzerPaymentTypeObject(): BasePaymentType
    {
        throw new \Exception('Payment method not implemented yet');
    }

    /**
     * @throws UnzerApiException
     * @throws Exception
     */
    public function execute(): bool
    {
        $paymentType = $this->getUnzerPaymentTypeObject();

        $customer = $this->unzerService->getSessionCustomerData();
        $basket = $this->session->getBasket();

        $paymentProcedure = $this->unzerService->getPaymentProcedure($this->paymentMethod);

        $transaction = $paymentType->{$paymentProcedure}(
            $basket->getPrice()->getPrice(),
            $basket->getBasketCurrency()->name,
            $this->unzerService->prepareRedirectUrl(self::PENDING_URL, true),
            $customer,
            $this->unzerOrderId,
            $this->getMetadata()
        );

        $this->setSessionVars($transaction);

        return true;
    }

    /**
     * @return bool
     */
    public function checkPaymentStatus(): bool
    {
        $result = false;

        if (!$paymentId = $this->session->getVariable('PaymentId')) {
            throw new Exception("Something went wrong. Please try again later.");
        }

        // Redirect to success if the payment has been successfully completed.
        $unzerPayment = $this->unzerSDK->fetchPayment($paymentId);
        if ($transaction = $unzerPayment->getInitialTransaction()) {
            if ($transaction->isSuccess()) {
                $result = true;
            } elseif ($transaction->isPending()) {
                $this->createPaymentStatusWebhook($paymentId);

                if ($redirectUrl = $transaction->getRedirectUrl()) {
                    throw new Redirect($redirectUrl);
                }
                $result = true;
            } elseif ($transaction->isError()) {
                throw new Exception($this->translator->translateCode(
                    $transaction->getMessage()->getCode(),
                    "Error in transaction for customer " . $transaction->getMessage()->getCustomer()
                ));
            }
        }

        return $result;
    }

    /**
     * @param string $paymentId
     */
    public function createPaymentStatusWebhook(string $paymentId): void
    {
        $webhookUrl = Registry::getConfig()->getShopUrl()
            . 'index.php?cl=unzer_dispatcher&fnc=updatePaymentTransStatus&paymentid='
            . $paymentId;

        $this->unzerSDK->createWebhook($webhookUrl, 'payment');
    }

    public function setSessionVars(AbstractTransactionType $charge): void
    {
        // You'll need to remember the shortId to show it on the success or failure page
        $this->session->setVariable('ShortId', $charge->getShortId());
        $this->session->setVariable('PaymentId', $charge->getPaymentId());

        $paymentType = $charge->getPayment()->getPaymentType();

        if (!$paymentType) {
            return;
        }

        // TODO: $charge is not only class of Charge possible here. Investigate and fix.
        if ($paymentType instanceof \UnzerSDK\Resources\PaymentTypes\Prepayment || $paymentType->isInvoiceType()) {
            $this->session->setVariable(
                'additionalPaymentInformation',
                $this->unzerService->getBankDataFromCharge($charge)
            );
        }
    }

    /**
     * @return Metadata
     * @throws Exception
     */
    public function getMetadata(): Metadata
    {
        $metadata = new Metadata();
        $metadata->setShopType("Oxid eShop " . (new Facts())->getEdition());
        $metadata->setShopVersion(ShopVersion::getVersion());
        $metadata->addMetadata('shopid', (string)Registry::getConfig()->getShopId());
        $metadata->addMetadata('paymentmethod', $this->paymentMethod);
        $metadata->addMetadata('paymentprocedure', $this->unzerService->getPaymentProcedure($this->paymentMethod));

        return $metadata;
    }
}
