<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Service;

use Doctrine\DBAL\Driver\Result;
use OxidSolutionCatalysts\Unzer\Model\Order;
use PDO;
use Doctrine\DBAL\Query\QueryBuilder;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Order as EshopModelOrder;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsDate;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSolutionCatalysts\Unzer\Model\Transaction as TransactionModel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\PaymentTypes\Invoice;
use UnzerSDK\Resources\PaymentTypes\PaylaterInstallment;
use UnzerSDK\Resources\PaymentTypes\PaylaterInvoice;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Resources\TransactionTypes\Shipment;

/**
 * TODO: Decrease count of dependencies to 13
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * TODO: Decrease overall complexity below 50
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
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

        $oOrder = oxNew(Order::class);
        $oOrder->load($orderid);

        $params = [
            'oxorderid' => $orderid,
            'oxshopid' => $this->context->getCurrentShopId(),
            'oxuserid' => $userId,
            'oxactiondate' => date('Y-m-d H:i:s', $this->utilsDate->getTime()),
            'customertype' => '',
        ];
        if ($unzerPayment) {
            $params = $unzerShipment ?
                array_merge($params, $this->getUnzerShipmentData($unzerShipment, $unzerPayment)) :
                array_merge($params, $this->getUnzerPaymentData($unzerPayment));

            // for PaylaterInvoice, store the customer type
            if (
                $unzerPayment->getPaymentType() instanceof PaylaterInvoice ||
                $unzerPayment->getPaymentType() instanceof PaylaterInstallment
            ) {
                $delCompany = $oOrder->getFieldData('oxdelcompany') ?? '';
                $billCompany = $oOrder->getFieldData('oxbillcompany') ?? '';
                $params['customertype'] = 'B2C';
                if (!empty($delCompany) || !empty($billCompany)) {
                    $params['customertype'] = 'B2B';
                }
            }
        }

        if ($this->saveTransaction($params)) {
            $this->deleteInitOrder($params);

            // Fallback: set ShortID as OXTRANSID
            $shortId = $params['shortid'] ?? '';
            $oOrder->setUnzerTransId($shortId);

            return true;
        }

        return false;
    }

    /**
     * @param (int|mixed|string)[] $params
     *
     */
    public function deleteInitOrder(array $params): void
    {
        $transaction = $this->getNewTransactionObject();

        $oxid = $this->getInitOrderOxid($params);
        if ($transaction->load($oxid)) {
            $transaction->delete();
        }
    }

    /**
     * @param string $orderid
     * @param string $userId
     * @param \UnzerSDK\Resources\TransactionTypes\Cancellation|null $unzerCancel
     * @return bool
     * @throws \Exception
     */
    public function writeCancellationToDB(string $orderid, string $userId, ?Cancellation $unzerCancel): bool
    {
        $params = [
            'oxorderid' => $orderid,
            'oxshopid' => $this->context->getCurrentShopId(),
            'oxuserid' => $userId,
            'oxactiondate' => date('Y-m-d H:i:s', $this->utilsDate->getTime()),
        ];

        if ($unzerCancel instanceof Cancellation) {
            $params = array_merge($params, $this->getUnzerCancelData($unzerCancel));
        }

        return $this->saveTransaction($params);
    }

    /**
     * @param string $orderid
     * @param string $userId
     * @param \UnzerSDK\Resources\TransactionTypes\Charge|null $unzerCharge
     * @throws \Exception
     * @return bool
     */
    public function writeChargeToDB(string $orderid, string $userId, ?Charge $unzerCharge): bool
    {
        $params = [
            'oxorderid' => $orderid,
            'oxshopid' => $this->context->getCurrentShopId(),
            'oxuserid' => $userId,
            'oxactiondate' => date('Y-m-d H:i:s', $this->utilsDate->getTime()),
        ];

        if ($unzerCharge instanceof Charge) {
            $params = array_merge($params, $this->getUnzerChargeData($unzerCharge));
        }

        return $this->saveTransaction($params);
    }

    /**
     * @param array $params
     * @return string
     */
    protected function prepareTransactionOxid(array $params): string
    {
        unset($params['oxactiondate']);
        unset($params['serialized_basket']);
        unset($params['customertype']);

        /** @var string $jsonEncode */
        $jsonEncode = json_encode($params);
        return md5($jsonEncode);
    }

    protected function saveTransaction(array $params): bool
    {
        $result = false;

        $transaction = $this->getNewTransactionObject();

        //check if metadata exists
        $params['metadata'] = $params['metadata'] ?? json_encode('', JSON_THROW_ON_ERROR);

        // building oxid from unique index columns
        // only write to DB if oxid doesn't exist to prevent multiple entries of the same transaction
        $oxid = $this->prepareTransactionOxid($params);
        if (!$transaction->load($oxid)) {
            $transaction->assign($params);
            $transaction->setId($oxid);
            $transaction->save();

            $result = true;
        }
        return $result;
    }

    /**
     * @param array $params
     * @return string
     */
    protected function getInitOrderOxid(array $params): string
    {
        $params['oxaction'] = "init";
        return $this->prepareTransactionOxid($params);
    }

    /**
     * @param Payment $unzerPayment
     * @return array
     * @throws UnzerApiException
     */
    protected function getUnzerPaymentData(Payment $unzerPayment): array
    {
        $oxaction = preg_replace(
            '/[^a-z]/',
            '',
            strtolower($unzerPayment->getStateName())
        );
        $params = [
            'amount'   => $unzerPayment->getAmount()->getTotal(),
            'currency' => $unzerPayment->getCurrency(),
            'typeid'   => $unzerPayment->getId(),
            'oxaction' => $oxaction,
            'traceid'  => $unzerPayment->getTraceId()
        ];
        $savePayment = Registry::getRequest()->getRequestParameter('oscunzersavepayment');
        $paymentType = $unzerPayment->getPaymentType();
        if ($savePayment === "1" && $paymentType) {
            $typeId = $paymentType->getId();
            $params['paymenttypeid'] = $typeId;
        }
        $initialTransaction = $unzerPayment->getInitialTransaction();
        $params['shortid'] = !is_null($initialTransaction) && !is_null($initialTransaction->getShortId()) ?
            $initialTransaction->getShortId() :
            Registry::getSession()->getVariable('ShortId');

        $metadata = $unzerPayment->getMetadata();
        if ($metadata instanceof Metadata) {
            $params['metadata'] = $metadata->jsonSerialize();
        }

        $unzerCustomer = $unzerPayment->getCustomer();
        if ($unzerCustomer instanceof Customer) {
            $params['customerid'] = $unzerCustomer->getId();
        }

        return $params;
    }

    protected function getUnzerChargeData(Charge $unzerCharge): array
    {
        $customerId = '';
        $typeId = '';
        /** @var Payment $payment */
        $payment = $unzerCharge->getPayment();
        if ($payment) {
            /** @var Customer $customer */
            $customer = $payment->getCustomer();
            if ($customer) {
                $customerId = $customer->getId();
            }
            $paymentType = $payment->getPaymentType();
            $typeId = $paymentType ? $paymentType->getId() : '';
        }

        return [
            'amount'        => $unzerCharge->getAmount(),
            'currency'      => $unzerCharge->getCurrency(),
            'typeid'        => $unzerCharge->getId(),
            'paymenttypeid' => $typeId,
            'oxaction'      => 'charged',
            'customerid'    => $customerId,
            'traceid'       => $unzerCharge->getTraceId(),
            'shortid'       => $unzerCharge->getShortId(),
            'status'        => self::getUzrStatus($unzerCharge),
        ];
    }

    protected function getUnzerCancelData(Cancellation $unzerCancel): array
    {
        $currency = '';
        $customerId = '';
        $payment = $unzerCancel->getPayment();
        if (is_object($payment)) {
            $currency = $payment->getCurrency();
            $customer = $payment->getCustomer();
            if (is_object($customer)) {
                $customerId = $customer->getId();
            }
        }
        return [
            'amount'     => $unzerCancel->getAmount(),
            'currency'   => $currency,
            'typeid'     => $unzerCancel->getId(),
            'oxaction'   => 'canceled',
            'customerid' => $customerId,
            'traceid'    => $unzerCancel->getTraceId(),
            'shortid'    => $unzerCancel->getShortId(),
            'status'     => $this->getUzrStatus($unzerCancel),
        ];
    }

    protected function getUnzerShipmentData(Shipment $unzerShipment, Payment $unzerPayment): array
    {
        $currency = '';
        $customerId = '';
        $payment = $unzerShipment->getPayment();
        if (is_object($payment)) {
            $currency = $payment->getCurrency();
            $customer = $payment->getCustomer();
            if (is_object($customer)) {
                $customerId = $customer->getId();
            }
        }
        $params = [
            'amount'     => $unzerShipment->getAmount(),
            'currency'   => $currency,
            'fetchedAt'  => $unzerShipment->getFetchedAt(),
            'typeid'     => $unzerShipment->getId(),
            'oxaction'   => 'shipped',
            'customerid' => $customerId,
            'shortid'    => $unzerShipment->getShortId(),
            'traceid'    => $unzerShipment->getTraceId(),
            'metadata'   => json_encode(["InvoiceId" => $unzerShipment->getInvoiceId()])
        ];

        $unzerCustomer = $unzerPayment->getCustomer();
        if ($unzerCustomer instanceof Customer) {
            $params['customerid'] = $unzerCustomer->getId();
        }

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

        return null;
    }

    /**
     * @param string $orderid
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
                WHERE OXORDERID=? AND OXACTION IN ('completed', 'pending', 'chargeback')",
                [$orderid]
            );

            $result = $rows[0]['TYPEID'];
        }

        return $result;
    }

    /**
     * @param string $orderid
     * @return string
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function getTransactionIdByOrderId(string $orderid): string
    {
        $result = '';

        if ($orderid) {
            $rows = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll(
                "SELECT OXID FROM oscunzertransaction
                WHERE OXORDERID=? AND OXACTION IN ('completed', 'pending', 'chargeback')
                ORDER BY OXTIMESTAMP DESC LIMIT 1",
                [$orderid]
            );

            $result = $rows[0]['OXID'];
        }

        return $result;
    }

    /**
     * @param string $orderid
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function getCustomerTypeAndCurrencyByOrderId($orderid): array
    {
        $transaction = oxNew(TransactionModel::class);
        $transactionId = $this->getTransactionIdByOrderId($orderid);
        $transaction->load($transactionId);

        return [
            'customertype' => $transaction->getFieldData('customertype') ?? '',
            'currency' => $transaction->getFieldData('currency') ?? '',
        ];
    }

    /**
     * @param string $typeid
     * @return bool
     * @throws DatabaseConnectionException
     */
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
