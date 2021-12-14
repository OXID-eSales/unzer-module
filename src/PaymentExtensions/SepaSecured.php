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

class SepaSecured extends UnzerPayment
{
    /**
     * @var string
     */
    protected $Paymentmethod = 'sepa-direct-debit-secured';

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
        return false;
    }

    /**
     * @return void
     * @throws UnzerApiException
     * @throws Exception
     */
    public function execute()
    {
        $sId = $this->getUzrId();
        /** @var \UnzerSDK\Resources\PaymentTypes\SepaDirectDebitSecured $uzrSepa */
        $uzrSepa = $this->unzerSDK->fetchPaymentType($sId);

        $customer = $this->unzerService->getSessionCustomerData();
        $uzrBasket = $this->unzerService->getUnzerBasket($this->unzerOrderId, $this->basket);

        $transaction = $uzrSepa->charge(
            $this->basket->getPrice()->getPrice(),
            $this->basket->getBasketCurrency()->name,
            UnzerHelper::redirecturl(self::CONTROLLER_URL),
            $customer,
            $this->unzerOrderId,
            $this->getMetadata(),
            $uzrBasket
        );

        $this->setSessionVars($transaction);
    }
}