<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\UtilsDate;
use OxidSolutionCatalysts\Unzer\Model\Transaction as TransactionModel;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Resources\TransactionTypes\Shipment;

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
     * @param Shipment|null $unzerShipment
     * @throws \Exception
     * @return bool
     */
    public function writeTransactionToDB(string $orderid, string $userId, ?Payment $unzerPayment, ?Shipment  $unzerShipment = null)
    {
        $transaction = $this->getNewTransactionObject();

        $params = [
            'oxorderid' => $orderid,
            'oxshopid' => $this->context->getCurrentShopId(),
            'oxuserid' => $userId,
            'oxactiondate' => date('Y-m-d H:i:s', $this->utilsDate->getTime()),
        ];

        if ($unzerPayment && !$unzerShipment) {
            $params = array_merge($params, $this->getUnzerPaymentData($unzerPayment));
        }

        if ($unzerShipment) {
            $params = array_merge($params, $this->getUnzerShipmentData($unzerShipment, $unzerPayment));
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

    /**
     * @param string $orderid
     * @param string $userId
     * @param Cancellation|null $unzerCharge
     * @throws \Exception
     * @return bool
     */
    public function writeCancellationToDB(string $orderid, string $userId, ?Cancellation $unzerCancel)
    {
        $transaction = $this->getNewTransactionObject();

        $params = [
            'oxorderid' => $orderid,
            'oxshopid' => $this->context->getCurrentShopId(),
            'oxuserid' => $userId,
            'oxactiondate' => date('Y-m-d H:i:s', $this->utilsDate->getTime()),
        ];


        $params = array_merge($params, $this->getUnzerCancelData($unzerCancel));


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

    /**
     * @param string $orderid
     * @param string $userId
     * @param Charge|null $unzerCharge
     * @throws \Exception
     * @return bool
     */
    public function writeChargeToDB(string $orderid, string $userId, ?Charge $unzerCharge)
    {
        $transaction = $this->getNewTransactionObject();

        $params = [
            'oxorderid' => $orderid,
            'oxshopid' => $this->context->getCurrentShopId(),
            'oxuserid' => $userId,
            'oxactiondate' => date('Y-m-d H:i:s', $this->utilsDate->getTime()),
        ];


        $params = array_merge($params, $this->getUnzerChargeData($unzerCharge));


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

    protected function getUnzerChargeData(Charge $unzerCharge): array
    {
        $params = [
            'amount'   => $unzerCharge->getAmount(),
            'currency' => $unzerCharge->getCurrency(),
            'typeid'   => $unzerCharge->getId(),
            'oxaction' => 'charge',

        ];

        $params['shortid'] = $unzerCharge->getShortId();
        $params['status'] = $this->getUzrStatus($unzerCharge);

        return $params;
    }

    protected function getUnzerCancelData(Cancellation $unzerCancel): array
    {
        $params = [
            'amount'   => $unzerCancel->getAmount(),
            'typeid'   => $unzerCancel->getId(),
            'oxaction' => 'cancel',

        ];

        $params['shortid'] = $unzerCancel->getShortId();
        $params['status'] = $this->getUzrStatus($unzerCancel);

        return $params;
    }

    protected function getUnzerShipmentData(Shipment $unzerShipment, Payment $unzerPayment): array
    {
        $params = [
            'amount'   => $unzerShipment->getAmount(),
            'fetchedAt'=> $unzerShipment->getFetchedAt(),
            'typeid'   => $unzerShipment->getId(),
            'oxaction' => 'ship',
            'shortid'  => $unzerShipment->getShortId()
        ];

        $params['metadata'] = json_encode(["InvoiceId" => $unzerShipment->getInvoiceId()]);

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

    protected static function getUzrStatus($unzerObject) {
        if ($unzerObject->isSuccess())
            return "success";
        if ($unzerObject->isError()) {
            return "error";
        }
        if ($unzerObject->isPending()) {
            return "pending";
        }
    }

    /**
     * @param $paymentid
     * @return array|false
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public static function getPaymentIdByOrderId($orderid)
    {
        if ($orderid) {
            return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll(
                "SELECT DISTINCT TYPEID FROM oscunzertransaction WHERE OXORDERID=? AND OXACTION IN ('completed', 'pending')",
                [(string)$orderid]
            );
        }

        return false;
    }
}
