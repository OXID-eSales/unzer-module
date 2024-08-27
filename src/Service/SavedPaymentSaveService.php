<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use Doctrine\DBAL\Connection;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentSessionService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\UserIdService;
use UnzerSDK\Resources\Payment;

class SavedPaymentSaveService
{
    /** @var Connection $connection */
    protected $connection;

    /** @var UserIdService $userIdService */
    private $userIdService;

    /** @var SavedPaymentSessionService $sessionService */
    private $sessionService;

    public function __construct(
        Connection $connection,
        UserIdService $userIdService,
        SavedPaymentSessionService $sessionService
    ) {
        $this->connection = $connection;
        $this->userIdService = $userIdService;
        $this->sessionService = $sessionService;
    }

    public function getTransactionParameters(Payment $payment): array
    {
        $paymentType = $payment->getPaymentType();

        if ($this->sessionService->isSavedPayment()) {
            return [
                'savepaymentuserid' => $paymentType ? $this->userIdService->getUserIdByPaymentType($paymentType) : '',
                'savepayment' => $paymentType ? '1' : '0',
            ];
        }

        return [];
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
