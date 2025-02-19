<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions as CoreUnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Service\UnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Service\PaymentValidator;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;

class Payment extends Payment_parent
{
    use ServiceContainer;

    public function isUnzerPayment(): bool
    {
        /** @var PaymentValidator $service */
        $service = $this->getServiceFromContainer(PaymentValidator::class);
        return $service->isUnzerPayment($this);
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
        /** @var PaymentValidator $service */
        $service = $this->getServiceFromContainer(PaymentValidator::class);
        return $service->isPaymentCurrencyAllowed($this);
    }

    /**
     * Checks if the payment method is secured or installment
     *
     * @return bool
     */
    public function isUnzerSecuredPayment(): bool
    {
        /** @var PaymentValidator $service */
        $service = $this->getServiceFromContainer(PaymentValidator::class);
        return $service->isSecuredPayment($this);
    }

    public function isUnzerPaymentHealthy(): bool
    {
        /** @var PaymentValidator $service */
        $service = $this->getServiceFromContainer(PaymentValidator::class);
        return $service->isConfigurationHealthy($this);
    }

    private function canDoUnzerAbility(string $sAbility): bool
    {
        /** @var UnzerDefinitions $definitionService */
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
