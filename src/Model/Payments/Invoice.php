<?php

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use RuntimeException;
use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Unzer;
use UnzerSDK\Resources\CustomerFactory;

class Invoice extends UnzerPayment
{
    /**
     * @var mixed|Payment
     */
    protected $_oPayment;

    /**
     * @param string $oxpaymentid
     */
    public function __construct(string $oxpaymentid)
    {
        $oPayment = oxNew(Payment::class);
        $oPayment->load($oxpaymentid);
        $this->_oPayment = $oPayment;
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

    /**
     * @return mixed|void
     */
    public function validate()
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

            $customer = CustomerFactory::createCustomer($oUser->oxuser__oxfname->value, $oUser->oxuser__oxlname->value);
            $this->setCustomerData($customer, $oUser);

            $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));

            $transaction = $invoice->charge($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, Registry::getConfig()->getShopHomeUrl() . 'cl=order', $customer, $orderId);

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

            //TODO SUCCESS CHECK Dummy Redirect
            \OxidEsales\Eshop\Core\Registry::getUtils()->redirect('index.php?cl=order', true, 302);
        } catch (UnzerApiException $e) {
            $merchantMessage = $e->getMerchantMessage();
            $clientMessage = $e->getClientMessage();
        } catch (RuntimeException $e) {
            $merchantMessage = $e->getMessage();
        }
        //TODO ERROR Dummy Redirect
        \OxidEsales\Eshop\Core\Registry::getUtils()->redirect('index.php?cl=order', true, 302);
    }
}
