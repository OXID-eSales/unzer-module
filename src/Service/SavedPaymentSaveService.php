<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use Doctrine\DBAL\Connection;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentSessionService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\UserIdService;
use UnzerSDK\Resources\Payment;

class SavedPaymentSaveService
{
    private QueryBuilderFactoryInterface $queryBuilderFactory;
    private UserIdService $userIdService;
    private SavedPaymentSessionService $sessionService;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory,
        UserIdService $userIdService,
        SavedPaymentSessionService $sessionService
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
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
        if (empty($transactionIds)) {
            return false;
        }

        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->update('oscunzertransaction')
            ->set('SAVEPAYMENT', ':savePaymentValue')
            ->where($queryBuilder->expr()->in('OXID', ':transactionIds'))
            ->setParameter('savePaymentValue', 0)
            ->setParameter('transactionIds', $transactionIds, Connection::PARAM_STR_ARRAY);

        return (bool)$queryBuilder->execute();
    }
}
