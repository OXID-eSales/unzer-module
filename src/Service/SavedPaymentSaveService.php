<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use Doctrine\DBAL\Connection;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\UserIdService;
use OxidSolutionCatalysts\Unzer\Traits\Request;
use UnzerSDK\Resources\Payment;

class SavedPaymentSaveService
{
    /** @var Connection $connection */
    protected $connection;

    /** @var UserIdService $userIdService */
    private $userIdService;

    /** @var RequestService $requestService */
    private $requestService;

    public function __construct(
        Connection $connection,
        UserIdService $userIdService,
        RequestService $requestService
    ) {
        $this->connection = $connection;
        $this->userIdService = $userIdService;
        $this->requestService = $requestService;
    }

    public function getTransactionParameters(Payment $payment): array
    {
        $paymentType = $payment->getPaymentType();

        return [
            'savepaymentuserid' => $paymentType ? $this->userIdService->getUserIdByPaymentType($paymentType) : '',
            'savepayment' => $paymentType
                && $this->requestService->isSavePaymentSelectedByUserInRequest($paymentType),
        ];
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
