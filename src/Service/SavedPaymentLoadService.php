<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use Doctrine\DBAL\Connection;
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

    /** @var Connection $connection */
    private $connection;

    /** @var SavedPaymentMethodValidator $methodValidator */
    private $methodValidator;

    /** @var SavedPaymentLoadFilterService $loadFilterService */
    private $loadFilterService;
    /** @var SavedPaymentLoadGroupService $loadGroupService */
    private $loadGroupService;

    public function __construct(
        Connection $connection,
        SavedPaymentMethodValidator $methodValidator,
        SavedPaymentLoadFilterService $loadFilterService,
        SavedPaymentLoadGroupService $loadGroupService
    ) {
        $this->connection = $connection;
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
        if (!$this->methodValidator->validate(($savedPaymentMethod))) {
            throw new InvalidArgumentException(
                "Invalid savedPaymentMethod SavedPaymentService::getLastSavedPaymentTransaction"
                . ": $savedPaymentMethod"
            );
        }

        $sql = LoadQueries::LOAD_TRANSACTIONS_SQL;

        $filterSQL = $this->loadFilterService->getPaymentTypeIdLikeExpression($savedPaymentMethod);
        if ($filterSQL) {
            $sql = $sql . " AND ($filterSQL)";
        }

        $sql = $sql . ' ORDER BY transactionAfterOrder.OXACTIONDATE';

        /** @var \Doctrine\DBAL\Driver\Result $statement */
        $statement = $this->connection->executeQuery($sql, ['oxuserid' => $oxUserId]);
        $ungroupedRows = $statement->fetchAllAssociative();

        return $this->loadGroupService->groupByPaymentTypeId($ungroupedRows);
    }

    public function getSavedPaymentTransactionsByUserId(string $savedPaymentUserId): array
    {
        $sql = LoadQueries::LOAD_TRANSACTIONS_BY_USER_ID_SQL;

        /** @var \Doctrine\DBAL\Driver\Result $statement */
        $statement = $this->connection->executeQuery($sql, ['savedPaymentUserId' => $savedPaymentUserId]);
        $rowsFromDB = $statement->fetchAllAssociative();

        return array_map(function ($row) {
            return $row['OXID'];
        }, $rowsFromDB);
    }
}
