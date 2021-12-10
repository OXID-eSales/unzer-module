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

namespace OxidSolutionCatalysts\Unzer\PaymentExtensions;

use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;

class Przelewy24 extends UnzerPayment
{
    /**
     * @var string
     */
    protected $Paymentmethod = 'przelewy24';

    /**
     * @var array
     */
    protected $aCurrencies = [];

    /**
     * @return bool
     */
    public function isRecurringPaymentType(): bool
    {
        return false;
    }

    public function execute()
    {
        /** @var \UnzerSDK\Resources\PaymentTypes\Przelewy24 $uzrPrzelewy */
        $uzrPrzelewy = $this->unzerSDK->createPaymentType(new \UnzerSDK\Resources\PaymentTypes\Przelewy24());

        $customer = $this->getCustomerData();

        $transaction = $uzrPrzelewy->charge(
            $this->basket->getPrice()->getPrice(),
            $this->basket->getBasketCurrency()->name,
            UnzerHelper::redirecturl(self::PENDING_URL, true),
            $customer,
            $this->unzerOrderId,
            $this->getMetadata()
        );

        $this->setSessionVars($transaction);
    }
}
