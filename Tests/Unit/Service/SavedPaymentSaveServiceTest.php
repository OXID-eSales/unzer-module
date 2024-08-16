<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;


use OxidSolutionCatalysts\Unzer\Service\RequestService;
use OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\UserIdService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\Paypal;

class SavedPaymentSaveServiceTest extends TestCase
{
    /** @var Connection|\PHPUnit\Framework\MockObject\MockObject  */
    private $connectionMock;

    /** @var UserIdService|\PHPUnit\Framework\MockObject\MockObject  */
    private $userIdServiceMock;

    /** @var RequestService|\PHPUnit\Framework\MockObject\MockObject  */
    private $requestServiceMock;
    private $savedPaymentSaveService;

    protected function setUp(): void
    {
        // Mock the dependencies
        $this->connectionMock = $this->createMock(Connection::class);
        $this->userIdServiceMock = $this->createMock(UserIdService::class);
        $this->requestServiceMock = $this->createMock(RequestService::class);

        // Instantiate the service with mocked dependencies
        $this->savedPaymentSaveService = new SavedPaymentSaveService(
            $this->connectionMock,
            $this->userIdServiceMock,
            $this->requestServiceMock
        );
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService::getTransactionParameters
     */
    public function testGetTransactionParametersWithPaymentType()
    {
        $paymentType = new Paypal();
        $paymentMock = $this->createMock(Payment::class);

        // Mock the behavior of getPaymentType()
        $paymentMock->method('getPaymentType')->willReturn($paymentType);

        // Mock the behavior of getUserIdByPaymentType()
        $this->userIdServiceMock->method('getUserIdByPaymentType')
            ->with($paymentType)
            ->willReturn('test-user-id');

        // Assuming isSavePaymentSelectedByUserInRequest() returns true
        $this->requestServiceMock->expects($this->once())
            ->method('isSavePaymentSelectedByUserInRequest')
            ->with($paymentType)
            ->willReturn(true);

        // Test getTransactionParameters
        $result = $this->savedPaymentSaveService->getTransactionParameters($paymentMock);

        $expected = [
            'savepaymentuserid' => 'test-user-id',
            'savepayment' => true,
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService::getTransactionParameters
     */
    public function testGetTransactionParametersWithoutPaymentType()
    {
        $paymentMock = $this->createMock(Payment::class);

        // Mock the behavior of getPaymentType() to return null
        $paymentMock->method('getPaymentType')->willReturn(null);

        // Test getTransactionParameters
        $result = $this->savedPaymentSaveService->getTransactionParameters($paymentMock);

        $expected = [
            'savepaymentuserid' => '',
            'savepayment' => false,
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService::unsetSavedPayments
     */
    public function testUnsetSavedPaymentsSuccess()
    {
        $transactionIds = ['trans1', 'trans2'];

        // Mock the executeStatement to return 2 (indicating 2 rows were affected)
        $this->connectionMock->method('executeStatement')
            ->with(
                'UPDATE oscunzertransaction SET SAVEPAYMENT = 0 WHERE OXID IN (:transactionIds)',
                ['transactionIds' => $transactionIds],
                ['transactionIds' => Connection::PARAM_STR_ARRAY]
            )
            ->willReturn(2);

        // Test unsetSavedPayments
        $result = $this->savedPaymentSaveService->unsetSavedPayments($transactionIds);

        $this->assertTrue($result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService::unsetSavedPayments
     */
    public function testUnsetSavedPaymentsFailure()
    {
        $transactionIds = ['trans1', 'trans2'];

        // Mock the executeStatement to return 0 (indicating no rows were affected)
        $this->connectionMock->method('executeStatement')
            ->with(
                'UPDATE oscunzertransaction SET SAVEPAYMENT = 0 WHERE OXID IN (:transactionIds)',
                ['transactionIds' => $transactionIds],
                ['transactionIds' => Connection::PARAM_STR_ARRAY]
            )
            ->willReturn(0);

        // Test unsetSavedPayments
        $result = $this->savedPaymentSaveService->unsetSavedPayments($transactionIds);

        $this->assertFalse($result);
    }
}
