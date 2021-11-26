<?php

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

use Exception;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;

class Invoice extends UnzerPayment
{
    /**
     * @var string
     */
    protected string $Paymentmethod = 'invoice';

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

            /** @var \UnzerSDK\Resources\PaymentTypes\Invoice $invoice */
            $invoice = $unzer->createPaymentType(new \UnzerSDK\Resources\PaymentTypes\Invoice);

            $oUser = UnzerHelper::getUser();
            $oBasket = UnzerHelper::getBasket();

            $customer = $this->getCustomerData($oUser);

            $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));

            $transaction = $invoice->charge($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, UnzerHelper::redirecturl(self::CONTROLLER_URL), $customer, $orderId, UnzerHelper::getMetadata($this));

            // You'll need to remember the shortId to show it on the success or failure page
            Registry::getSession()->setVariable('ShortId', $transaction->getShortId());
            Registry::getSession()->setVariable('PaymentId', $transaction->getPaymentId());

            $bankData = UnzerHelper::getBankData($transaction);
            Registry::getSession()->setVariable('additionalPaymentInformation', $bankData);
        } catch (UnzerApiException $e) {
            UnzerHelper::getUnzerLogger()->error($e->getMessage(), ["code" => $e->getCode(), "cl" => __CLASS__, "fnc" => __METHOD__]);
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, UnzerHelper::translatedMsg($e->getCode(), $e->getClientMessage()));
        } catch (Exception $e) {
            UnzerHelper::getUnzerLogger()->error($e->getMessage(), ["code" => $e->getCode(), "cl" => __CLASS__, "fnc" => __METHOD__]);
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, $e->getMessage());
        }
    }
}
