<?php

namespace OxidSolutionCatalysts\Unzer\Service\SavedPayment;

use InvalidArgumentException;

/**
 * This service is used to filter out those PaymentTypes from the amount of the given PaymentTypes,
 * so that only one PaymentType per PayPal account or credit card remains.
 */
class SavedPaymentMapper
{
    /** @var SavedPaymentMethodValidator $methodValidator */
    private $methodValidator;

    private const GROUPING_KEY_PAYPAL = 'email';
    private const GROUPING_KEY_CARD = 'number';
    private const GROUPING_KEY_SEPA = 'iban';

    public function __construct(SavedPaymentMethodValidator $methodValidator)
    {
        $this->methodValidator = $methodValidator;
    }

    public function groupPaymentTypes(array $paymentTypes): array
    {
        $groupedPaymentTypes = [];
        foreach ($paymentTypes as $paymentMethod => $paymentTypesOfMethod) {
            $groupedPaymentTypes[$paymentMethod] = $this->groupPaymentTypesInner(
                $paymentTypesOfMethod
            );
        }

        return $groupedPaymentTypes;
    }

    private function groupPaymentTypesInner(array $paymentTypes): array
    {
        $groupedPaymentTypes = [];
        foreach ($paymentTypes as $paymentType) {
            $groupingKeyBy = $this->getGroupingKeyByChecking($paymentType);
            if ($this->paymentTypeMatchesGroupingKey($paymentType, $groupingKeyBy)) {
                $groupedPaymentTypes[$paymentType[$groupingKeyBy]] = $paymentType;
            }
        }

        return $groupedPaymentTypes;
    }

    private function paymentTypeMatchesGroupingKey(array $paymentType, ?string $groupingKey): bool
    {
        return isset($paymentType[$groupingKey]);
    }

    /**
     * the order of if statements is important because email is defined in all paymentypes number only for credit card
     * and iban only for sepa payments
     */
    private function getGroupingKeyByChecking(array $paymentType): string
    {
        if (isset($paymentType[self::GROUPING_KEY_CARD])) {
            return self::GROUPING_KEY_CARD;
        } elseif (isset($paymentType[self::GROUPING_KEY_SEPA])) {
            return self::GROUPING_KEY_SEPA;
        } elseif (isset($paymentType[self::GROUPING_KEY_PAYPAL])) {
            return self::GROUPING_KEY_PAYPAL;
        }

        throw new InvalidArgumentException(
            'cant determine grouping key in ' . __CLASS__ . '::' . __METHOD__
        );
    }
}
