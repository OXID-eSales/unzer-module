<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\Payment;
use OxidSolutionCatalysts\Unzer\Controller\DispatcherController;

class PaymentGateway extends PaymentGateway_parent
{
    /**
     * @param float $dAmount
     * @param Order|object $oOrder
     * @return bool
     */
    public function executePayment(float $dAmount, &$oOrder): bool
    {
        $oPayment = oxNew(Payment::class);
        if ($oPayment->load($oOrder->oxorder__oxpaymenttype->value)) {
            if ($oPayment->isUnzerPayment()) {
                $Dispatcher = oxNew(DispatcherController::class);
                if ($Dispatcher->executePayment($oPayment->getId())) {

                    return true;
                }
                return false;
            }
        }
        return $this->executePayment($dAmount, $oOrder);
    }
}
