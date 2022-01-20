<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\UtilsDate;
use OxidSolutionCatalysts\Unzer\Model\Transaction as TransactionModel;
use UnzerSDK\Resources\Payment;

class Transaction
{
    /** @var Context */
    protected $context;

    /** @var UtilsDate */
    protected $utilsDate;

    public function __construct(
        Context $context,
        UtilsDate $utilsDate
    ) {
        $this->context = $context;
        $this->utilsDate = $utilsDate;
    }

    /**
     * @param string $orderid
     * @param string $userId
     * @param Payment|null $unzerPayment
     * @throws \Exception
     * @return bool
     */
    public function writeTransactionToDB(string $orderid, string $userId, ?Payment $unzerPayment)
    {
        $transaction = $this->getNewTransactionObject();

        $params = [
            'oxorderid' => $orderid,
            'oxshopid' => $this->context->getCurrentShopId(),
            'oxuserid' => $userId,
            'oxactiondate' => date('Y-m-d H:i:s', $this->utilsDate->getTime()),
        ];

        if ($unzerPayment) {
            $params = array_merge($params, $this->getUnzerPaymentData($unzerPayment));
        }

        // building oxid from unique index columns
        // only write to DB if oxid doesn't exist to prevent multiple entries of the same transaction
        $oxid = $this->prepareTransactionOxid($params);
        if (!$transaction->load($oxid)) {
            $transaction->assign($params);
            $transaction->setId($oxid);
            $transaction->save();

            return true;
        }

        return false;
    }

    protected function prepareTransactionOxid(array $params): string
    {
        unset($params['oxactiondate']);
        return md5(json_encode($params));
    }

    protected function getUnzerPaymentData(Payment $unzerPayment): array
    {
        $params = [
            'amount'   => $unzerPayment->getAmount()->getTotal(),
            'currency' => $unzerPayment->getCurrency(),
            'typeid'   => $unzerPayment->getId(),
            'oxaction' => $unzerPayment->getStateName()
        ];

        if ($initialTransaction = $unzerPayment->getInitialTransaction()) {
            $params['shortid'] = $initialTransaction->getShortId();
        }

        if ($metadata = $unzerPayment->getMetadata()) {
            $params['metadata'] = $metadata->jsonSerialize();
        }

        if ($unzerCustomer = $unzerPayment->getCustomer()) {
            $params['customerid'] = $unzerCustomer->getId();
        }

        return $params;
    }

    protected function getNewTransactionObject(): TransactionModel
    {
        return oxNew(TransactionModel::class);
    }

    /**
     * @param $paymentid
     * @return array|false
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public static function getTransactionDataByPaymentId($paymentid)
    {
        if ($paymentid) {
            return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll(
                "SELECT DISTINCT OXORDERID, OXUSERID FROM oscunzertransaction WHERE TYPEID=?",
                [(string)$paymentid]
            );
        }

        return false;
    }
}
