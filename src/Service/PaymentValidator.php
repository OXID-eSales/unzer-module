<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidEsales\Eshop\Application\Model\Payment;

class PaymentValidator
{
    public function isUnzerPayment(Payment $payment): bool
    {
        $isUnzer = false;

        if (strpos(strtolower($payment->getId()), "oscunzer") !== false) {
            $isUnzer = true;
        }

        return $isUnzer;
    }
}
