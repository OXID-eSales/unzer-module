<?php

namespace OxidSolutionCatalysts\Unzer\Service;

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
     * @return void
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
        $oxid = md5(json_encode($params));
        if (!$transaction->load($oxid)) {
            $transaction->setId($oxid);
            $transaction->assign($params);
            $transaction->save();
        }
    }

    protected function getUnzerPaymentData(Payment $unzerPayment): array
    {
        $params = [];
        $params['amount'] = $unzerPayment->getAmount()->getTotal();
        $params['currency'] = $unzerPayment->getCurrency();
        $params['typeid'] = $unzerPayment->getId();
        $params['oxaction'] = $unzerPayment->getStateName();

        if ($metadata = $unzerPayment->getMetadata()) {
            $params['metadataid'] = $metadata->getId();
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
}
