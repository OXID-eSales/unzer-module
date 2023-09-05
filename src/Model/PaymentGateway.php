<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidSolutionCatalysts\Unzer\Service\Payment as PaymentService;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

class PaymentGateway extends PaymentGateway_parent
{
    use ServiceContainer;

    /**
     * @inerhitDoc
     *
     * @param Order $oOrder
     */
    public function executePayment_notnecessary($dAmount, &$oOrder)
    {
        $success = parent::executePayment($dAmount, $oOrder);

        /** @var string $oxpaymenttype */
        $oxpaymenttype = $oOrder->getFieldData('oxpaymenttype');
        $oPayment = oxNew(Payment::class);
        if ($oPayment->load($oxpaymenttype)) {
            if ($oPayment->isUnzerPayment()) {
                $paymentService = $this->getServiceFromContainer(PaymentService::class);
                $success = $paymentService->executeUnzerPayment($oPayment);
            }
        }

        return $success;
    }
}
