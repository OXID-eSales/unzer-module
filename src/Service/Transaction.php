<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Model\Transaction as TransactionModel;
use UnzerSDK\Resources\Payment;

class Transaction
{
    /** @var Context */
    protected $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    public function writeTransactionToDB(string $orderid, string $userId, ?Payment $unzerPayment)
    {
        $oTrans = oxNew(TransactionModel::class);

        $params = [
            'oxorderid' => $orderid,
            'oxshopid' => $this->context->getCurrentShopId(),
            'oxuserid' => $userId,
            'oxactiondate' => date('Y-m-d H:i:s', Registry::getUtilsDate()->getTime()),
        ];

        if ($unzerPayment) {
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
        }

        $oTrans->assign($params);
        $oTrans->save();
    }
}
