<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Exception;

use Exception;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\UnzerPayment;
use PHPUnit\Framework\TestCase;

class UnzerPaymentTest extends TestCase
{
    public function testGetUnzerPaymentTypeObjectForNotImplemented(): void
    {
        $sut = $this->getMockForAbstractClass(UnzerPayment::class, [
            $this->createPartialMock(\UnzerSDK\Unzer::class, []),
            $this->createPartialMock(\OxidSolutionCatalysts\Unzer\Service\Unzer::class, [])
        ]);

        $this->expectException(Exception::class);

        $sut->getUnzerPaymentTypeObject();
    }
}
