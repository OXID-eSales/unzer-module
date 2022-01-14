<?php

namespace OxidSolutionCatalysts\Unzer\Model;

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
     * @param \OxidEsales\Eshop\Application\Model\Basket $oBasket
     * @param \OxidEsales\Eshop\Application\Model\User $oUser
     * @return int
     * @throws \Exception
     */
    public function finalizeUnzerOrderAfterRedirect(\OxidEsales\Eshop\Application\Model\Basket $oBasket, \OxidEsales\Eshop\Application\Model\User $oUser): int
    {
        $this->isRedirectOrder = true;
        $iRet = $this->finalizeOrder($oBasket, $oUser, true);
        $unzerPaymentStatus = $this->getServiceFromContainer(PaymentService::class)->checkUnzerPaymentStatus();
        $this->updateOrderStatus($unzerPaymentStatus);
        if ($unzerPaymentStatus != "error") {
            $this->initWriteTransactionToDB();
        } else {
            // payment is canceled
            $this->delete();
            $iRet = self::ORDER_STATE_PAYMENTERROR;
        }

        return $iRet;
    }

    private function markUnzerOrderAsPaid(): void
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
     * @param string $status
     */
    public function updateOrderStatus(string $status): void
    {
        switch ($status) {
            case 'success':
                $this->_setOrderStatus('OK');
                $this->markUnzerOrderAsPaid();
                break;
            case 'pending':
                $this->_setOrderStatus('NOT_FINISHED');
                break;
            case 'error':
                $this->_setOrderStatus('ERROR');
                break;
        }
    }
}
