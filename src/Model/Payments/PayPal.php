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

use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Exceptions\UnzerApiException;

class PayPal extends UnzerPayment
{
    /**
     * @var string
     */
    protected $Paymentmethod = 'paypal';

    /**
     * @var array|bool
     */
    protected $aCurrencies = false;

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

            /* @var \UnzerSDK\Resources\PaymentTypes\Paypal $uzrPP */
            $uzrPP = $oUnzer->createPaymentType(new \UnzerSDK\Resources\PaymentTypes\Paypal);
            $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));
            $oUser = $this->session->getUser();
            $oBasket = $this->session->getBasket();

            $customer = $this->getCustomerData($oUser);

            if ($this->isDirectCharge()) {
                $transaction = $uzrPP->charge($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, UnzerHelper::redirecturl(self::PENDING_URL, true), $customer, $orderId);
            } else {
                $transaction = $uzrPP->authorize($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, UnzerHelper::redirecturl(self::PENDING_URL, true), $customer, $orderId);
            }
            // You'll need to remember the shortId to show it on the success or failure page
            $this->session->setVariable('ShortId', $transaction->getShortId());
            $this->session->setVariable('PaymentId', $transaction->getPaymentId());
        } catch (UnzerApiException $e) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, UnzerHelper::translatedMsg($e->getCode(), $e->getClientMessage()));
        }
    }
}
