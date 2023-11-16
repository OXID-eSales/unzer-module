<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Model;

use Exception;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSolutionCatalysts\Unzer\Service\Payment as PaymentService;
use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use OxidSolutionCatalysts\Unzer\Service\Unzer;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use Psr\Container\ContainerInterface;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * TODO: Decrease count of dependencies to 13
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Order extends Order_parent
{
    use ServiceContainer;

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
        $orderId = Registry::getSession()->getVariable('sess_challenge');
        $orderId = is_string($orderId) ? $orderId : '';
        $iRet = self::ORDER_STATE_PAYMENTERROR;

        if (!$orderId) {
            return $iRet;
        }

        $this->setId($orderId);
        $paymentService = $this->getServiceFromContainer(PaymentService::class);
        $unzerService = $this->getServiceFromContainer(Unzer::class);
        $unzerPaymentStatus = $paymentService->getUnzerPaymentStatus();

        if ($unzerPaymentStatus !== "ERROR") {
            // copies user info
            $this->setUser($oUser);

            // copies basket info
            $this->loadFromBasket($oBasket);

            $oUserPayment = $this->setPayment($oBasket->getPaymentId());

            // set folder information, order is new
            $this->setFolder();

            //saving all order data to DB
            $this->save();

            if (!$this->getFieldData('oxordernr')) {
                $this->setNumber();
            }

            // setUnzerOrderId
            $this->setUnzerOrderNr($paymentService->getUnzerOrderId());
            $unzerService->resetUnzerOrderId();

            // deleting remark info only when order is finished
            Registry::getSession()->deleteVariable('ordrem');

            //#4005: Order creation time is not updated when order processing is complete
            $this->updateOrderDate();

            // store orderid
            $oBasket->setOrderId($orderId);

            // updating wish lists
            $this->updateWishlist($oBasket->getContents(), $oUser);

            // updating users notice list
            $this->updateNoticeList($oBasket->getContents(), $oUser);

            // marking vouchers as used and sets them to $this->_aVoucherList (will be used in order email)
            $this->markVouchers($oBasket, $oUser);

            // send order by email to shop owner and current user
            // don't let order fail due to stock check while sending out the order mail
            Registry::getSession()->setVariable('blDontCheckProductStockForUnzerMails', true);
            $iRet = $this->sendOrderByEmail($oUser, $oBasket, $oUserPayment);
            Registry::getSession()->deleteVariable('blDontCheckProductStockForUnzerMails');

            $this->setOrderStatus($unzerPaymentStatus);

            if ($unzerPaymentStatus === 'OK') {
                $this->markUnzerOrderAsPaid();
            }

            $this->initWriteTransactionToDB();
        } else {
            // payment is canceled
            $this->delete();
        }

        return $iRet;
    }

    public function getUnzerOrderNr(): int
    {
        $value = $this->getFieldData('oxunzerordernr');
        return is_numeric($value) ? (int)$value : 0;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function setUnzerOrderNr(int $unzerOrderId): int
    {
        /** @var ContainerInterface $container */
        $container = ContainerFactory::getInstance()
            ->getContainer();

        /** @var QueryBuilderFactoryInterface $queryBuilderFactory */
        $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);

        $queryBuilder = $queryBuilderFactory->create();

        $query = $queryBuilder
            ->update('oxorder')
            ->set("oxunzerordernr", ":oxunzerordernr")
            ->where("oxid = :oxid");

        $parameters = [
            ':oxunzerordernr' => $unzerOrderId,
            ':oxid' => $this->getId()
        ];

        $query->setParameters($parameters)->execute();

        // TODO fixme Access to an undefined property
        // OxidSolutionCatalysts\Unzer\Model\Order::$oxorder__oxunzerordernr.
        /** @phpstan-ignore-next-line */
        $this->oxorder__oxunzerordernr = new Field($unzerOrderId);

        return $unzerOrderId;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function markUnzerOrderAsPaid(): void
    {
        $oxpaid = $this->getFieldData('oxpaid');
        if ($oxpaid === '0000-00-00 00:00:00' || is_null($oxpaid)) {
            $utilsDate = Registry::getUtilsDate();
            $date = date('Y-m-d H:i:s', $utilsDate->getTime());
            $this->setFieldData('oxpaid', $date);
            $this->save();
        }
        // e.g. prepayments start with "NO_FINISHED", if they are marked as paid,
        // we set the status to OK here
        $this->setOrderStatus('OK');
    }

    /**
     * Update order oxtransid
     */
    public function setUnzerTransId(string $sTransId): void
    {
        $this->setFieldData('oxtransid', $sTransId);
        $this->save();
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
            strpos($oxpaymenttype, "oscunzer") !== false
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
}
