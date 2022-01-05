<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\Prepayment;
use UnzerSDK\Resources\TransactionTypes\Authorization;

class Order extends Order_parent
{
    use ServiceContainer;

    /**
     * @inerhitDoc
     * @param \OxidEsales\Eshop\Application\Model\User $oUser
     * @throws UnzerApiException
     */
    public function finalizeOrder($oBasket, $oUser, $blRecalculatingOrder = false)
    {
        $blRedirectFromUzr = Registry::getRequest()->getRequestParameter('uzrredirect');
        if ($blRedirectFromUzr) {
            $orderId = \OxidEsales\Eshop\Core\Registry::getSession()->getVariable('sess_challenge');
            if ($this->load($orderId)) {
                // order not saved TODO
            }

            $sessionUnzerPayment = $this->getSessionUnzerPayment();
            if ($sessionUnzerPayment && $this->checkUnzerPaymentStatus($sessionUnzerPayment)) {
                if (!$this->oxorder__oxordernr->value) {
                    $this->_setNumber();
                } else {
                    oxNew(\OxidEsales\Eshop\Core\Counter::class)
                        ->update($this->_getCounterIdent(), $this->oxorder__oxordernr->value);
                }

                $oUserPayment = $this->_setPayment($oBasket->getPaymentId());
                // deleting remark info only when order is finished
                \OxidEsales\Eshop\Core\Registry::getSession()->deleteVariable('ordrem');

                //#4005: Order creation time is not updated when order processing is complete
                if (!$blRecalculatingOrder) {
                    $this->_updateOrderDate();
                }

                // store orderid
                $oBasket->setOrderId($this->getId());

                // updating wish lists
                $this->_updateWishlist($oBasket->getContents(), $oUser);

                // updating users notice list
                $this->_updateNoticeList($oBasket->getContents(), $oUser);

                // marking vouchers as used and sets them to $this->_aVoucherList (will be used in order email)
                // skipping this action in case of order recalculation
                if (!$blRecalculatingOrder) {
                    $this->_markVouchers($oBasket, $oUser);
                }

                // send order by email to shop owner and current user
                // skipping this action in case of order recalculation
                if (!$blRecalculatingOrder) {
                    $iRet = $this->_sendOrderByEmail($oUser, $oBasket, $oUserPayment);
                } else {
                    $iRet = self::ORDER_STATE_OK;
                }

                //redirect payment
                if (
                    $this->oxorder__oxtransstatus->value == "OK"
                    && strpos($this->oxorder__oxpaymenttype->value, "oscunzer") !== false
                ) {
                    $transactionService = $this->getServiceFromContainer(TransactionService::class);
                    $transactionService->writeTransactionToDB(
                        $this->getId(),
                        $oUser->getId() ?: '',
                        $sessionUnzerPayment
                    );
                }

                return (int)$iRet;
            } else {
                // payment is canceled
                $this->delete();
                return self::ORDER_STATE_PAYMENTERROR;
            }
        } else {
            $iRet = parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);

            //no redirect payment
            if (
                $this->oxorder__oxtransstatus->value == "OK"
                && strpos($this->oxorder__oxpaymenttype->value, "oscunzer") !== false
            ) {
                $transactionService = $this->getServiceFromContainer(TransactionService::class);
                $transactionService->writeTransactionToDB(
                    $this->getId(),
                    $oUser->getId() ?: '',
                    $this->getSessionUnzerPayment()
                );
            }
        }
        return $iRet;
    }

    private function markUnzerOrderAsPaid(): void
    {
        $utilsDate = \OxidEsales\Eshop\Core\Registry::getUtilsDate();
        $date = date('Y-m-d H:i:s', $utilsDate->getTime());
        $this->oxorder__oxpaid = new \OxidEsales\Eshop\Core\Field($date);
        $this->save();
    }

    protected function checkUnzerPaymentStatus(\UnzerSDK\Resources\Payment $payment): bool
    {
        $result = false;
        $transaction = $payment->getInitialTransaction();

        if ($payment->isCompleted()) {
            // updating order trans status (success status)
            $this->_setOrderStatus('OK');
            $this->markUnzerOrderAsPaid();
            $result = true;
        } elseif ($payment->isPending() && $transaction) {
            if ($transaction->isSuccess()) {
                if ($transaction instanceof Authorization) {
                    $payment->getAuthorization()->charge();
                } else {
                    // Payment is not done yet (e.g. Prepayment)
                    // Goods can be shipped later after incoming payment (event).
                }
                $this->_setOrderStatus('NOT_FINISHED');
                // In any case:
                // * You can show the success page.
                // * You can set order status to pending payment
                $result = true;
            } elseif ($transaction->isPending()) {
                // The initial transaction of invoice types will not change to success but stay pending.
                $paymentType = $payment->getPaymentType();
                if ($paymentType instanceof Prepayment || $paymentType->isInvoiceType()) {
                    // Awaiting payment by the customer.
                    // Goods can be shipped immediately except for Prepayment type.
                }

                // In cases of a redirect to an external service (e.g. 3D secure, PayPal, etc) it
                // sometimes takes time for the payment to update it's status after redirect into shop.
                // In this case the payment and the transaction are pending at first and change to cancel
                // or success later.

                // Use the webhooks feature to stay informed about changes of payment
                // and transaction (e.g. cancel, success)
                // then you can handle the states as shown above in transaction->isSuccess() branch.
                $this->_setOrderStatus('NOT_FINISHED');
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @throws UnzerApiException
     */
    protected function getSessionUnzerPayment(): ?\UnzerSDK\Resources\Payment
    {
        if ($paymentId = Registry::getSession()->getVariable('PaymentId')) {
            $unzerSDKLoader = $this->getServiceFromContainer(UnzerSDKLoader::class);
            $unzer = $unzerSDKLoader->getUnzerSDK();

            return $unzer->fetchPayment($paymentId);
        }

        return null;
    }
}
