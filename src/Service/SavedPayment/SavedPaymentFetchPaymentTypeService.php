<?php

namespace OxidSolutionCatalysts\Unzer\Service\SavedPayment;

use OxidSolutionCatalysts\Unzer\Exception\UnzerException;
use OxidSolutionCatalysts\Unzer\Service\DebugHandler;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\PaymentTypes\Paypal;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;

class SavedPaymentFetchPaymentTypeService
{
    private UnzerSDKLoader $unzerSDKLoader;
    private DebugHandler $debugHandler;

    public function __construct(UnzerSDKLoader $unzerSDKLoader, DebugHandler $debugHandler)
    {
        $this->unzerSDKLoader = $unzerSDKLoader;
        $this->debugHandler = $debugHandler;
    }

    /**
     * @param array $transactions transactions got from
     *      SavedPaymentFetchPaymentTypeService::getSavedPaymentTransactions
     */
    public function fetchPaymentTypes(array $transactions): array
    {
        $paymentTypes = [];
        foreach ($transactions as $transaction) {
            $paymentTypeId = $transaction['PAYMENTTYPEID'];
            $paymentId = (string)$transaction['OXPAYMENTTYPE'];
            $currency = $transaction['CURRENCY'];
            $customerType = $transaction['CUSTOMERTYPE'];
            $transactionOxId = $transaction['OXID'];

            if (empty($paymentTypeId)) {
                continue;
            }

            try {
                $unzerSDK = $this->unzerSDKLoader->getUnzerSDK(
                    $paymentId,
                    $currency,
                    $customerType
                );
                $paymentType = $unzerSDK->fetchPaymentType($paymentTypeId);
                if ($paymentType instanceof Card) {
                    $paymentTypes[$paymentType->getBrand()][$transactionOxId] = $paymentType->expose();
                }
                if ($paymentType instanceof Paypal) {
                    $paymentTypes['paypal'][$transactionOxId] = $paymentType->expose();
                }
                if ($paymentType instanceof SepaDirectDebit) {
                    $paymentTypes['sepa'][$transactionOxId] = $paymentType->expose();
                }
            } catch (UnzerApiException | UnzerException | \Throwable $e) {
                if ($e->getCode() !== 'API.500.100.001') {
                    $logEntry = sprintf(
                        'Unknown error code while creating the PaymentList: "%s", message: "%s" ',
                        $e->getCode(),
                        $e->getMessage()
                    );
                    $this->debugHandler->log($logEntry);
                    continue;
                }
            }
        }

        return $paymentTypes;
    }
}
