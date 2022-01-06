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

use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class SepaSecured extends UnzerPayment
{
    protected $paymentMethod = 'sepa-direct-debit-secured';

    protected $allowedCurrencies = ['EUR'];

    public function execute(): bool
    {
        $uzrSepa = $this->getUnzerPaymentTypeObject();

        $customer = $this->unzerService->getUnzerCustomer($this->session->getUser());
        $basket = $this->session->getBasket();
        $uzrBasket = $this->unzerService->getUnzerBasket($this->unzerOrderId, $basket);

        $transaction = $uzrSepa->charge(
            $basket->getPrice()->getPrice(),
            $basket->getBasketCurrency()->name,
            $this->unzerService->prepareRedirectUrl(self::CONTROLLER_URL),
            $customer,
            $this->unzerOrderId,
            $this->getMetadata(),
            $uzrBasket
        );

        $this->unzerService->setSessionVars($transaction);

        return true;
    }

    /**
     * @return \UnzerSDK\Resources\PaymentTypes\SepaDirectDebitSecured
     */
    public function getUnzerPaymentTypeObject(): BasePaymentType
    {
        return $this->unzerSDK->fetchPaymentType(
            $this->unzerService->getUnzerPaymentIdFromRequest()
        );
    }
}
