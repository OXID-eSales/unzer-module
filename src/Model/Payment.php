<?php

namespace OxidSolutionCatalysts\Unzer\Model;

use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;

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
        if (UnzerHelper::getUnzerObjectbyPaymentId($this->oxpayments__oxid->value)->isPaymentTypeAllowed()) {
            return true;
        }
        return false;
    }
}
