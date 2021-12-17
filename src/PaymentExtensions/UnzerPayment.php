<?php

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use Exception;
use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidEsales\Facts\Facts;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\Unzer as UnzerService;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\TransactionTypes\Charge;
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

    /** @var null|array */
    protected $aPaymentParams = null;

    /** @var array */
    protected $allowedCurrencies;

    /**
     * @return mixed|void
     * @throws Exception
     * @throws UnzerApiException
     */
    abstract public function execute();

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

    /**
     * @return array|bool
     */
    public function getPaymentCurrencies()
    {
        return $this->allowedCurrencies;
    }

    /**
     * @return bool
     */
    public function isDirectCharge()
    {
        return (strpos($this->payment->oxpayments__oxpaymentprocedure->value, "direct Capture") !== false);
    }

    /**
     * @return   string|void
     */
    public function getUzrId()
    {
        if (array_key_exists('id', $this->getPaymentParams())) {
            return $this->getPaymentParams()['id'];
        }

        UnzerHelper::redirectOnError(
            'order',
            $this->translator->translate('WRONGPAYMENTID', 'Ungültige ID')
        );
    }

    public function getPaymentParams()
    {
        if ($this->aPaymentParams == null) {
            $jsonobj = Registry::getRequest()->getRequestParameter('paymentData');
            $this->aPaymentParams = json_decode($jsonobj, true);
        }
        return $this->aPaymentParams;
    }

    /**
     * @return bool
     */
    public function checkPaymentstatus($blDoRedirect = false): bool
    {
        $result = false;

        if (!$paymentId = $this->session->getVariable('PaymentId')) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, "Something went wrong. Please try again later.");
        }

        // Catch API errors, write the message to your log and show the ClientMessage to the client.
        try {
            // Redirect to success if the payment has been successfully completed.
            $unzerPayment = $this->unzerSDK->fetchPayment($paymentId);
            $transaction = $unzerPayment->getInitialTransaction();
            if ($transaction->isSuccess()) {
                // TODO log success
                //$msg = UnzerHelper::translatedMsg(
                //  $transaction->getMessage()->getCode(),
                //  $transaction->getMessage()->getCustomer()
                //);
                $result = true;
            } elseif ($transaction->isPending()) {
                // TODO Handle Pending...
                $paymentType = $unzerPayment->getPaymentType();

                if (!$blDoRedirect && $transaction->getRedirectUrl()) {
                    Registry::getUtils()->redirect($transaction->getRedirectUrl(), false);
                    exit;
                }
                $result = true;
            } elseif ($transaction->isError()) {
                UnzerHelper::redirectOnError(
                    self::CONTROLLER_URL,
                    $this->translator->translate(
                        $transaction->getMessage()->getCode(),
                        $transaction->getMessage()->getCustomer()
                    )
                );
            }
        } catch (UnzerApiException $e) {
            UnzerHelper::redirectOnError(
                self::CONTROLLER_URL,
                $this->translator->translate((string)$e->getCode(), $e->getClientMessage())
            );
        } catch (\RuntimeException $e) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, $e->getMessage());
        }
        return $result;
    }

    /**
     * @param Charge $charge
     */
    public function setSessionVars(Charge $charge): void
    {
        // You'll need to remember the shortId to show it on the success or failure page
        $this->session->setVariable('ShortId', $charge->getShortId());
        $this->session->setVariable('PaymentId', $charge->getPaymentId());

        $paymentType = $charge->getPayment()->getPaymentType();
        if ($paymentType instanceof \UnzerSDK\Resources\PaymentTypes\Prepayment || $paymentType->isInvoiceType()) {
            $this->session->setVariable('additionalPaymentInformation', $this->unzerService->getBankDataFromCharge($charge));
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
        $metadata->addMetadata('paymentprocedure', $this->unzerService->getPaymentProcedure($this->payment->getId()));

        return $metadata;
    }
}
