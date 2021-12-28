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

class PayPal extends UnzerPayment
{
    /**
     * @var string
     */
    protected $paymentMethod = 'paypal';

    /**
     * @return bool
     */
    public function isRecurringPaymentType(): bool
    {
        return true;
    }

    public function execute()
    {
        /** @var \UnzerSDK\Resources\PaymentTypes\Paypal $uzrPP */
        $uzrPP = $this->unzerSDK->createPaymentType(new \UnzerSDK\Resources\PaymentTypes\Paypal());

        $customer = $this->unzerService->getSessionCustomerData();
        $basket = $this->session->getBasket();

        if ($this->isDirectCharge()) {
            $transaction = $uzrPP->charge(
                $basket->getPrice()->getPrice(),
                $basket->getBasketCurrency()->name,
                $this->unzerService->prepareRedirectUrl(self::PENDING_URL, true),
                $customer,
                $this->unzerOrderId,
                $this->getMetadata()
            );
        } else {
            $transaction = $uzrPP->authorize(
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
