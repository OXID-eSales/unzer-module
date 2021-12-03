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

use Exception;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Exceptions\UnzerApiException;

class PrePayment extends UnzerPayment
{
    /**
     * @var string
     */
    protected $Paymentmethod = 'prepayment';

    /**
     * @var array|bool
     */
    protected $aCurrencies = ['EUR'];

    /**
     * @return bool
     */
    public function isRecurringPaymentType(): bool
    {
        return false;
    }

    public function execute()
    {
        // Catch API errors, write the message to your log and show the ClientMessage to the client.
        try {
            // Create an Unzer object using your private key and register a debug handler if you want to.
            $unzer = UnzerHelper::getUnzer();

            /** @var \UnzerSDK\Resources\PaymentTypes\Prepayment $prepayment */
            $prepayment = $unzer->createPaymentType(new \UnzerSDK\Resources\PaymentTypes\Prepayment());

            $oUser = UnzerHelper::getUser();
            $oBasket = UnzerHelper::getBasket();

            $customer = $this->getCustomerData($oUser);

            $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));

            $transaction = $prepayment->charge($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, UnzerHelper::redirecturl(self::CONTROLLER_URL), $customer, $orderId, UnzerHelper::getMetadata($this));

            // You'll need to remember the shortId to show it on the success or failure page
            Registry::getSession()->setVariable('ShortId', $transaction->getShortId());
            Registry::getSession()->setVariable('PaymentId', $transaction->getPaymentId());

            $bankData = UnzerHelper::getBankData($transaction);
            Registry::getSession()->setVariable('additionalPaymentInformation', $bankData);
        } catch (UnzerApiException $e) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, UnzerHelper::translatedMsg($e->getCode(), $e->getClientMessage()));
        } catch (Exception $e) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, $e->getMessage());
        }
    }
}
