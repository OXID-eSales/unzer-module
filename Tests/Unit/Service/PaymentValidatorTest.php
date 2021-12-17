<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidSolutionCatalysts\Unzer\Service\PaymentValidator;
use PHPUnit\Framework\TestCase;

class PaymentValidatorTest extends TestCase
{
    /**
     * @dataProvider isUnzerPaymentDataProvider
     */
    public function testIsUnzerPayment($exampleId, $expectedValue): void
    {
        $sut = $this->getSut();

        $payment = $this->createConfiguredMock(Payment::class, [
            'getId' => $exampleId
        ]);

        $this->assertSame($expectedValue, $sut->isUnzerPayment($payment));
    }

    public function isUnzerPaymentDataProvider(): array
    {
        return [
            ['regularId', false],
            ['oscunzerExampleId', true],
            ['otherPlaceoscunzer', true],
            ['OSCUnzerOtherCase', true]
        ];
    }

    /**
     * @dataProvider isSelectedCurrencyAllowedDataProvider
     */
    public function testIsSelectedCurrencyAllowed($currencies, $expectedValue): void
    {
        $sut = $this->getSut([], ['getActiveCurrencyName' => 'someCurrencyName']);
        $this->assertSame($expectedValue, $sut->isSelectedCurrencyAllowed($currencies));
    }

    public function isSelectedCurrencyAllowedDataProvider(): array
    {
        return [
            [null, true],
            [['EUR', 'USD'], false],
            [['EUR', 'USD', 'someCurrencyName'], true],
        ];
    }

    private function getSut($extensionMethods = [], $contextMethods = []): PaymentValidator
    {
        return new PaymentValidator(
            $this->createConfiguredMock(
                \OxidSolutionCatalysts\Unzer\Service\PaymentExtensionLoader::class,
                $extensionMethods
            ),
            $this->createConfiguredMock(
                \OxidSolutionCatalysts\Unzer\Service\Context::class,
                $contextMethods
            )
        );
    }
}
