<?php

namespace OxidSolutionCatalysts\Unzer\Service\SavedPayment;

use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\PaymentTypes\Paypal;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;

class UserIdService
{
    public function getUserIdByPaymentType(BasePaymentType $paymentType): string
    {
        if ($paymentType instanceof Paypal) {
            return $paymentType->getEmail() ?? '';
        } elseif ($paymentType instanceof Card) {
            return $paymentType->getNumber() . '|' . $paymentType->getExpiryDate();
        } elseif ($paymentType instanceof SepaDirectDebit) {
            return $paymentType->getIban() ?? '';
        }

        return '';
    }
}
