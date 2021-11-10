<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use RuntimeException;
use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Unzer;
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\PaymentTypes\Invoice;

class Invoice_unsecured extends Payment
{
    /**
     * @var mixed|\OxidEsales\Eshop\Application\Model\Payment
     */
    protected $_oPayment;

    public function __construct($oxpaymentid)
    {
        $oPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $oPayment->load($oxpaymentid);
        $this->_oPayment = $oPayment;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return 'invoice';
    }

    /**
     * @return string
     */
    public function getPaymentCode(): string
    {
        return 'IV';
    }

    /**
     * @return string
     */
    public function getSyncMode(): string
    {
        return 'SYNC';
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->_oPayment->getId();
    }

    /**
     * @return string
     */
    public function getPaymentProcedure(): string
    {
        return $this->_oPayment->oxpayment__oxpaymentprocedure->value;
    }

    public function validate()
    {
        $unzerHelper = $this->getUnzerHelper();

        // Catch API errors, write the message to your log and show the ClientMessage to the client.
        try {
            // Create an Unzer object using your private key and register a debug handler if you want to.
            $unzer = new Unzer($unzerHelper->getShopPrivateKey());
            $unzer->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

            /** @var Invoice $invoice */
            $invoice = $unzer->createPaymentType(new Invoice());

            $oUser = $this->getUser();
            $oBasket = $this->getBasket();

            $customer = CustomerFactory::createCustomer($oUser->oxuser__oxfname->value, $oUser->oxuser__oxlname->value);
            $this->setCustomerData($customer, $oUser);

            $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));

            $transaction = $invoice->charge($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, $unzerHelper->redirectUrl(self::CONTROLLER_URL), $customer, $orderId);

            // You'll need to remember the shortId to show it on the success or failure page
            $_SESSION['ShortId'] = $transaction->getShortId();
            $_SESSION['PaymentId'] = $transaction->getPaymentId();
            $_SESSION['additionalPaymentInformation'] =
                sprintf(
                    "Please transfer the amount of %f %s to the following account:<br /><br />"
                    . "Holder: %s<br/>"
                    . "IBAN: %s<br/>"
                    . "BIC: %s<br/><br/>"
                    . "<i>Please use only this identification number as the descriptor: </i><br/>"
                    . "%s",
                    $transaction->getAmount(),
                    $transaction->getCurrency(),
                    $transaction->getHolder(),
                    $transaction->getIban(),
                    $transaction->getBic(),
                    $transaction->getDescriptor()
                );

            $unzerHelper->redirect($unzerHelper->redirectUrl(self::RETURN_CONTROLLER_URL));

        } catch (UnzerApiException $e) {
            $merchantMessage = $e->getMerchantMessage();
            $clientMessage = $e->getClientMessage();
        } catch (RuntimeException $e) {
            $merchantMessage = $e->getMessage();
        }
        $unzerHelper->redirect($unzerHelper->redirectUrl(self::FAILURE_URL), $merchantMessage, $clientMessage);
    }
}
