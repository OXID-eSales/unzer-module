<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidSolutionCatalysts\Unzer\Controller\DispatcherController;

class PaymentGateway extends PaymentGateway_parent
{
    /**
     * @inerhitDoc
     */
    public function executePayment($dAmount, &$oOrder): bool
    {
        $oPayment = oxNew(Payment::class);
        if ($oPayment->load($oOrder->oxorder__oxpaymenttype->value)) {
            if ($oPayment->isUnzerPayment()) {
                $Dispatcher = oxNew(DispatcherController::class);
                return $Dispatcher->executePayment($oPayment);
            }
        }
        return parent::executePayment($dAmount, $oOrder);
    }
}
