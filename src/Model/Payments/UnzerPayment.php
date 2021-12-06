<?php

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use UnzerSDK\Unzer;

abstract class UnzerPayment
{
    protected const CONTROLLER_URL = "order";
    protected const RETURN_CONTROLLER_URL = "order";
    protected const FAILURE_URL = "";
    protected const PENDING_URL = "order&fnc=unzerExecuteAfterRedirect&uzrredirect=1";
    protected const SUCCESS_URL = "thankyou";

    /**
     * @var Payment
     */
    protected $payment;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Unzer
     */
    protected $unzerSDK;

    /**
     * @var string
     */
    protected $Paymentmethod;

    /**
     * @var null|array
     */
    protected $aPaymentParams = null;

    /**
     * @var array|bool
     */
    protected $aCurrencies = false;

    public function __construct(
        Payment $payment,
        Session $session,
        Unzer $unzerSDK
    ) {
        $this->payment = $payment;
        $this->session = $session;
        $this->unzerSDK = $unzerSDK;
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->payment->getId();
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->Paymentmethod;
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

        return false;
    }

    /**
     * @return string
     */
    public function getPaymentProcedure(): string
    {
        return $this->payment->oxpayments__oxpaymentprocedure->value;
    }

    /**
     * @return bool
     */
    public function isDirectCharge()
    {
        return (strpos($this->payment->oxpayments__oxpaymentprocedure->value, "direct Capture") !== false);
    }

    /**
     * @return mixed|void
     */
    abstract public function execute();

    /**
     * @var AbstractTransactionType|null
     */
    protected ?AbstractTransactionType $transaction;

    /**
     * @return   string|void
     */
    public function getUzrId()
    {
        if (array_key_exists('id', $this->getPaymentParams())) {
            return $this->getPaymentParams()['id'];
        } else {
            UnzerHelper::redirectOnError('order', UnzerHelper::translatedMsg('WRONGPAYMENTID', 'Ungültige ID'));
        }
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
     * @param \OxidEsales\Eshop\Application\Model\Basket|null $oBasket
     * @param $orderId
     * @return Basket
     */
    public function getUnzerBasket(?\OxidEsales\Eshop\Application\Model\Basket $oBasket, $orderId): Basket
    {
        $basket = new Basket($orderId, $oBasket->getNettoSum(), $oBasket->getBasketCurrency()->name);

        $basketContents = $oBasket->getContents();

        $aBasketItems = $basket->getBasketItems();
        /**
         * @var string $sBasketItemKey
         * @var \OxidEsales\Eshop\Application\Model\BasketItem $oBasketItem
         */
        foreach ($basketContents as $oBasketItem) {
            $aBasketItems[] = new BasketItem(
                $oBasketItem->getTitle(),
                $oBasketItem->getPrice()->getNettoPrice(),
                $oBasketItem->getUnitPrice()->getNettoPrice(),
                (int)$oBasketItem->getAmount()
            );
        }

        $basket->setBasketItems($aBasketItems);

        return $basket;
    }

    /**
     * @param User $oUser
     * @param Order|null $oOrder
     * @return Customer
     */
    public function getCustomerData(User $oUser, Order $oOrder = null): Customer
    {
        $customer = CustomerFactory::createCustomer($oUser->oxuser__oxfname->value, $oUser->oxuser__oxlname->value);
        if ($oUser->oxuser__oxbirthdate->value != "0000-00-00") {
            $customer->setBirthDate($oUser->oxuser__oxbirthdate->value);
        }
        if ($oUser->oxuser__oxcompany->value) {
            $customer->setCompany($oUser->oxuser__oxcompany->value);
        }
        if ($oUser->oxuser__oxsal->value) {
            $customer->setSalutation($oUser->oxuser__oxsal->value);
        }
        if ($oUser->oxuser__oxusername->value) {
            $customer->setEmail($oUser->oxuser__oxusername->value);
        }
        if ($oUser->oxuser__oxfon->value) {
            $customer->setPhone($oUser->oxuser__oxfon->value);
        }

        $billingAddress = $customer->getBillingAddress();

        $oCountry = oxNew(Country::class);
        if ($oCountry->load($oUser->oxuser__oxcountryid->value)) {
            $billingAddress->setCountry($oCountry->oxcountry__oxisoalpha2->value);
        }
        if ($oUser->oxuser__oxcompany->value) {
            $billingAddress->setName($oUser->oxuser__oxcompany->value);
        } else {
            $billingAddress->setName($oUser->oxuser__oxfname->value . ' ' . $oUser->oxuser__oxlname->value);
        }

        if ($oUser->oxuser__oxcity->value) {
            $billingAddress->setCity(trim($oUser->oxuser__oxcity->value));
        }
        if ($oUser->oxuser__oxstreet->value) {
            $billingAddress->setStreet($oUser->oxuser__oxstreet->value . ($oUser->oxuser__oxstreetnr->value !== '' ? ' ' . $oUser->oxuser__oxstreetnr->value : ''));
        }
        if ($oUser->oxuser__oxzip->value) {
            $billingAddress->setZip($oUser->oxuser__oxzip->value);
        }
        if ($oUser->oxuser__oxmobfon->value) {
            $customer->setMobile($oUser->oxuser__oxmobfon->value);
        }
        if ($oOrder !== null) {
            $oDelAddress = $oOrder->getDelAddressInfo();
            $shippingAddress = $customer->getShippingAddress();

            if ($oDelAddress->oxaddress__oxcompany->value) {
                $shippingAddress->setName($oDelAddress->oxaddress__oxcompany->value);
            } else {
                $shippingAddress->setName($oDelAddress->oxaddress__oxfname->value . ' ' . $oDelAddress->oxaddress__oxlname->value);
            }

            if ($oDelAddress->oxaddress__oxstreet->value) {
                $shippingAddress->setStreet($oDelAddress->oxaddress__oxstreet->value . ($oDelAddress->oxaddress__oxstreetnr->value !== '' ? ' ' . $oDelAddress->oxaddress__oxstreetnr->value : ''));
            }

            if ($oDelAddress->oxaddress__oxstreet->value) {
                $shippingAddress->setCity($oDelAddress->oxaddress__oxstreet->value);
            }

            if ($oDelAddress->oxaddress__oxzip->value) {
                $shippingAddress->setZip($oDelAddress->oxaddress__oxzip->value);
            }

            $oCountry = oxNew(Country::class);
            if ($oCountry->load($oDelAddress->oxaddress__oxcountryid->value)) {
                $oCountry->load($oDelAddress->oxaddress__oxcountryid->value);

                $billingAddress->setCountry($oCountry->oxcountry__oxisoalpha2->value);
            }
        }

        return $customer;
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
            $unzer = $this->unzerSDK;
            // Create an Unzer object using your private key and register a debug handler if you want to.

            // Redirect to success if the payment has been successfully completed.
            $unzerPayment = $unzer->fetchPayment($paymentId);
            $this->transaction = $unzerPayment->getInitialTransaction();
            if ($this->transaction->isSuccess()) {
                // TODO log success
                //$msg = UnzerHelper::translatedMsg($this->transaction->getMessage()->getCode(), $this->transaction->getMessage()->getCustomer());
                $result = true;
            } elseif ($this->transaction->isPending()) {
                // TODO Handle Pending...
                $paymentType = $unzerPayment->getPaymentType();
                if ($paymentType instanceof \UnzerSDK\Resources\PaymentTypes\Prepayment || $paymentType->isInvoiceType() || $paymentType instanceof \UnzerSDK\Resources\PaymentTypes\Card) {
                    if (!$blDoRedirect && $this->transaction->getRedirectUrl()) {
                        Registry::getUtils()->redirect($this->transaction->getRedirectUrl(), false);
                        exit;
                    }
                    $result = true;
                }
            } elseif ($this->transaction->isError()) {
                UnzerHelper::redirectOnError(self::CONTROLLER_URL, UnzerHelper::translatedMsg($this->transaction->getMessage()->getCode(), $this->transaction->getMessage()->getCustomer()));
            }
        } catch (UnzerApiException $e) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, UnzerHelper::translatedMsg($e->getCode(), $e->getClientMessage()));
        } catch (\RuntimeException $e) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, $e->getMessage());
        }
        return $result;
    }
}
