<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Application\Model\User;
use OxidSolutionCatalysts\Unzer\Exception\UnzerException;
use OxidSolutionCatalysts\Unzer\Model\Order;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsDate;
use OxidSolutionCatalysts\Unzer\Model\Transaction as TransactionModel;
use UnzerSDK\Constants\PaymentState;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\PaymentTypes\PaylaterInstallment;
use UnzerSDK\Resources\PaymentTypes\PaylaterInvoice;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Resources\TransactionTypes\Shipment;
use UnzerSDK\Resources\PaymentTypes\Card as UnzerResourceCard;
use UnzerSDK\Resources\PaymentTypes\Paypal as UnzerResourcePaypal;

/**
 * TODO: Decrease count of dependencies to 13
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * TODO: Decrease overall complexity below 50
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Transaction
{
    use ServiceContainer;

    protected Context $context;

    protected UtilsDate $utilsDate;

    private array $paymentTypes = [];

    private array $transPaymentTypeIds = [
        'crd' => 'card',
        'ppl' => 'paypal',
        'sdd' => 'sepa'
    ];

    private array $transActionConst = [
        PaymentState::STATE_NAME_COMPLETED,
        PaymentState::STATE_NAME_CANCELED,
        PaymentState::STATE_NAME_CHARGEBACK,
        PaymentState::STATE_NAME_PENDING
    ];

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
            $unzerPaymentData = $unzerShipment !== null ?
                $this->getUnzerShipmentData($unzerShipment, $unzerPayment) :
                $this->getUnzerPaymentData($unzerPayment);
            $params = array_merge($params, $unzerPaymentData);

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

        if ($this->saveTransaction($params, $oOrder)) {
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
    public function writeCancellationToDB(
        string $orderid,
        string $userId,
        ?Cancellation $unzerCancel,
        Order $oOrder
    ): bool {
        $unzerCancelReason = '';
        if ($unzerCancel !== null) {
            $unzerCancelReason = $unzerCancel->getReasonCode() ?? '';
        }

        $params = [
            'oxorderid' => $orderid,
            'oxshopid' => $this->context->getCurrentShopId(),
            'oxuserid' => $userId,
            'oxactiondate' => date('Y-m-d H:i:s', $this->utilsDate->getTime()),
            'cancelreason' => $unzerCancelReason,
        ];

        if ($unzerCancel instanceof Cancellation) {
            $params = array_merge($params, $this->getUnzerCancelData($unzerCancel));
        }

        return $this->saveTransaction($params, $oOrder);
    }

    /**
     * @param string $orderid
     * @param string $userId
     * @param \UnzerSDK\Resources\TransactionTypes\Charge|null $unzerCharge
     * @throws \Exception
     * @return bool
     */
    public function writeChargeToDB(string $orderid, string $userId, ?Charge $unzerCharge, Order $oOrder): bool
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

        return $this->saveTransaction($params, $oOrder);
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

    protected function saveTransaction(array $params, Order $oOrder): bool
    {
        $transaction = $this->getNewTransactionObject();

        //check if metadata exists
        $params['metadata'] = $params['metadata'] ?? json_encode('', JSON_THROW_ON_ERROR);

        // building oxid from unique index columns
        // only write to DB if oxid doesn't exist to prevent multiple entries of the same transaction
        $oxid = $this->prepareTransactionOxid($params);
        if (!$transaction->load($oxid)) {
            if ($oOrder->getFieldData('oxtransstatus') === 'ABORTED') {
                $transaction->setTransStatus('aborted');
            }

            $transaction->assign($params);
            $transaction->setId($oxid);
            $transaction->save();

            return true;
        }

        return false;
    }

    protected function getInitOrderOxid(array $params): string
    {
        $params['oxaction'] = "init";
        return $this->prepareTransactionOxid($params);
    }

    /**
     * @throws UnzerApiException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getUnzerPaymentData(
        Payment $unzerPayment,
        ?AbstractTransactionType $transaction = null
    ): array {
        $oxaction = preg_replace(
            '/[^a-z]/',
            '',
            strtolower($unzerPayment->getStateName())
        );
        $params = [
            'amount'    => $this->getTransactionAmount($unzerPayment, $transaction),
            'remaining' => $unzerPayment->getAmount()->getRemaining(),
            'currency'  => $unzerPayment->getCurrency(),
            'typeid'    => $unzerPayment->getId(),
            'oxaction'  => $oxaction,
            'traceid'   => $unzerPayment->getTraceId()
        ];
        $savePayment = Registry::getSession()->getVariable('oscunzersavepayment');

        $paymentType = $unzerPayment->getPaymentType();
        $firstPaypalCall = Registry::getSession()->getVariable('oscunzersavepayment_paypal');

        if (
            ($savePayment && ($paymentType instanceof UnzerResourcePaypal && !$firstPaypalCall))
            || ($savePayment && $paymentType instanceof UnzerResourceCard)
        ) {
            $typeId = $paymentType->getId();
            $params['paymenttypeid'] = $typeId;
        }
        Registry::getSession()->setVariable('oscunzersavepayment_paypal', false);

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
        $payment = $unzerCharge->getPayment();
        $customerId = $payment?->getCustomer()?->getId();
        $typeId = $payment?->getPaymentType()?->getId();

        return [
            'amount'            => $unzerCharge->getAmount(),
            'currency'          => $unzerCharge->getCurrency(),
            'typeid'            => $unzerCharge->getId(),
            'paymenttypeid'     => $typeId,
            'oxaction'          => 'charged',
            'customerid'        => $customerId,
            'traceid'           => $unzerCharge->getTraceId(),
            'shortid'           => $unzerCharge->getShortId(),
            'status'            => $this->getUzrStatus($unzerCharge),
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
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getPaymentIdByOrderId(string $orderid, bool $withoutCancel = false): string
    {
        $result = '';
        if ($orderid) {
            $result = DatabaseProvider::getDb()->getOne(
                "SELECT DISTINCT TYPEID FROM oscunzertransaction
                WHERE OXORDERID=? AND OXACTION IN (" . $this->prepareTransActionConstForSql($withoutCancel) . ")",
                [$orderid]
            );
            $result = is_string($result) ? $result : '';
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
                WHERE OXORDERID=? AND OXACTION IN ('completed', 'pending', 'canceled', 'chargeback')
                ORDER BY OXTIMESTAMP DESC LIMIT 1",
                [$orderid]
            );

            $result = $rows[0]['OXID'];
        }

        return ($result === null) ? '' : $result;
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

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getTransactionIds(?User $user = null): array
    {
        $result = [];

        // check user Model
        if (!$user) {
            return $result;
        }

        // check user Id
        $userId = $user->getId() ?: null;
        if (!$userId) {
            return $result;
        }

        $oDB = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
        if ($oDB) {
            $result = $oDB->getAll(
                "SELECT ot.OXID, ot.PAYMENTTYPEID, ot.CURRENCY, ot.CUSTOMERTYPE, o.OXPAYMENTTYPE
                        from oscunzertransaction as ot
                        left join oxorder as o ON (ot.oxorderid = o.OXID) 
            where ot.OXUSERID = :oxuserid AND ot.PAYMENTTYPEID IS NOT NULL
            GROUP BY ot.PAYMENTTYPEID ",
                [':oxuserid' => $userId]
            );
        }
        return $result;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getSavedPaymentsForUser(?User $user, array $ids, bool $cache): array
    {
        if ($cache && count($this->paymentTypes) > 0) {
            return $this->paymentTypes;
        }

        $tmpArr = [];
        foreach ($ids as $typeData) {
            $paymentTypes = null;
            $paymentTypeId = $typeData['PAYMENTTYPEID'] ?: '';
            if ($paymentTypeId) {
                $paymentTypes = $this->setPaymentTypes(
                    $user,
                    $typeData['PAYMENTTYPEID'] ?: '',
                    $typeData['CURRENCY'] ?: '',
                    $typeData['CUSTOMERTYPE'] ?: '',
                    $paymentTypeId
                );
            }

            if ($paymentTypes) {
                foreach ($paymentTypes as $key => $paymentType) {
                    $tmpArr[$key][] = $paymentType;
                }
            }
        }

        $result = [];
        foreach ($tmpArr as $key => $paymentType) {
            foreach ($paymentType as $paymentDetails) {
                $keyDetail = array_key_first($paymentDetails);
                $result[$key][$keyDetail] = $paymentDetails[$keyDetail];
            }
        }

        $this->paymentTypes = $result;
        return $this->paymentTypes;
    }

    private function setPaymentTypes(
        ?User $user,
        string $paymentId,
        string $currency,
        string $customerType,
        string $paymentTypeId
    ): ?array {
        $result = [];

        try {
            $UnzerSdk = $this->getServiceFromContainer(UnzerSDKLoader::class);
            $unzerSDK = $UnzerSdk->getUnzerSDK(
                $paymentId,
                $currency,
                $customerType
            );
            $paymentType = $unzerSDK->fetchPaymentType($paymentTypeId);
        } catch (UnzerException | UnzerApiException $e) {
            $userId = $user ? $user->getId() : 'unknown';
            $logEntry = sprintf(
                'The incorrect data used to initialize the SDK ' .
                'comes from the transactions of the user: "%s"',
                $userId
            );
            $logger = $this->getServiceFromContainer(DebugHandler::class);
            $logger->log($logEntry);
            return null;
        }

        foreach ($this->transPaymentTypeIds as $unzerId => $oxVar) {
            if (strpos($paymentTypeId, $unzerId)) {
                $result[$oxVar][$paymentTypeId] = $paymentType->expose();
            }
        }
        return $result;
    }

    /**
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    private function prepareTransActionConstForSql(bool $withoutCancel = false): string
    {
        $transActionConst = $this->transActionConst;
        if ($withoutCancel) {
            $transActionConst = array_diff($transActionConst, [PaymentState::STATE_NAME_CANCELED]);
        }
        return implode(',', DatabaseProvider::getDb()->quoteArray($transActionConst));
    }


    private function getTransactionAmount(
        Payment $unzerPayment,
        ?AbstractTransactionType $transaction = null
    ): ?float {
        return $transaction instanceof Cancellation ?
            $transaction->getAmount() : $unzerPayment->getAmount()->getTotal();
    }
}
