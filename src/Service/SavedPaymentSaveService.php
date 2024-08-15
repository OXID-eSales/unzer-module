<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use Doctrine\DBAL\Connection;
use OxidSolutionCatalysts\Unzer\Traits\Request;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\PaymentTypes\Paypal;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;

class SavedPaymentSaveService
{
    use Request;

    /** @var Connection $connection */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getTransactionParameters(Payment $payment): array
    {
        $paymentType = $payment->getPaymentType();
        $parameters = [];
        if ($paymentType instanceof Paypal) {
            $parameters['savepaymentuserid'] = $paymentType->getEmail();
        } elseif ($paymentType instanceof Card) {
            $parameters['savepaymentuserid'] = $paymentType->getNumber();
        } elseif ($paymentType instanceof SepaDirectDebit) {
            $parameters['savepaymentuserid'] = $paymentType->getIban();
        }

        $parameters['savepayment'] = $paymentType
            && $this->isSavePaymentSelectedByUserInRequest($paymentType);

        return $parameters;
    }

    public function unsetSavedPayments(array $transactionIds): bool
    {
        $sql = 'UPDATE oscunzertransaction SET SAVEPAYMENT = 0 WHERE OXID IN (:transactionIds)';

        return $this->connection->executeStatement(
            $sql,
            ['transactionIds' => $transactionIds],
            ['transactionIds' => Connection::PARAM_STR_ARRAY]
        ) > 0;
    }
}
