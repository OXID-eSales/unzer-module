<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Exception\RedirectWithMessage;
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
                try {
                    return $paymentService->executeUnzerPayment($oPayment);
                } catch (RedirectWithMessage | Redirect $e) {
                }
            }
        }

        return parent::executePayment($dAmount, $oOrder);
    }
}
