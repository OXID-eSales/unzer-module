<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Service\Payment as PaymentService;
use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Exceptions\UnzerApiException;

class Order extends Order_parent
{
    use ServiceContainer;

    /** @var bool $isRedirectOrder */
    protected $isRedirectOrder = false;

    /**
     * @param Basket $oBasket
     * @param User $oUser
     * @return int|bool
     * @throws \Exception
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
                $this->_setNumber();
            }
            // else {
            //    oxNew(\OxidEsales\Eshop\Core\Counter::class)
            //        ->update($this->_getCounterIdent(), $this->oxorder__oxordernr->value);
            //}

            // deleting remark info only when order is finished
            \OxidEsales\Eshop\Core\Registry::getSession()->deleteVariable('ordrem');

            //#4005: Order creation time is not updated when order processing is complete
            $this->_updateOrderDate();

            // store orderid
            $oBasket->setOrderId($this->getId());

            // updating wish lists
            $this->_updateWishlist($oBasket->getContents(), $oUser);

            // updating users notice list
            $this->_updateNoticeList($oBasket->getContents(), $oUser);

            // marking vouchers as used and sets them to $this->_aVoucherList (will be used in order email)
            $this->_markVouchers($oBasket, $oUser);

            $oUserPayment = $this->_setPayment($oBasket->getPaymentId());
            // send order by email to shop owner and current user

            // don't let order fail due to stock check while sending out the order mail
            Registry::getSession()->setVariable('blDontCheckProductStockForUnzerMails', true);
            $iRet = $this->_sendOrderByEmail($oUser, $oBasket, $oUserPayment);
            Registry::getSession()->deleteVariable('blDontCheckProductStockForUnzerMails');

            $this->_setOrderStatus($unzerPaymentStatus);

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
        /** @var string $oxpaid */
        $oxpaid = $this->getFieldData('oxpaid');
        if ($oxpaid == '0000-00-00 00:00:00') {
            $utilsDate = Registry::getUtilsDate();
            $date = date('Y-m-d H:i:s', $utilsDate->getTime());
            $this->setFieldData('oxpaid', $date);
            $this->save();
        }
    }

    /**
     * Update order oxtransid
     */
    public function setUnzerTransId($sTransId): void
    {
        $this->setFieldData('oxtransid', $sTransId);
        $this->save();
    }

    /**
     * @inheritDoc
     */
    protected function _checkOrderExist($sOxId = null): bool // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        if ($this->isRedirectOrder) {
            return false;
        }

        return parent::_checkOrderExist($sOxId);
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
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     *
     * @return false|int
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function reinitializeOrder()
    {
        $oDB = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
        $rowSelect = $oDB->getRow("SELECT OXUSERID, SERIALIZED_BASKET from oscunzertransaction
                            where OXORDERID = :oxorderid AND OXACTION = 'init'", [':oxorderid' => $this->getId()]);
        if ($rowSelect) {
            $oUser = oxNew(User::class);
            $oUser->load($rowSelect['OXUSERID']);
            if ($oUser->isLoaded()) {
                /** @var Basket $oBasket */
                $oBasket = unserialize(base64_decode($rowSelect['SERIALIZED_BASKET']));
                return $this->finalizeOrder($oBasket, $oUser, true);
            }
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
     * @param string $fieldName
     * @param string $value
     * @param int $dataType
     * @return false|void
     */
    public function setFieldData($fieldName, $value, $dataType = \OxidEsales\Eshop\Core\Field::T_TEXT)
    {
        return parent::_setFieldData($fieldName, $value, $dataType);
    }
}
