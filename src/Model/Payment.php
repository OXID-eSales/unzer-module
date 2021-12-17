<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Unzer\Service\PaymentValidator;

class Payment extends Payment_parent
{
    /**
     * Checks if the payment method is an unzer payment method
     */
    public function isUnzerPayment(): bool
    {
        return $this->getUnzerPaymentValidator()->isUnzerPayment($this);
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

        return $this->getUnzerPaymentValidator()->isPaymentCurrencyAllowed($this);
    }

    private function getUnzerPaymentValidator(): PaymentValidator
    {
        return ContainerFactory::getInstance()
            ->getContainer()
            ->get(PaymentValidator::class);
    }
}
