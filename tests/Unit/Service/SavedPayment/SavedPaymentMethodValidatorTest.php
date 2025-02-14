<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service\SavedPayment;

use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentMethodValidator;
use OxidSolutionCatalysts\Unzer\Service\SavedPaymentLoadService;
use PHPUnit\Framework\TestCase;

class SavedPaymentMethodValidatorTest extends TestCase
{
    /** @var SavedPaymentMethodValidator */
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new SavedPaymentMethodValidator();
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentMethodValidator::validate
     */
    public function testValidateReturnsTrueForValidPaymentMethods()
    {
        $validMethods = [
            SavedPaymentLoadService::SAVED_PAYMENT_PAYPAL,
            SavedPaymentLoadService::SAVED_PAYMENT_CREDIT_CARD,
            SavedPaymentLoadService::SAVED_PAYMENT_SEPA_DIRECT_DEBIT,
            SavedPaymentLoadService::SAVED_PAYMENT_ALL
        ];

        foreach ($validMethods as $method) {
            $this->assertTrue($this->validator->validate($method), "Expected true for valid payment method: $method");
        }
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentMethodValidator::validate
     */
    public function testValidateReturnsFalseForInvalidPaymentMethods()
    {
        $invalidMethods = [
            'invalid',
            'unknown',
            '123',
            'paypal', // Example of partial or non-matching string
        ];

        foreach ($invalidMethods as $method) {
            $this->assertFalse(
                $this->validator->validate($method),
                "Expected false for invalid payment method: $method"
            );
        }
    }
}
