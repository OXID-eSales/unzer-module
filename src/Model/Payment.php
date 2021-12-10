<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Unzer\Service\PaymentExtensionLoader;

class Payment extends Payment_parent
{
    /**
     * Checks if the payment method is an unzer payment method
     *
     * @return bool
     */
    public function isUnzerPayment(): bool
    {
        $isUnzer = false;

        if (strpos($this->oxpayments__oxid->value, "oscunzer") !== false) {
            $isUnzer = true;
        }

        return $isUnzer;
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
