<?php

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Resources\Customer;
use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\CustomerFactory;


abstract class UnzerPayment
{
    const CONTROLLER_URL = "order";
    const RETURN_CONTROLLER_URL = "order";
    const FAILURE_URL = "";
    const PENDING_URL = "order";
    const SUCCESS_URL = "thankyou";

    /**
     * @var Payment
     */
    protected Payment $oPayment;


    /**
     * @var null|array
     */
    protected $aPaymentParams = null;

    /**
     * @param string $oxpaymentid
     */
    abstract public function __construct(string $oxpaymentid);

    /**
     * @return string
     */
    abstract public function getID(): string;

    /**
     * @return string
     */
    abstract public function getPaymentProcedure(): string;

    /**
     * @return mixed|void
     */
    abstract public function execute();

    /**
     * @param User $oUser
     *
     * @return Customer
     */
    public function getCustomerData(User $oUser, Order $oOrder = null)
    {
        $customer = CustomerFactory::createCustomer($oUser->oxuser__oxfname->value, $oUser->oxuser__oxlname->value);
        if ($oUser->oxuser__oxbirthdate->value != "'0000-00-00'") {
            $customer->setBirthDate(date('Y-m-d', $oUser->oxuser__oxbirthdate->value));
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
        if ($oUser->oxuser__oxsal->value) {
            $customer->setSalutation($oUser->oxuser__oxsal->value);
        }

        $billingAddress = $customer->getBillingAddress();
        $oBillCountry = $oUser->getUserCountry();

        if ($oBillCountry->oxcountry__oxtitle->value) {
            $billingAddress->setCountry($oBillCountry->oxcountry__oxtitle->value);
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

            if ($oDelAddress->oxaddress__oxcountry->value) {
                $shippingAddress->setCountry($oDelAddress->oxaddress__oxcountry->value);
            }
        }

        return $customer;
    }

    public function checkPaymentstatus()
    {
        if (!$paymentId = Registry::getSession()->getVariable('PaymentId')) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, "Something went wrong. Please try again later.");
        }

        // Catch API errors, write the message to your log and show the ClientMessage to the client.
        try {
            // Create an Unzer object using your private key and register a debug handler if you want to.
            $unzer = UnzerHelper::getUnzer();
            $unzer->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

            // Redirect to success if the payment has been successfully completed.
            $payment = $unzer->fetchPayment($paymentId);
            $transaction = $payment->getInitialTransaction();
            if ($transaction->isSuccess()) {
                // TODO log success
                return true;
            } elseif ($transaction->isPending()) {
                // TODO Handle Pending...
                return false;
            } elseif ($transaction->isError()) {
                // TODO Handle Error
                return false;
            }
        } catch (UnzerApiException | \RuntimeException $e) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, $e->getMessage());
        }
    }
}
