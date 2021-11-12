<?php

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;
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

            $customer = CustomerFactory::createCustomer($oUser->oxuser__oxfname->value, $oUser->oxuser__oxlname->value);
            $this->setCustomerData($customer, $oUser);

            $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));

            $transaction = $invoice->charge($oBasket->getPrice()->getPrice(), $oBasket->getBasketCurrency()->name, UnzerHelper::redirecturl(self::CONTROLLER_URL), $customer, $orderId);

            // You'll need to remember the shortId to show it on the success or failure page
            Registry::getSession()->setVariable('ShortId', $transaction->getShortId());
            Registry::getSession()->setVariable('PaymentId', $transaction->getPaymentId());

            $html = sprintf(Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_AMOUNT'), $transaction->getAmount(), $transaction->getCurrency());
            $html .= sprintf(Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_HOLDER'), $transaction->getHolder());
            $html .= sprintf(Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_IBAN'), $transaction->getIban());
            $html .= sprintf(Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_BIC'), $transaction->getBic());
            $html .= sprintf(Registry::getLang()->translateString('OSCUNZER_BANK_DETAILS_DESCRIPTOR'), $transaction->getDescriptor());
            Registry::getSession()->setVariable('additionalPaymentInformation', $html);
        } catch (UnzerApiException | \RuntimeException $e) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, $e->getMessage());
        }
    }
}
