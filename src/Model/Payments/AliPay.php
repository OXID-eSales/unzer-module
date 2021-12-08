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

class AliPay extends UnzerPayment
{
    /**
     * @var string
     */
    protected $Paymentmethod = 'alipay';

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
        /* @var \UnzerSDK\Resources\PaymentTypes\Alipay $uzrAP */
        $uzrAP = $this->unzerSDK->createPaymentType(new \UnzerSDK\Resources\PaymentTypes\Alipay());

        $customer = $this->getCustomerData();

        if ($this->isDirectCharge()) {
            $transaction = $uzrAP->charge(
                $this->basket->getPrice()->getPrice(),
                $this->basket->getBasketCurrency()->name,
                UnzerHelper::redirecturl(self::PENDING_URL, true),
                $customer,
                $this->unzerOrderId,
                $this->getMetadata()
            );
        } else {
            $transaction = $uzrAP->authorize(
                $this->basket->getPrice()->getPrice(),
                $this->basket->getBasketCurrency()->name,
                UnzerHelper::redirecturl(self::PENDING_URL, true),
                $customer,
                $this->unzerOrderId,
                $this->getMetadata()
            );
        }

        $this->setSessionVars($transaction);
    }
}
