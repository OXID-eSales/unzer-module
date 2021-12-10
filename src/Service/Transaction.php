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

    public function writeTransactionToDB(string $orderid, User $oUser, ?Payment $unzerPayment)
    {
        $oTrans = oxNew(TransactionModel::class);

        $params = [
            'oxorderid' => $orderid,
            'oxshopid' => $this->context->getCurrentShopId(),
            'oxuserid' => $oUser->getId(),
            'amount' => $unzerPayment->getAmount()->getTotal(),
            'currency' => $unzerPayment->getCurrency(),
            'typeid' => $unzerPayment->getId(),
            'oxactiondate' => date('Y-m-d H:i:s', Registry::getUtilsDate()->getTime()),
            'oxaction' => $unzerPayment->getStateName(),
        ];

        if ($metadata = $unzerPayment->getMetadata()) {
            $params['metadataid'] = $metadata->getId();
            $params['metadata'] = $metadata->jsonSerialize();
        }

        if ($unzerCustomer = $unzerPayment->getCustomer()) {
            $params['customerid'] = $unzerCustomer->getId();
        }

        $oTrans->assign($params);
        $oTrans->save();
    }
}
