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
use UnzerSDK\Exceptions\UnzerApiException;

class Card extends UnzerPayment
{
    protected $paymentMethod = 'card';

    protected $isRecurring = true;

    /**
     * @return void
     * @throws UnzerApiException
     * @throws Exception
     */
    public function execute()
    {
        $sId = $this->unzerService->getUnzerPaymentIdFromRequest();
        /** @var \UnzerSDK\Resources\PaymentTypes\Card $uzrCard */
        $uzrCard = $this->unzerSDK->fetchPaymentType($sId);

        $customer = $this->unzerService->getSessionCustomerData();
        $basket = $this->session->getBasket();

        if ($this->isDirectCharge()) {
            $transaction = $uzrCard->charge(
                $basket->getPrice()->getPrice(),
                $basket->getBasketCurrency()->name,
                $this->unzerService->prepareRedirectUrl(self::PENDING_URL, true),
                $customer,
                $this->unzerOrderId,
                $this->getMetadata()
            );
        } else {
            $transaction = $uzrCard->authorize(
                $basket->getPrice()->getPrice(),
                $basket->getBasketCurrency()->name,
                $this->unzerService->prepareRedirectUrl(self::PENDING_URL, true),
                $customer,
                $this->unzerOrderId,
                $this->getMetadata()
            );
        }
        $this->setSessionVars($transaction);
    }
}
