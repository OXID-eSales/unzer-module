<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsDate;
use OxidSolutionCatalysts\Unzer\Model\Transaction as TransactionModel;
use UnzerSDK\Exceptions\UnzerApiException;
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

    /**
     * @param Context $context
     * @param UtilsDate $utilsDate
     */
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
     * @return bool
     * @throws \Exception
    */
    public function writeTransactionToDB(
        string $orderid,
        string $userId,
        ?Payment $unzerPayment,
        ?Shipment $unzerShipment = null
    ): bool {
        $transaction = $this->getNewTransactionObject();

        $params = [
            'oxorderid' => $orderid,
            'oxshopid' => $this->context->getCurrentShopId(),
            'oxuserid' => $userId,
            'oxactiondate' => date('Y-m-d H:i:s', $this->utilsDate->getTime()),
        ];
        if ($unzerPayment && !$unzerShipment) {
            $params = array_merge($params, $this->getUnzerPaymentData($unzerPayment));
        } elseif ($unzerShipment) {
            $params = array_merge($params, $this->getUnzerShipmentData($unzerShipment, $unzerPayment));
        }

        if ($unzerPayment && $unzerPayment->getState() == 2) {
            $this->deleteOldInitOrders();
        }

        // building oxid from unique index columns
        // only write to DB if oxid doesn't exist to prevent multiple entries of the same transaction
        $oxid = $this->prepareTransactionOxid($params);
        if (!$transaction->load($oxid)) {
            $transaction->assign($params);
            $transaction->setId($oxid);
            $transaction->save();
            $this->deleteInitOrder($params);

            return true;
        }

        return false;
    }

    /**
     * @param string $orderid
     * @param string $userId
     * @param Payment|null $unzerPayment
     * @param Basket|null $basketModel
     * @return bool
     * @throws \Exception
     */
    public function writeInitOrderToDB(
        string $orderid,
        string $userId,
        ?Payment $unzerPayment,
        ?Basket $basketModel
    ): bool {
        $transaction = $this->getNewTransactionObject();

        $params = [
            'oxorderid' => $orderid,
            'oxshopid' => $this->context->getCurrentShopId(),
            'oxuserid' => $userId,
            'oxactiondate' => date('Y-m-d H:i:s', $this->utilsDate->getTime()),
        ];

        $params = array_merge($params, $this->getUnzerInitOrderData($unzerPayment, $basketModel));

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
     * @param $params
     */
    public function deleteInitOrder($params)
    {
        $transaction = $this->getNewTransactionObject();

        $oxid = $this->getInitOrderOxid($params);
        if ($transaction->load($oxid)) {
            $transaction->delete();
        }
    }

    public function deleteOldInitOrders()
    {
        DatabaseProvider::getDb()->Execute(
            "DELETE from oscunzertransaction where OXACTION = 'init' AND OXACTIONDATE < NOW() - INTERVAL 1 DAY"
        );
    }

    /**
     * @param string $orderid
     * @param string $userId
     * @param Cancellation|null $unzerCharge
     * @return bool
     * @throws \Exception
     */
    public function writeCancellationToDB(string $orderid, string $userId, ?Cancellation $unzerCancel): bool
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
    public function writeChargeToDB(string $orderid, string $userId, ?Charge $unzerCharge): bool
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

    /**
     * @param array $params
     * @return string
     */
    protected function prepareTransactionOxid(array $params): string
    {
        unset($params['oxactiondate']);
        unset($params['serialized_basket']);
        return md5(json_encode($params));
    }

    /**
     * @param array $params
     * @return string
     */
    protected function getInitOrderOxid(array $params): string
    {
        unset($params['oxactiondate']);
        unset($params['serialized_basket']);
        $params['oxaction'] = "init";
        return md5(json_encode($params));
    }

    /**
     * @param Payment $unzerPayment
     * @return array
     * @throws UnzerApiException
     */
    protected function getUnzerPaymentData(Payment $unzerPayment): array
    {
        $params = [
            'amount'   => $unzerPayment->getAmount()->getTotal(),
            'currency' => $unzerPayment->getCurrency(),
            'typeid'   => $unzerPayment->getId(),
            'oxaction' => $unzerPayment->getStateName(),
            'traceid'  => $unzerPayment->getTraceId()
        ];

        if (
            ($initialTransaction = $unzerPayment->getInitialTransaction()) &&
            $initialTransaction->getShortId() !== null
        ) {
            $params['shortid'] = $initialTransaction->getShortId();
        } else {
            $params['shortid'] = Registry::getSession()->getVariable('ShortId');
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
        return [
            'amount'   => $unzerCharge->getAmount(),
            'currency' => $unzerCharge->getCurrency(),
            'typeid'   => $unzerCharge->getId(),
            'oxaction' => 'charge',
            'traceid'  => $unzerCharge->getTraceId(),
            'shortid'  => $unzerCharge->getShortId(),
            'status'   => $this->getUzrStatus($unzerCharge),
        ];
    }

    protected function getUnzerCancelData(Cancellation $unzerCancel): array
    {
        return [
            'amount'   => $unzerCancel->getAmount(),
            'typeid'   => $unzerCancel->getId(),
            'oxaction' => 'cancel',
            'traceid'  => $unzerCancel->getTraceId(),
            'shortid'  => $unzerCancel->getShortId(),
            'status'   => $this->getUzrStatus($unzerCancel),
        ];
    }

    protected function getUnzerShipmentData(Shipment $unzerShipment, Payment $unzerPayment): array
    {
        $params = [
            'amount'    => $unzerShipment->getAmount(),
            'fetchedAt' => $unzerShipment->getFetchedAt(),
            'typeid'    => $unzerShipment->getId(),
            'oxaction'  => 'ship',
            'shortid'   => $unzerShipment->getShortId(),
            'traceid'   => $unzerShipment->getTraceId(),
            'metadata'  => json_encode(["InvoiceId" => $unzerShipment->getInvoiceId()])
        ];

        if ($unzerCustomer = $unzerPayment->getCustomer()) {
            $params['customerid'] = $unzerCustomer->getId();
        }

        return $params;
    }

    /**
     * @throws UnzerApiException
     */
    protected function getUnzerInitOrderData(Payment $unzerPayment, Basket $basketModel): array
    {
        $params = $this->getUnzerPaymentData($unzerPayment);
        $params["oxaction"] = 'init';
        $params["serialized_basket"] = base64_encode(serialize($basketModel));

        return $params;
    }

    /**
     * @return TransactionModel
     */

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
    public static function getTransactionDataByPaymentId(string $paymentid)
    {
        if ($paymentid) {
            return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll(
                "SELECT DISTINCT OXORDERID, OXUSERID FROM oscunzertransaction WHERE TYPEID=?",
                [$paymentid]
            );
        }

        return false;
    }

    /**
     * @param Cancellation|Charge $unzerObject
     *
     * @return null|string
     *
     * @psalm-return 'error'|'pending'|'success'|null
     */
    protected static function getUzrStatus($unzerObject)
    {
        if ($unzerObject->isSuccess()) {
            return "success";
        }
        if ($unzerObject->isError()) {
            return "error";
        }
        if ($unzerObject->isPending()) {
            return "pending";
        }
    }

    /**
     * @param $paymentid
     * @return string
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public static function getPaymentIdByOrderId(string $orderid)
    {
        $result = '';

        if ($orderid) {
            $rows = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll(
                "SELECT DISTINCT TYPEID FROM oscunzertransaction
                WHERE OXORDERID=? AND OXACTION IN ('completed', 'pending')",
                [$orderid]
            );

            $result = $rows[0]['TYPEID'];
        }

        return $result;
    }

    public function isValidTransactionTypeId($typeid): bool
    {
        if (
            DatabaseProvider::getDb()->getOne(
                "SELECT DISTINCT TYPEID FROM oscunzertransaction WHERE TYPEID=? ",
                [$typeid]
            )
        ) {
            return true;
        }
        return false;
    }
}
