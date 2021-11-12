<?php

namespace OxidSolutionCatalysts\Unzer\Model\Payments;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use UnzerSDK\Resources\Customer;
use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Unzer;
use UnzerSDK\Resources\PaymentTypes\Prepayment;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use UnzerSDK\Resources\TransactionTypes\Authorization;

abstract class UnzerPayment
{
    const CONTROLLER_URL = "order";
    const RETURN_CONTROLLER_URL = "order";
    const FAILURE_URL = "";
    const PENDING_URL = "order";
    const SUCCESS_URL = "thankyou";

    /**
     * @param string $oxpaymentid
     */
    abstract public function __construct(string $oxpaymentid);

    /**
     * @return string
     */
    abstract public function getID(): string;

    /**
     * @return string
     */
    abstract public function getPaymentProcedure(): string;

    /**
     * @return mixed|void
     */
    abstract public function execute();

    /**
     * @param Customer $customer
     * @param User $oUser
     */
    public function setCustomerData(Customer $customer, User $oUser)
    {
        if ($oUser->oxuser__oxbirthdate->value != "'0000-00-00'") {
            $customer->setBirthDate(date('Y-m-d', $oUser->oxuser__oxbirthdate->value));
        }
        if ($oUser->oxuser__oxcompany->value) {
            $customer->setCompany($oUser->oxuser__oxcompany->value);
        }
        if ($oUser->oxuser__oxsal->value) {
            $customer->setSalutation($oUser->oxuser__oxsal->value);
        }
        if ($oUser->oxuser__oxusername->value) {
            $customer->setEmail($oUser->oxuser__oxusername->value);
        }
        if ($oUser->oxuser__oxfon->value) {
            $customer->setPhone($oUser->oxuser__oxfon->value);
        }
    }

    public function checkpaymentstatus()
    {
        if (!$paymentId = Registry::getSession()->getVariable('PaymentId')) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, "Something went wrong. Please try again later.");
        }

        // Catch API errors, write the message to your log and show the ClientMessage to the client.
        try {
            // Create an Unzer object using your private key and register a debug handler if you want to.
            $unzer = UnzerHelper::getUnzer();
            $unzer->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

            // Redirect to success if the payment has been successfully completed.
            $payment = $unzer->fetchPayment($paymentId);
            $transaction = $payment->getInitialTransaction();

            if ($payment->isCompleted()) {
                // The payment process has been successful.
                // You show the success page.
                // Goods can be shipped.
            } elseif ($payment->isPending()) {
                if ($transaction->isSuccess()) {
                    if ($transaction instanceof Authorization) {
                        // Payment is ready to be captured.
                        // Goods can be shipped later AFTER charge.
                    }else{
                        // Payment is not done yet (e.g. Prepayment)
                        // Goods can be shipped later after incoming payment (event).
                    }

                    // In any case:
                    // * You can show the success page.
                    // * You can set order status to pending payment
                } elseif ($transaction->isPending()) {

                    // The initial transaction of invoice types will not change to success but stay pending.
                    $paymentType = $payment->getPaymentType();
                    if ($paymentType instanceof Prepayment || $paymentType->isInvoiceType()) {
                        // Awaiting payment by the customer.
                        // Goods can be shipped immediately except for Prepayment type.
                    }

                    // In cases of a redirect to an external service (e.g. 3D secure, PayPal, etc) it sometimes takes time for
                    // the payment to update it's status after redirect into shop.
                    // In this case the payment and the transaction are pending at first and change to cancel or success later.

                    // Use the webhooks feature to stay informed about changes of payment and transaction (e.g. cancel, success)
                    // then you can handle the states as shown above in transaction->isSuccess() branch.
                }
            }
            // If the payment is neither success nor pending something went wrong.
            // In this case do not create the order or cancel it if you already did.
            // Redirect to an error page in your shop and show a message if you want.

            // Check the result message of the initial transaction to find out what went wrong.
            if ($transaction instanceof AbstractTransactionType) {
                // For better debugging log the error message in your error log
                $clientMessage = $transaction->getMessage()->getCustomer();
                UnzerHelper::redirectOnError(self::CONTROLLER_URL, $clientMessage);
            }
        } catch (UnzerApiException | \RuntimeException $e) {
            UnzerHelper::redirectOnError(self::CONTROLLER_URL, $e->getMessage());
        }
    }
}
