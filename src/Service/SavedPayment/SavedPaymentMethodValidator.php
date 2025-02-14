<?php

namespace OxidSolutionCatalysts\Unzer\Service\SavedPayment;

use OxidSolutionCatalysts\Unzer\Service\SavedPaymentLoadService;

class SavedPaymentMethodValidator
{
    public function validate(string $savedPaymentMethod): bool
    {
        switch ($savedPaymentMethod) {
            case SavedPaymentLoadService::SAVED_PAYMENT_PAYPAL:
            case SavedPaymentLoadService::SAVED_PAYMENT_CREDIT_CARD:
            case SavedPaymentLoadService::SAVED_PAYMENT_SEPA_DIRECT_DEBIT:
            case SavedPaymentLoadService::SAVED_PAYMENT_ALL:
                return true;
            default:
                return false;
        }
    }
}
