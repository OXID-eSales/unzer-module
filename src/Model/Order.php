<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Model;

use Doctrine\DBAL\ForwardCompatibility\Result;
use Exception;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Order as Order_parent;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSolutionCatalysts\Unzer\Service\Payment as PaymentService;
use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * TODO: Decrease count of dependencies to 13
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Order extends Order_parent
{
    use ServiceContainer;

    /** @var bool $isRedirectOrder */
    protected $isRedirectOrder = false;

    /**
     * @param Basket $oBasket
     * @param User $oUser
     * @return int|bool
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function finalizeUnzerOrderAfterRedirect(
        Basket $oBasket,
        User $oUser
    ) {
        $this->isRedirectOrder = true;

        $unzerPaymentStatus = $this->getServiceFromContainer(PaymentService::class)->getUnzerPaymentStatus();

        if ($unzerPaymentStatus != "ERROR") {
            if (!$this->getFieldData('oxordernr')) {
                $this->setNumber();
            }
            // else {
            //    oxNew(\OxidEsales\Eshop\Core\Counter::class)
            //        ->update($this->_getCounterIdent(), $this->oxorder__oxordernr->value);
            //}

            // deleting remark info only when order is finished
            Registry::getSession()->deleteVariable('ordrem');

            //#4005: Order creation time is not updated when order processing is complete
            $this->updateOrderDate();

            // store orderid
            $oBasket->setOrderId($this->getId());

            // updating wish lists
            $this->updateWishlist($oBasket->getContents(), $oUser);

            // updating users notice list
            $this->updateNoticeList($oBasket->getContents(), $oUser);

            // marking vouchers as used and sets them to $this->_aVoucherList (will be used in order email)
            $this->markVouchers($oBasket, $oUser);

            $oUserPayment = $this->setPayment($oBasket->getPaymentId());
            // send order by email to shop owner and current user

            // don't let order fail due to stock check while sending out the order mail
            Registry::getSession()->setVariable('blDontCheckProductStockForUnzerMails', true);
            $iRet = $this->sendOrderByEmail($oUser, $oBasket, $oUserPayment);
            Registry::getSession()->deleteVariable('blDontCheckProductStockForUnzerMails');

            $this->setOrderStatus($unzerPaymentStatus);

            if ($unzerPaymentStatus == 'OK') {
                $this->markUnzerOrderAsPaid();
            }

            $this->initWriteTransactionToDB();
        } else {
            // payment is canceled
            $this->delete();
            $iRet = self::ORDER_STATE_PAYMENTERROR;
        }

        return $iRet;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function markUnzerOrderAsPaid(): void
    {
        $utilsDate = Registry::getUtilsDate();
        $date = date('Y-m-d H:i:s', $utilsDate->getTime());
        $this->setFieldData('oxpaid', $date);
        $this->save();
    }

    /**
     * @param string $fieldName
     * @param string $value
     * @param int $dataType
     * @return false|void
     */
    public function setFieldData($fieldName, $value, $dataType = Field::T_TEXT)
    {
        return parent::setFieldData($fieldName, $value, $dataType);
    }

    /**
     * @param \UnzerSDK\Resources\Payment|null $unzerPayment
     * @return bool
     * @throws UnzerApiException
     */
    public function initWriteTransactionToDB($unzerPayment = null): bool
    {
        /** @var string $oxpaymenttype */
        $oxpaymenttype = $this->getFieldData('oxpaymenttype');
        if (
            $this->getFieldData('oxtransstatus') == "OK"
            && strpos($oxpaymenttype, "oscunzer") !== false
        ) {
            $transactionService = $this->getServiceFromContainer(TransactionService::class);
            return $transactionService->writeTransactionToDB(
                $this->getId(),
                $this->getOrderUser()->getId() ?: '',
                $unzerPayment instanceof \UnzerSDK\Resources\Payment ?
                    $unzerPayment :
                    $this->getServiceFromContainer(PaymentService::class)->getSessionUnzerPayment()
            );
        }

        return false;
    }

    /**
     * @inerhitDoc
     *
     * @param string $sOxId Ordering ID (default null)
     *
     * @return bool
     */
    public function delete($sOxId = null)
    {
        $sOxId = $sOxId ?? $this->getId();

        // delete transaction-list too
        $transactionList = oxNew(TransactionList::class);
        $transactionList->getTransactionList($sOxId);
        if ($transactionList->count()) {
            /** @var Transaction $transaction */
            foreach ($transactionList as $transaction) {
                $transaction->delete();
            }
        }

        return parent::delete($sOxId);
    }

    /**
     * @return mixed
     */
    public function getUnzerInvoiceNr()
    {
        /** @var int $number */
        $number = $this->getFieldData('OXINVOICENR') !== 0 ?
            $this->getFieldData('OXINVOICENR') :
            $this->getFieldData('OXORDERNR');
        return $number;
    }

    /**
     * @return false|int
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @throws DatabaseConnectionException
     *
     * @throws DatabaseErrorException
     */
    public function reinitializeOrder()
    {
        /** @var ContainerInterface $container */
        $container = ContainerFactory::getInstance()->getContainer();
        /** @var QueryBuilderFactoryInterface $queryBuilderFactory */
        $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);

        $queryBuilder = $queryBuilderFactory->create();
        $queryBuilder->select('oxuserid', 'serialized_basket')
            ->from('oscunzertransaction')
            ->where('oxorderid = :oxorderid')->andWhere('oxaction = :oxaction');

        $parameters = [
            'oxorderid' => $this->getId(),
            'oxaction' => 'init'
        ];

        /** @var Result $result */
        $result = $queryBuilder->setParameters($parameters)->execute();
        $rowSelect = $result->fetchAllAssociative();

        if ($rowSelect) {
            $oUser = oxNew(User::class);
            /** @var string $oxuserid */
            $oxuserid = $rowSelect[0]['oxuserid'];
            /** @var string $serialBasket */
            $serialBasket = $rowSelect[0]['serialized_basket'];
            $oUser->load($oxuserid);
            if ($oUser->isLoaded()) {
                /** @var Basket $oBasket */
                $oBasket = unserialize(base64_decode($serialBasket));
                return $this->finalizeOrder($oBasket, $oUser, true);
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    protected function checkOrderExist($sOxId = null): bool
    {
        if ($this->isRedirectOrder) {
            return false;
        }

        return parent::checkOrderExist($sOxId);
    }
}
