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

use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\Traits\CanDirectCharge;

class Card extends UnzerPayment
{
    /**
     * @var string
     */
    protected $Paymentmethod = 'card';

    /**
     * @var array|bool
     */
    protected $aCurrencies = [];

    /**
     * @return bool
     */
    public function isRecurringPaymentType(): bool
    {
        return true;
    }

    public function execute()
    {
        try {
            $oUnzer = $this->unzerSDK;

            $sId = $this->getUzrId();
            /* @var \UnzerSDK\Resources\PaymentTypes\Card $uzrCard */
            $uzrCard = $oUnzer->fetchPaymentType($sId);
            $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));
            $oUser = $this->session->getUser();
            $oBasket = $this->session->getBasket();

            $customer = $this->getCustomerData($oUser);

            if ($this->isDirectCharge()) {
                $transaction = $uzrCard->charge($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, UnzerHelper::redirecturl(self::PENDING_URL, true), $customer, $orderId);
            } else {
                $transaction = $uzrCard->authorize($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, UnzerHelper::redirecturl(self::PENDING_URL, true), $customer, $orderId);
            }
            // You'll need to remember the shortId to show it on the success or failure page
            $this->session->setVariable('ShortId', $transaction->getShortId());
            $this->session->setVariable('PaymentId', $transaction->getPaymentId());
        } catch (UnzerApiException $e) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, UnzerHelper::translatedMsg($e->getCode(), $e->getClientMessage()));
        }
    }
}
