<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service\Payment;

use OxidSolutionCatalysts\Unzer\Service\Payment\GetPaymentType;
use OxidSolutionCatalysts\Unzer\Service\Payment;
use OxidEsales\Eshop\Core\Session;
use PHPUnit\Framework\TestCase;

class GetPaymentTypeTest extends TestCase
{
    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\Payment\GetPaymentType::getUnzerPaymentStatus
     */
    public function testGetUnzerPaymentStatus()
    {
        // Arrange
        $unzerPaymentId = 'test_payment_id';
        $orderId = 'test_order_id';
        $expectedStatus = 'payment_status';

        // Mock the Payment service
        $paymentServiceMock = $this->createMock(Payment::class);
        $paymentServiceMock->expects($this->once())
            ->method('getUnzerPaymentStatus')
            ->willReturn($expectedStatus);

        // Mock the Session service
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->expects($this->exactly(2))
            ->method('setVariable')
            ->withConsecutive(
                ['UnzerPaymentId', $unzerPaymentId],
                ['sess_challenge', $orderId]
            );

        // Instantiate the class to be tested with mocked dependencies
        $getPaymentType = new GetPaymentType($paymentServiceMock, $sessionMock);

        // Act
        $actualStatus = $getPaymentType->getUnzerPaymentStatus($unzerPaymentId, $orderId);

        // Assert
        $this->assertEquals($expectedStatus, $actualStatus);
    }
}
