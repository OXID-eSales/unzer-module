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

class EPS extends UnzerPayment
{
    /**
     * @var string
     */
    protected $paymentMethod = 'eps';

    /**
     * @var array
     */
    protected $allowedCurrencies = [];

    /**
     * @return bool
     */
    public function isRecurringPaymentType(): bool
    {
        return false;
    }

    public function execute()
    {
        $sId = $this->getUzrId();

        /** @var \UnzerSDK\Resources\PaymentTypes\EPS $uzrEPS */
        $uzrEPS = $this->unzerSDK->fetchPaymentType($sId);

        $customer = $this->unzerService->getSessionCustomerData();
        $basket = $this->session->getBasket();

        $transaction = $uzrEPS->charge(
            $basket->getPrice()->getPrice(),
            $basket->getBasketCurrency()->name,
            UnzerHelper::redirecturl(self::PENDING_URL, true),
            $customer,
            null,
            $this->getMetadata()
        );

        $this->setSessionVars($transaction);
    }
}
