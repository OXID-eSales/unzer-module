<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidSolutionCatalysts\Unzer\Service\Payment as PaymentService;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

class PaymentGateway extends PaymentGateway_parent
{
    use ServiceContainer;

    /**
     * @inerhitDoc
     */
    public function executePayment($dAmount, &$oOrder): bool
    {
        $oPayment = oxNew(PaymentModel::class);
        if ($oPayment->load($oOrder->getFieldData('oxpaymenttype'))) {
            if ($oPayment->isUnzerPayment()) {
                $paymentService = $this->getServiceFromContainer(PaymentService::class);
                $paymentService->executeUnzerPayment($oPayment);
            }
        }

        return parent::executePayment($dAmount, $oOrder);
    }
}
