<?php

namespace OxidSolutionCatalysts\Unzer\Service\SavedPayment;

use OxidSolutionCatalysts\Unzer\Service\SavedPaymentLoadService;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class SavedPaymentLoadGroupService
{
    /**
     * in case of sepa both the transaction before and the transaction after order get
     * SAVEPAYMENT = 1 because only in case of sepa it's the same request, because of that we group
     * here by PAYMENTTYPEID
     */
    public function groupByPaymentTypeId(array $ungroupedTransactions): array
    {
        $groupedTransactions = [];
        foreach ($ungroupedTransactions as $ungroupedTransaction) {
            $groupedTransactions[$ungroupedTransaction['PAYMENTTYPEID']] = $ungroupedTransaction;
        }

        return array_values($groupedTransactions);
    }
}
