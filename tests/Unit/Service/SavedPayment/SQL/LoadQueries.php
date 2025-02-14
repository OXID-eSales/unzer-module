<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service\SavedPayment\SQL;

class LoadQueries
{
    public const LOAD_TRANSACTIONS_SQL = 'SELECT transactionAfterOrder.OXORDERID, transactionBeforeOrder.OXID,
            transactionBeforeOrder.PAYMENTTYPEID, transactionBeforeOrder.CURRENCY,
            transactionBeforeOrder.CUSTOMERTYPE, oxorder.OXPAYMENTTYPE,
            transactionBeforeOrder.OXACTIONDATE, transactionBeforeOrder.SAVEPAYMENT
            FROM oscunzertransaction as transactionBeforeOrder
                LEFT JOIN oscunzertransaction as transactionAfterOrder
                    on transactionBeforeOrder.SHORTID = transactionAfterOrder.SHORTID
                INNER JOIN oxorder ON transactionAfterOrder.oxorderid = oxorder.OXID
            WHERE transactionBeforeOrder.OXUSERID = :oxuserid 
              AND transactionBeforeOrder.PAYMENTTYPEID IS NOT NULL 
              AND transactionBeforeOrder.SAVEPAYMENT = 1';

    public const LOAD_TRANSACTIONS_BY_USER_ID_SQL = "SELECT transactionBeforeOrder.OXID 
            FROM oscunzertransaction as transactionBeforeOrder 
            INNER JOIN oscunzertransaction as transactionAfterOrder
                ON transactionBeforeOrder.SHORTID = transactionAfterOrder.SHORTID
            WHERE transactionAfterOrder.SAVEPAYMENTUSERID = :savedPaymentUserId
            AND transactionBeforeOrder.SAVEPAYMENT = 1";
}
