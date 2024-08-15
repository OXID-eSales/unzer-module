<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Connection;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\PaymentTypes\Paypal;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\Resources\Payment;
use OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService;

class SavedPaymentSaveServiceTest extends TestCase
{
    /** @var SavedPaymentSaveService */
    private $savedPaymentSaveService;

    /** @var Connection|\PHPUnit\Framework\MockObject\MockObject */
    private $connection;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->savedPaymentSaveService = new SavedPaymentSaveService($this->connection);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService::getTransactionParameters
     */
    public function testGetTransactionParametersPaypal()
    {
        $email = 'test@example.com';
        $paymentType = $this->createMock(Paypal::class);
        $paymentType->expects($this->once())->method('getEmail')->willReturn($email);

        $payment = $this->createMock(Payment::class);
        $payment->expects($this->once())->method('getPaymentType')->willReturn($paymentType);
        $payment->expects($this->once())->method('getPaymentType')->willReturn($paymentType);

        // Mock the Request trait method isSavePaymentSelectedByUserInRequest
        $this->savedPaymentSaveService = $this->getMockBuilder(SavedPaymentSaveService::class)
            ->onlyMethods(['isSavePaymentSelectedByUserInRequest'])
            ->setConstructorArgs([$this->connection])
            ->getMock();

        $this->savedPaymentSaveService
            ->expects($this->once())
            ->method('isSavePaymentSelectedByUserInRequest')
            ->with($paymentType)
            ->willReturn(true);

        $result = $this->savedPaymentSaveService->getTransactionParameters($payment);

        $expected = [
            'savepaymentuserid' => $email,
            'savepayment' => true,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService::getTransactionParameters
     */
    public function testGetTransactionParametersCard()
    {
        $cardNumber = '4111111111111111';
        $paymentType = $this->createMock(Card::class);
        $paymentType->expects($this->once())->method('getNumber')->willReturn($cardNumber);

        $payment = $this->createMock(Payment::class);
        $payment->expects($this->once())->method('getPaymentType')->willReturn($paymentType);

        // Mock the Request trait method isSavePaymentSelectedByUserInRequest
        $this->savedPaymentSaveService = $this->getMockBuilder(SavedPaymentSaveService::class)
            ->onlyMethods(['isSavePaymentSelectedByUserInRequest'])
            ->setConstructorArgs([$this->connection])
            ->getMock();

        $this->savedPaymentSaveService
            ->expects($this->once())
            ->method('isSavePaymentSelectedByUserInRequest')
            ->with($paymentType)
            ->willReturn(true);

        $result = $this->savedPaymentSaveService->getTransactionParameters($payment);

        $expected = [
            'savepaymentuserid' => $cardNumber,
            'savepayment' => true,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService::getTransactionParameters
     */
    public function testGetTransactionParametersSepaDirectDebit()
    {
        $iban = 'DE89370400440532013000';
        $paymentType = $this->createMock(SepaDirectDebit::class);
        $paymentType->expects($this->once())->method('getIban')->willReturn($iban);

        $payment = $this->createMock(Payment::class);
        $payment->expects($this->once())->method('getPaymentType')->willReturn($paymentType);

        // Mock the Request trait method isSavePaymentSelectedByUserInRequest
        $this->savedPaymentSaveService = $this->getMockBuilder(SavedPaymentSaveService::class)
            ->onlyMethods(['isSavePaymentSelectedByUserInRequest'])
            ->setConstructorArgs([$this->connection])
            ->getMock();

        $this->savedPaymentSaveService
            ->expects($this->once())
            ->method('isSavePaymentSelectedByUserInRequest')
            ->with($paymentType)
            ->willReturn(true);

        $result = $this->savedPaymentSaveService->getTransactionParameters($payment);

        $expected = [
            'savepaymentuserid' => $iban,
            'savepayment' => true,
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService::unsetSavedPayments
     */
    public function testUnsetSavedPayments()
    {
        $transactionIds = ['txn1', 'txn2', 'txn3'];

        $this->connection->expects($this->once())
            ->method('executeStatement')
            ->with(
                'UPDATE oscunzertransaction SET SAVEPAYMENT = 0 WHERE OXID IN (:transactionIds)',
                ['transactionIds' => $transactionIds],
                ['transactionIds' => Connection::PARAM_STR_ARRAY]
            )
            ->willReturn(3);

        $result = $this->savedPaymentSaveService->unsetSavedPayments($transactionIds);

        $this->assertTrue($result);
    }
}
