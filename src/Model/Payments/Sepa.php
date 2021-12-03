<?php

/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @copyright 2003-2021 OXID eSales AG
 * @author    OXID Solution Catalysts
 * @link      https://www.oxid-esales.com
 */

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\Traits\CanDirectCharge;
use Exception;

class Sepa extends UnzerPayment
{
    /**
     * @var string
     */
    protected $Paymentmethod = 'sepa-direct-debit';

    /**
     * @var array
     */
    protected $aCurrencies = ['EUR'];

    /**
     * @var string
     */
    protected $sIban;

    /**
     * @return string
     */
    public function getSIban(): string
    {
        return $this->sIban;
    }

    /**
     * @param string $sIban
     */
    public function setSIban(string $sIban): void
    {
        $this->sIban = $sIban;
    }

    /**
     * @return bool
     */
    public function isRecurringPaymentType(): bool
    {
        return true;
    }

    /**
     * @return void
     * @throws UnzerApiException
     * @throws Exception
     */
    public function execute()
    {
        $sId = $this->getUzrId();
        /* @var SepaDirectDebit|CanDirectCharge $uzrSepa */
        $uzrSepa = $this->unzerSDK->fetchPaymentType($sId);

        $customer = $this->getCustomerData();

        $transaction = $uzrSepa->charge(
            $this->basket->getPrice()->getPrice()
            , $this->basket->getBasketCurrency()->name
            , UnzerHelper::redirecturl(self::CONTROLLER_URL)
            , $customer
            , $this->unzerOrderId
            , $this->getMetadata());

        $this->setSessionVars($transaction);
    }
}
