<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use OxidSolutionCatalysts\Unzer\Service\UnzerPaymentLoader;

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
        /** @var UnzerPaymentLoader $paymentLoader */
        $paymentLoader = ContainerFactory::getInstance()->getContainer()->get(UnzerPaymentLoader::class);

        if ($this->isUnzerPayment() && $paymentLoader->getUnzerPayment($this)->isPaymentTypeAllowed()) {
            return true;
        }
        return false;
    }
}
