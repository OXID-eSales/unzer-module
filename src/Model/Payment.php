<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;
use OxidSolutionCatalysts\Unzer\Service\PaymentValidator;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use OxidEsales\Eshop\Application\Model\Payment as Payment_parent;

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

    private function canDoUnzerAbility(string $sAbility): bool
    {
        $moduleId = $this->getFieldData('oxid');
        $unzerDefinitions = UnzerDefinitions::getUnzerDefinitions();
        $unzerAbilities = UnzerDefinitions::PAYMENT_ABILITIES;

        if (
            in_array($sAbility, $unzerAbilities) &&
            isset($unzerDefinitions[ $moduleId ]) &&
            $unzerDefinitions[ $moduleId ]['abilities']
        ) {
            return in_array($sAbility, $unzerDefinitions[ $moduleId ]['abilities']);
        }
        return false;
    }

    public function canCollectFully(): bool
    {
        return $this->canDoUnzerAbility(UnzerDefinitions::CAN_COLLECT_FULLY);
    }
    public function canCollectPartially(): bool
    {
        return $this->canDoUnzerAbility(UnzerDefinitions::CAN_COLLECT_PARTIALLY);
    }
    public function canRefundFully(): bool
    {
        return $this->canDoUnzerAbility(UnzerDefinitions::CAN_REFUND_FULLY);
    }
    public function canRefundPartially(): bool
    {
        return $this->canDoUnzerAbility(UnzerDefinitions::CAN_REFUND_PARTIALLY);
    }
}
