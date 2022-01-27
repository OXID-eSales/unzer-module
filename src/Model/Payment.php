<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Model;

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
}
