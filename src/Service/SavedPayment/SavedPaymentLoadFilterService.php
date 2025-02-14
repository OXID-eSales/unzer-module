<?php

namespace OxidSolutionCatalysts\Unzer\Service\SavedPayment;

use OxidSolutionCatalysts\Unzer\Service\SavedPaymentLoadService;
use InvalidArgumentException;

class SavedPaymentLoadFilterService
{
    /** @var SavedPaymentMethodValidator $methodValidator */
    private $methodValidator;

    public function __construct(SavedPaymentMethodValidator $methodValidator)
    {
        $this->methodValidator = $methodValidator;
    }

    public function getPaymentTypeIdLikeExpression(string $savedPaymentMethod): string
    {
        if (!$this->methodValidator->validate(($savedPaymentMethod))) {
            throw new InvalidArgumentException(
                "Invalid savedPaymentMethod SavedPaymentService::getLastSavedPaymentTransaction"
                . ": $savedPaymentMethod"
            );
        }

        if (SavedPaymentLoadService::SAVED_PAYMENT_ALL === $savedPaymentMethod) {
            return "transactionAfterOrder.PAYMENTTYPEID LIKE 's-"
                . SavedPaymentLoadService::SAVED_PAYMENT_PAYPAL . "%'"
                . " OR transactionAfterOrder.PAYMENTTYPEID LIKE 's-"
                . SavedPaymentLoadService::SAVED_PAYMENT_CREDIT_CARD . "%'"
                . " OR transactionAfterOrder.PAYMENTTYPEID LIKE 's-"
                . SavedPaymentLoadService::SAVED_PAYMENT_SEPA_DIRECT_DEBIT . "%'";
        }

        return "transactionAfterOrder.PAYMENTTYPEID LIKE 's-{$savedPaymentMethod}%'";
    }
}
