<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use Doctrine\DBAL\Query\QueryBuilder;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentLoadFilterService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentLoadGroupService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentMethodValidator;
use InvalidArgumentException;
use OxidSolutionCatalysts\Unzer\Tests\Unit\Service\SavedPayment\SQL\LoadQueries;

class SavedPaymentLoadService
{
    public const SAVED_PAYMENT_PAYPAL = 'ppl';
    public const SAVED_PAYMENT_CREDIT_CARD = 'crd';
    public const SAVED_PAYMENT_SEPA_DIRECT_DEBIT = 'sdd';
    public const SAVED_PAYMENT_ALL = 'all';

    private QueryBuilder $queryBuilder;
    private SavedPaymentMethodValidator $methodValidator;
    private SavedPaymentLoadFilterService $loadFilterService;
    private SavedPaymentLoadGroupService $loadGroupService;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory,
        SavedPaymentMethodValidator $methodValidator,
        SavedPaymentLoadFilterService $loadFilterService,
        SavedPaymentLoadGroupService $loadGroupService
    ) {
        $this->queryBuilder = $queryBuilderFactory->create();
        $this->methodValidator = $methodValidator;
        $this->loadFilterService = $loadFilterService;
        $this->loadGroupService = $loadGroupService;
    }

    /**
     * Returns the saved transactions of given $savedPaymentMethod and $userId.
     * The resulting array has the same structure as the result from Transaction::getTransactionIds().
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSavedPaymentTransactions(string $oxUserId, string $savedPaymentMethod): array
    {
        if (!$this->methodValidator->validate($savedPaymentMethod)) {
            throw new InvalidArgumentException(
                "Invalid savedPaymentMethod SavedPaymentService::getLastSavedPaymentTransaction: $savedPaymentMethod"
            );
        }

        $queryBuilder = clone $this->queryBuilder;
        $queryBuilder->select('*')
            ->from('transactionAfterOrder')
            ->where('OXUSERID = :oxuserid')
            ->setParameter('oxuserid', $oxUserId);

        $filterSQL = $this->loadFilterService->getPaymentTypeIdLikeExpression($savedPaymentMethod);
        if ($filterSQL) {
            $queryBuilder->andWhere($filterSQL);
        }

        $queryBuilder->orderBy('OXACTIONDATE');

        $ungroupedRows = $queryBuilder->execute();
        $result = [];
        if ($ungroupedRows instanceof \Doctrine\DBAL\Driver\Result) {
            $result = $ungroupedRows->fetchAllAssociative();
        }
        return $this->loadGroupService->groupByPaymentTypeId($result);
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSavedPaymentTransactionsByUserId(string $savedPaymentUserId): array
    {
        $queryBuilder = clone $this->queryBuilder;
        $queryBuilder->select('OXID')
            ->from('transactionAfterOrder')
            ->where('OXUSERID = :savedPaymentUserId')
            ->setParameter('savedPaymentUserId', $savedPaymentUserId);

        $rowsFromDB = $queryBuilder->execute();
        $result = [];
        if ($rowsFromDB instanceof \Doctrine\DBAL\Driver\Result) {
            $result = $rowsFromDB->fetchAllAssociative();
        }

        return array_map(fn($row) => $row['OXID'], $result);
    }
}
