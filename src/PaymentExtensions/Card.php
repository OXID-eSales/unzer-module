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

use Exception;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Exceptions\UnzerApiException;

class Card extends UnzerPayment
{
    /**
     * @var string
     */
    protected $Paymentmethod = 'card';

    /**
     * @var array
     */
    protected $aCurrencies = [];

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
        /** @var \UnzerSDK\Resources\PaymentTypes\Card $uzrCard */
        $uzrCard = $this->unzerSDK->fetchPaymentType($sId);

        $customer = $this->unzerService->getSessionCustomerData();

        if ($this->isDirectCharge()) {
            $transaction = $uzrCard->charge(
                $this->basket->getPrice()->getPrice(),
                $this->basket->getBasketCurrency()->name,
                UnzerHelper::redirecturl(self::PENDING_URL, true),
                $customer,
                $this->unzerOrderId,
                $this->getMetadata()
            );
        } else {
            $transaction = $uzrCard->authorize(
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