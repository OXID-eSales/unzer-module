<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Unzer\Service\PaymentExtensionLoader;
use OxidSolutionCatalysts\Unzer\Service\PaymentValidator;

class Payment extends Payment_parent
{
    /**
     * Checks if the payment method is an unzer payment method
     */
    public function isUnzerPayment(): bool
    {
        /** @var PaymentValidator $paymentValidator */
        $paymentValidator = $this->getContainer()->get(PaymentValidator::class);
        return $paymentValidator->isUnzerPayment($this);
    }

    /**
     * Checks if the selected currency is supported by the selected unzer payment method
     *
     * @return bool
     */
    public function isUnzerPaymentTypeAllowed(): bool
    {
        /** @var PaymentExtensionLoader $paymentLoader */
        $paymentLoader = ContainerFactory::getInstance()
            ->getContainer()
            ->get(PaymentExtensionLoader::class);

        if ($this->isUnzerPayment() && $paymentLoader->getPaymentExtension($this)->isPaymentTypeAllowed()) {
            return true;
        }
        return false;
    }
}
