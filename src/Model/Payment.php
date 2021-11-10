<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Resources\Customer;
use RuntimeException;
use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\Prepayment;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Unzer;

abstract class Payment
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
     * @param Customer
     * @param User
     */
    public function setCustomerData($customer, $oUser)
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
     * @return object|\OxidEsales\Eshop\Application\Model\Basket|null
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
