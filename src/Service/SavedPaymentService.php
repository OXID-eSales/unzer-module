<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use Doctrine\DBAL\Connection;

class SavedPaymentService
{
    public const SAVED_PAYMENT_PAYPAL = 'ppl';
    public const SAVED_PAYMENT_CREDIT_CARD = 'crd';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns the last transaction of given $savedPaymentMethod.
     * The resulting array has the same structure as the result from Transaction::getTransactionIds()
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getLastSavedPaymentTransaction(string $oxUserId, string $savedPaymentMethod = null): ?array
    {
        $sql = 'SELECT transaction.OXID, transaction.PAYMENTTYPEID, transaction.CURRENCY, transaction.CUSTOMERTYPE, oxorder.OXPAYMENTTYPE, transaction.OXACTIONDATE
                FROM oscunzertransaction AS transaction
                LEFT JOIN oxorder ON transaction.oxorderid = oxorder.OXID
                INNER JOIN (
                    SELECT PAYMENTTYPEID, MAX(OXACTIONDATE) AS LatestActionDate
                    FROM oscunzertransaction
                    WHERE OXUSERID = :oxuserid AND PAYMENTTYPEID IS NOT NULL
                    GROUP BY PAYMENTTYPEID
                ) AS latest_transaction ON transaction.PAYMENTTYPEID = latest_transaction.PAYMENTTYPEID AND transaction.OXACTIONDATE = latest_transaction.LatestActionDate
                WHERE transaction.OXUSERID = :oxuserid';

        if ($savedPaymentMethod) {
            $sql = $sql . ' AND ' . $this->getPaymentTypeIdLikeExpression($savedPaymentMethod);
        }

        $statement = $this->connection->executeQuery($sql, ['oxuserid' => $oxUserId]);

        $row = $statement->fetchAssociative();

        if ($row) {
            return $row;
        }

        return null;
    }

    private function getPaymentTypeIdLikeExpression(string $savedPaymentMethod)
    {
        return "transaction.PAYMENTTYPEID LIKE 's-{$savedPaymentMethod}%'";
    }
}
