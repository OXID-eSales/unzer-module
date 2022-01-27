<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Service\Payment as PaymentService;
use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Exceptions\UnzerApiException;

class Order extends Order_parent
{
    use ServiceContainer;

    protected $isRedirectOrder = false;

    /**
     * @param Basket $oBasket
     * @param User $oUser
     * @return int|bool
     * @throws \Exception
     */
    public function finalizeUnzerOrderAfterRedirect(
        Basket $oBasket,
        User $oUser
    ) {
        $this->isRedirectOrder = true;

        $unzerPaymentStatus = $this->getServiceFromContainer(PaymentService::class)->getUnzerPaymentStatus();

        if (!$this->oxorder__oxordernr->value) {
            $this->_setNumber();
        } else {
            oxNew(\OxidEsales\Eshop\Core\Counter::class)
                ->update($this->_getCounterIdent(), $this->oxorder__oxordernr->value);
        }

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
        $iRet = $this->_sendOrderByEmail($oUser, $oBasket, $oUserPayment);

        $this->_setOrderStatus($unzerPaymentStatus);

        if ($unzerPaymentStatus == 'OK') {
            $this->markUnzerOrderAsPaid();
        }

        if ($unzerPaymentStatus != "ERROR") {
            $this->initWriteTransactionToDB();
        } else {
            // payment is canceled
            $this->delete();
            $iRet = self::ORDER_STATE_PAYMENTERROR;
        }

        return $iRet;
    }

    public function markUnzerOrderAsPaid(): void
    {
        $utilsDate = Registry::getUtilsDate();
        $date = date('Y-m-d H:i:s', $utilsDate->getTime());
        $this->oxorder__oxpaid = new Field($date);
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
     * @return bool
     * @throws UnzerApiException
     */
    public function initWriteTransactionToDB(): bool
    {
        if (
            $this->oxorder__oxtransstatus->value == "OK"
            && strpos($this->oxorder__oxpaymenttype->value, "oscunzer") !== false
        ) {
            $transactionService = $this->getServiceFromContainer(TransactionService::class);
            return    $transactionService->writeTransactionToDB(
                $this->getId(),
                $this->getUser()->getId() ?: '',
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
        return (int)$this->getFieldData('OXINVOICENR') !== 0 ?
            $this->getFieldData('OXINVOICENR') :
            $this->getFieldData('OXORDERNR');
    }
}
