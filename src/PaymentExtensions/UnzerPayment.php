<?php

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use Exception;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\Facts\Facts;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\Unzer as UnzerService;
use Psr\Container\ContainerInterface;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Unzer;

abstract class UnzerPayment
{
    public const CONTROLLER_URL = "order";
    public const RETURN_CONTROLLER_URL = "order";
    public const FAILURE_URL = "";
    public const PENDING_URL = "order&fnc=unzerExecuteAfterRedirect&uzrredirect=1";
    public const SUCCESS_URL = "thankyou";

    /** @var Payment */
    protected $payment;

    /** @var Session */
    protected $session;

    /** @var User */
    protected $user;

    /** @var \OxidEsales\Eshop\Application\Model\Basket */
    protected $basket;

    /** @var Unzer */
    protected $unzerSDK;

    /** @var Translator */
    protected $translator;

    /** @var UnzerService */
    protected $unzerService;

    /** @var string */
    protected $unzerOrderId;

    /** @var string */
    protected $Paymentmethod;

    /** @var null|array */
    protected $aPaymentParams = null;

    /** @var array */
    protected $aCurrencies;

    /**
     * @return mixed|void
     * @throws Exception
     * @throws UnzerApiException
     */
    abstract public function execute();

    /**
     * @var AbstractTransactionType|null
     */
    protected $transaction;

    public function __construct(
        Payment $payment,
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
        $this->user = $this->session->getUser();
        $this->basket = $this->session->getBasket();
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->payment->getId();
    }

    /**
     * @return array|bool
     */
    public function getPaymentCurrencies()
    {
        return $this->aCurrencies;
    }

    /**
     * @return bool
     */
    public function isPaymentTypeAllowed(): bool
    {
        if (
            is_array($this->getPaymentCurrencies()) &&
            (
                !count($this->getPaymentCurrencies()) ||
                in_array(Registry::getConfig()->getActShopCurrencyObject()->name, $this->getPaymentCurrencies())
            )
        ) {
            return true;
        }

        if (
            !$this->getPaymentCurrencies()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getPaymentProcedure(): string
    {
        /** @var ModuleSettings $settings */
        $settings = $this->getContainer()->get(ModuleSettings::class);

        $paymentid = $this->payment->getId();

        if ($paymentid == "oscunzer_paypal" || $paymentid == "oscunzer_card") {
            return $settings->getPaymentProcedureSetting($paymentid);
        }

        return $settings::PAYMENT_DIRECT;
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
            $this->transaction = $unzerPayment->getInitialTransaction();
            if ($this->transaction->isSuccess()) {
                // TODO log success
                //$msg = UnzerHelper::translatedMsg(
                //  $this->transaction->getMessage()->getCode(),
                //  $this->transaction->getMessage()->getCustomer()
                //);
                $result = true;
            } elseif ($this->transaction->isPending()) {
                // TODO Handle Pending...
                $paymentType = $unzerPayment->getPaymentType();

                //creating webhook
                //$this->unzerSDK->createWebhook(Registry::getConfig()->getShopUrl().'index.php?cl=unzer_dispatcher&fnc=updatePaymentTransStatus&paymentid='.$paymentId,'payment');

                if (!$blDoRedirect && $this->transaction->getRedirectUrl()) {
                    Registry::getUtils()->redirect($this->transaction->getRedirectUrl(), false);
                    exit;
                }
                $result = true;
            } elseif ($this->transaction->isError()) {
                UnzerHelper::redirectOnError(
                    self::CONTROLLER_URL,
                    $this->translator->translate(
                        $this->transaction->getMessage()->getCode(),
                        $this->transaction->getMessage()->getCustomer()
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
     * @param Charge $transaction
     */
    public function setSessionVars(Charge $transaction): void
    {
        // You'll need to remember the shortId to show it on the success or failure page
        $this->session->setVariable('ShortId', $transaction->getShortId());
        $this->session->setVariable('PaymentId', $transaction->getPaymentId());

        $paymentType = $transaction->getPayment()->getPaymentType();
        if ($paymentType instanceof \UnzerSDK\Resources\PaymentTypes\Prepayment || $paymentType->isInvoiceType()) {
            $this->session->setVariable('additionalPaymentInformation', $this->getBankData($transaction));
        }
    }

    /**
     * @param Charge $transaction
     * @return string
     */
    protected function getBankData(Charge $transaction): string
    {
        $amount = Registry::getLang()->formatCurrency($transaction->getAmount());

        $bankData = sprintf(
            Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_AMOUNT'),
            $amount,
            Registry::getConfig()->getActShopCurrencyObject()->sign
        );

        $bankData .= sprintf(
            Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_HOLDER'),
            $transaction->getHolder()
        );

        $bankData .= sprintf(
            Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_IBAN'),
            $transaction->getIban()
        );

        $bankData .= sprintf(
            Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_BIC'),
            $transaction->getBic()
        );

        $bankData .= sprintf(
            Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_DESCRIPTOR'),
            $transaction->getDescriptor()
        );

        return $bankData;
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
        $metadata->addMetadata('paymentmethod', $this->Paymentmethod);
        $metadata->addMetadata('paymentprocedure', $this->getPaymentProcedure());

        return $metadata;
    }

    /**
     *
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return ContainerFactory::getInstance()->getContainer();
    }
}
