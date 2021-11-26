<?php

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

use Exception;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Field as FieldAlias;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;

class InvoiceSecured extends UnzerPayment
{
    /**
     * @var string
     */
    protected string $Paymentmethod = 'invoice-secured';

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

    /**
     * @return void
     */
    public function execute()
    {
        // Catch API errors, write the message to your log and show the ClientMessage to the client.
        try {
            // Create an Unzer object using your private key and register a debug handler if you want to.
            $unzer = UnzerHelper::getUnzer();
            $unzer->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

            /** @var \UnzerSDK\Resources\PaymentTypes\InvoiceSecured $inv_secured */
            $inv_secured = $unzer->createPaymentType(new \UnzerSDK\Resources\PaymentTypes\InvoiceSecured);

            $oUser = UnzerHelper::getUser();
            $oBasket = UnzerHelper::getBasket();

            if ($birthdate = Registry::getRequest()->getRequestEscapedParameter('birthdate')) {
                $oUser->oxuser__oxbirthdate = new Field($birthdate, FieldAlias::T_RAW);
            }

            $customer = $this->getCustomerData($oUser);

            $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));

            $basket = UnzerHelper::getUnzerBasket($oBasket, $orderId);

            $transaction = $inv_secured->charge($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, UnzerHelper::redirecturl(self::CONTROLLER_URL), $customer, $orderId, UnzerHelper::getMetadata($this), $basket);

            // You'll need to remember the shortId to show it on the success or failure page
            Registry::getSession()->setVariable('ShortId', $transaction->getShortId());
            Registry::getSession()->setVariable('PaymentId', $transaction->getPaymentId());

            $bankData = UnzerHelper::getBankData($transaction);
            Registry::getSession()->setVariable('additionalPaymentInformation', $bankData);
            $oUser->save();
        } catch (UnzerApiException $e) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, UnzerHelper::translatedMsg($e->getCode(), $e->getClientMessage()));
        } catch (Exception $e) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, $e->getMessage());
        }
    }
}
