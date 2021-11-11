<?php

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Resources\Customer;

abstract class Payment
{
    const CONTROLLER_URL = "order";
    const RETURN_CONTROLLER_URL = "order";
    const FAILURE_URL = "";
    const PENDING_URL = "order";
    const SUCCESS_URL = "thankyou";

    /**
     * @var mixed|\OxidEsales\Eshop\Application\Model\Payment
     */
    protected $_oPayment;

    public function __construct($oxpaymentid)
    {
        $oPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $oPayment->load($oxpaymentid);
        $this->_oPayment = $oPayment;
    }



    /**
     * @return string
     */
    abstract public function getPaymentMethod(): string;

    /**
     * @return string
     */
    abstract public function getPaymentCode(): string;

    /**
     * @return string
     */
    abstract public function getSyncMode(): string;

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->_oPayment->getId();
    }

    /**
     * @return string
     */
    public function getPaymentProcedure(): string
    {
        return $this->_oPayment->oxpayment__oxpaymentprocedure->value;
    }

    /**
     * @return mixed
     */
    abstract public function validate();

    /**
     * @param Customer $customer
     * @param User $oUser
     */
    public function setCustomerData(Customer $customer, User $oUser)
    {
        $customer->setBirthDate(date('Y-m-d', $oUser->oxuser__oxbirthdate->value));
        $customer->setCompany($oUser->oxuser__oxcompany->value);
        $customer->setSalutation($oUser->oxuser__oxsal->value);
        $customer->setEmail($oUser->oxuser__oxusername->value);
        $customer->setPhone($oUser->oxuser__oxfon->value);
    }

    /**
     * @return false|User|null
     */
    public function getUser()
    {
        $oSession = Registry::getSession();
        return $oSession->getUser();
    }

    /**
     * @return object|Basket|null
     */
    public function getBasket()
    {
        $oSession = Registry::getSession();
        return $oSession->getBasket();
    }

    /**
     * @return mixed|UnzerHelper
     */
    public function getUnzerHelper()
    {
        return oxNew(UnzerHelper::class);
    }
}
