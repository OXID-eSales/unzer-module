<?php

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

use OxidEsales\Eshop\Application\Model\User;
use UnzerSDK\Resources\Customer;

abstract class UnzerPayment
{
    const CONTROLLER_URL = "order";
    const RETURN_CONTROLLER_URL = "order";
    const FAILURE_URL = "";
    const PENDING_URL = "order";
    const SUCCESS_URL = "thankyou";

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
    abstract public function getID(): string;

    /**
     * @return string
     */
    abstract public function getPaymentProcedure(): string;

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
}

