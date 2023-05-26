<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\UnzerPayment;
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

    public function testIsPaymentCurrencyAllowed(): void
    {
        $extension = $this->createConfiguredMock(UnzerPayment::class, [
            'getPaymentCurrencies' => ['EUR', 'USD', 'RUB']
        ]);
        $sut = $this->getSut(
            ['getPaymentExtension' => $extension],
            ['getActiveCurrencyName' => 'RUB']
        );
        $paymentStub = $this->createConfiguredMock(Payment::class, []);

        $this->assertTrue($sut->isPaymentCurrencyAllowed($paymentStub));
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
            [[], true],
            [['EUR', 'USD'], false],
            [['EUR', 'USD', 'someCurrencyName'], true],
        ];
    }

    private function getSut($extensionMethods = [], $contextMethods = [], $settingsMethods = []): PaymentValidator
    {
        return new PaymentValidator(
            $this->createConfiguredMock(
                \OxidSolutionCatalysts\Unzer\Service\PaymentExtensionLoader::class,
                $extensionMethods
            ),
            $this->createConfiguredMock(
                \OxidSolutionCatalysts\Unzer\Service\Context::class,
                $contextMethods
            ),
            $this->createConfiguredMock(
                \OxidSolutionCatalysts\Unzer\Service\ModuleSettings::class,
                $settingsMethods
            )
        );
    }
}
