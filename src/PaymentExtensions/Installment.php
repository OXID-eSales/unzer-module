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

class Installment extends UnzerPayment
{
    protected $paymentMethod = 'installment-secured';

    public function execute()
    {
        $sId = $this->unzerService->getUnzerPaymentIdFromRequest();
        /** @var \UnzerSDK\Resources\PaymentTypes\InstallmentSecured $uzrInstall */
        $uzrInstall = $this->unzerSDK->fetchPaymentType($sId);

        $customer = $this->unzerService->getSessionCustomerData();
        $basket = $this->session->getBasket();

        $transaction = $uzrInstall->authorize(
            $basket->getPrice()->getPrice(),
            $basket->getBasketCurrency()->name,
            $this->unzerService->prepareRedirectUrl(self::PENDING_URL, true),
            $customer,
            $this->unzerOrderId,
            $this->getMetadata()
        );

        $this->setSessionVars($transaction);
    }
}
