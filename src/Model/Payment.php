<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions as CoreUnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Service\UnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Service\PaymentValidator;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

class Payment extends Payment_parent
{
    use ServiceContainer;

    /**
     * Checks if the payment method is an unzer payment method
     *
     * @return bool
     */
    public function isUnzerPayment(): bool
    {
        return $this->getServiceFromContainer(PaymentValidator::class)->isUnzerPayment($this);
    }

    /**
     * Checks if the selected currency is supported by the selected unzer payment method
     *
     * @return bool
     */
    public function isUnzerPaymentTypeAllowed(): bool
    {
        if (!$this->isUnzerPayment()) {
            return false;
        }

        return $this->getServiceFromContainer(PaymentValidator::class)->isPaymentCurrencyAllowed($this);
    }

    /**
     * Checks if the payment method is secured or installment
     *
     * @return bool
     */
    public function isUnzerSecuredPayment(): bool
    {
        return $this->getServiceFromContainer(PaymentValidator::class)->isSecuredPayment($this);
    }

    public function isUnzerPaymentHealthy(): bool
    {
        return $this->getServiceFromContainer(PaymentValidator::class)->isConfigurationHealthy($this);
    }

    private function canDoUnzerAbility(string $sAbility): bool
    {
        $definitionService = $this->getServiceFromContainer(UnzerDefinitions::class);
        /** @var string $moduleId */
        $moduleId = $this->getFieldData('oxid');

        return $definitionService->unzerTypeHasAbility($moduleId, $sAbility);
    }

    public function canCollectFully(): bool
    {
        return $this->canDoUnzerAbility(CoreUnzerDefinitions::CAN_COLLECT_FULLY);
    }
    public function canCollectPartially(): bool
    {
        return $this->canDoUnzerAbility(CoreUnzerDefinitions::CAN_COLLECT_PARTIALLY);
    }
    public function canRefundFully(): bool
    {
        return $this->canDoUnzerAbility(CoreUnzerDefinitions::CAN_REFUND_FULLY);
    }
    public function canRefundPartially(): bool
    {
        return $this->canDoUnzerAbility(CoreUnzerDefinitions::CAN_REFUND_PARTIALLY);
    }
    public function canRevertPartially(): bool
    {
        return $this->canDoUnzerAbility(CoreUnzerDefinitions::CAN_REVERT_PARTIALLY);
    }
}
