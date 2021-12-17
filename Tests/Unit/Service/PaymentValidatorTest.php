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
        $sut = new PaymentValidator();

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
}
