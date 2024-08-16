<?php

namespace OxidSolutionCatalysts\Unzer\Service\SavedPayment;

use InvalidArgumentException;

/**
 * This service is used to filter out those PaymentTypes from the amount of the given PaymentTypes,
 * so that only one PaymentType per PayPal account or credit card remains.
 */
class SavedPaymentMapper
{
    private const GROUPING_KEY_PAYPAL = 'email';
    private const GROUPING_KEY_CARD = 'number|expiryDate';
    private const GROUPING_KEY_SEPA = 'iban';

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
            $groupingKeyBy = $this->getGroupingKey($paymentType);
            if ($this->paymentTypeMatchesGroupingKey($paymentType, $groupingKeyBy)) {
                $groupedPaymentTypes[$this->getGroupingValue($paymentType)] = $paymentType;
            }
        }

        return $groupedPaymentTypes;
    }

    private function paymentTypeMatchesGroupingKey(array $paymentType, string $groupingKey): bool
    {
        if (stripos($groupingKey, '|')) {
            $paymentTypeKeys = explode('|', $groupingKey);

            return $this->areKeysDefined($paymentTypeKeys, $paymentType);
        }
        return isset($paymentType[$groupingKey]);
    }

    /**
     * the order of if statements is important because email is defined in all paymentypes number only for credit card
     * and iban only for sepa payments
     */
    private function getGroupingKey(array $paymentType): string
    {
        if ($this->paymentTypeMatchesGroupingKey($paymentType, self::GROUPING_KEY_CARD)) {
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

    private function getGroupingValue(array $paymentType): string
    {
        if ($this->paymentTypeMatchesGroupingKey($paymentType, self::GROUPING_KEY_CARD)) {
            $paymentTypeKeys = explode('|', self::GROUPING_KEY_CARD);
            return $paymentType[$paymentTypeKeys[0]] . '|' . $paymentType[$paymentTypeKeys[1]];
        }

        return $paymentType[$this->getGroupingKey($paymentType)];
    }

    private function areKeysDefined(array $requiredKeys, array $array): bool
    {
        $arrayKeys = array_keys($array);
        $missingKeys = array_diff($requiredKeys, $arrayKeys);

        return empty($missingKeys);
    }
}
