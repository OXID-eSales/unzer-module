<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use Doctrine\DBAL\Connection;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentSessionService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\UserIdService;
use OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService;
use PHPUnit\Framework\TestCase;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

class SavedPaymentSaveServiceTest extends TestCase
{
    private $connectionMock;
    private $userIdServiceMock;
    private $sessionServiceMock;
    private $savedPaymentSaveService;

    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(Connection::class);
        $this->userIdServiceMock = $this->createMock(UserIdService::class);
        $this->sessionServiceMock = $this->createMock(SavedPaymentSessionService::class);

        $this->savedPaymentSaveService = new SavedPaymentSaveService(
            $this->connectionMock,
            $this->userIdServiceMock,
            $this->sessionServiceMock
        );
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService::getTransactionParameters
     */
    public function testGetTransactionParametersWhenSavedPaymentIsTrue()
    {
        $paymentMock = $this->createMock(Payment::class);
        $paymentTypeMock = $this->createMock(BasePaymentType::class);

        $this->sessionServiceMock->method('isSavedPayment')->willReturn(true);
        $paymentMock->method('getPaymentType')->willReturn($paymentTypeMock);
        $this->userIdServiceMock->method('getUserIdByPaymentType')->with($paymentTypeMock)->willReturn('user123');

        $result = $this->savedPaymentSaveService->getTransactionParameters($paymentMock);

        $this->assertSame(
            [
                'savepaymentuserid' => 'user123',
                'savepayment' => '1',
            ],
            $result
        );
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService::getTransactionParameters
     */
    public function testGetTransactionParametersWhenSavedPaymentIsFalse()
    {
        $paymentMock = $this->createMock(Payment::class);

        $this->sessionServiceMock->method('isSavedPayment')->willReturn(false);

        $result = $this->savedPaymentSaveService->getTransactionParameters($paymentMock);

        $this->assertSame([], $result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService::unsetSavedPayments
     */
    public function testUnsetSavedPayments()
    {
        $transactionIds = ['tx1', 'tx2', 'tx3'];

        $this->connectionMock->expects($this->once())
            ->method('executeStatement')
            ->with(
                $this->stringContains('UPDATE oscunzertransaction SET SAVEPAYMENT = 0 WHERE OXID IN (:transactionIds)'),
                ['transactionIds' => $transactionIds],
                ['transactionIds' => Connection::PARAM_STR_ARRAY]
            )
            ->willReturn(3); // Anzahl der aktualisierten Zeilen

        $result = $this->savedPaymentSaveService->unsetSavedPayments($transactionIds);

        $this->assertTrue($result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService::unsetSavedPayments
     */
    public function testUnsetSavedPaymentsReturnsFalseWhenNoRowsAffected()
    {
        $transactionIds = ['tx1', 'tx2', 'tx3'];

        $this->connectionMock->expects($this->once())
            ->method('executeStatement')
            ->willReturn(0); // Keine Zeilen aktualisiert

        $result = $this->savedPaymentSaveService->unsetSavedPayments($transactionIds);

        $this->assertFalse($result);
    }
}
