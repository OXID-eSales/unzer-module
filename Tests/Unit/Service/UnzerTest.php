<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\Service\Context;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Service\Unzer;
use PHPUnit\Framework\TestCase;

class UnzerTest extends TestCase
{
    /**
     * @dataProvider getPaymentProcedureDataProvider
     */
    public function testGetPaymentProcedure($paymentId, $expectedProcedure)
    {
        $sut = $this->getSut([
            'getPaymentProcedureSetting' => 'special'
        ]);

        $this->assertSame($expectedProcedure, $sut->getPaymentProcedure($paymentId));
    }

    public function getPaymentProcedureDataProvider(): array
    {
        return [
            ['oscunzer_paypal', 'special'],
            ['oscunzer_card', 'special'],
            ['oscunzer_other', ModuleSettings::PAYMENT_DIRECT],
        ];
    }

    private function getSut(array $settings): Unzer
    {
        return new Unzer(
            $this->createPartialMock(Session::class, []),
            $this->createPartialMock(Language::class, []),
            $this->createPartialMock(Context::class, []),
            $this->createConfiguredMock(ModuleSettings::class, $settings)
        );
    }
}
