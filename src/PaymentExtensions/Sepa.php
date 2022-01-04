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

class Sepa extends UnzerPayment
{
    protected $paymentMethod = 'sepa-direct-debit';

    protected $allowedCurrencies = ['EUR'];

    protected $isRecurring = true;

    /**
     * @return void
     * @throws UnzerApiException
     * @throws Exception
     */
    public function execute()
    {
        $sId = $this->getUzrId();

        /** @var \UnzerSDK\Resources\PaymentTypes\SepaDirectDebit $uzrSepa */
        $uzrSepa = $this->unzerSDK->fetchPaymentType($sId);

        $customer = $this->unzerService->getSessionCustomerData();
        $basket = $this->session->getBasket();

        $transaction = $uzrSepa->charge(
            $basket->getPrice()->getPrice(),
            $basket->getBasketCurrency()->name,
            $this->unzerService->prepareRedirectUrl(self::CONTROLLER_URL),
            $customer,
            $this->unzerOrderId,
            $this->getMetadata()
        );

        $this->setSessionVars($transaction);
    }
}
