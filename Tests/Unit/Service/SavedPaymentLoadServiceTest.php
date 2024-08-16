<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use Doctrine\DBAL\Driver\Result;
use OxidSolutionCatalysts\Unzer\Service\SavedPaymentLoadService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentLoadFilterService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentLoadGroupService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentMethodValidator;
use OxidSolutionCatalysts\Unzer\Tests\Unit\Service\SavedPayment\SQL\LoadQueries;
use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Connection;

class SavedPaymentLoadServiceTest extends TestCase
{
    /** @var SavedPaymentLoadService */
    private $savedPaymentLoadService;

    /** @var Connection|\PHPUnit\Framework\MockObject\MockObject */
    private $connection;

    /** @var SavedPaymentMethodValidator|\PHPUnit\Framework\MockObject\MockObject */
    private $savedPaymentMethodValidator;

    /** @var SavedPaymentLoadFilterService|\PHPUnit\Framework\MockObject\MockObject */
    private $savedPaymentLoadFilterService;

    /** @var SavedPaymentLoadGroupService|\PHPUnit\Framework\MockObject\MockObject */
    private $savedPaymentLoadGroupService;

    protected function setUp(): void
    {
        // Mock the DBAL connection
        $this->connection = $this->createMock(Connection::class);

        // Mock the SavedPaymentMethodValidator
        $this->savedPaymentMethodValidator = $this->createMock(SavedPaymentMethodValidator::class);

        // Mock the SavedPaymentLoadFilterService
        $this->savedPaymentLoadFilterService = $this->createMock(SavedPaymentLoadFilterService::class);

        // Mock the SavedPaymentLoadGroupService
        $this->savedPaymentLoadGroupService = $this->createMock(SavedPaymentLoadGroupService::class);

        // Initialize the service with the mocked objects
        $this->savedPaymentLoadService = new SavedPaymentLoadService(
            $this->connection,
            $this->savedPaymentMethodValidator,
            $this->savedPaymentLoadFilterService,
            $this->savedPaymentLoadGroupService
        );
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPaymentLoadService::getSavedPaymentTransactions
     */
    public function testGetSavedPaymentTransactionsValidMethod()
    {
        $oxUserId = 'user123';
        $savedPaymentMethod = SavedPaymentLoadService::SAVED_PAYMENT_CREDIT_CARD;

        // Mock the method validator
        $this->savedPaymentMethodValidator
            ->expects($this->once())
            ->method('validate')
            ->with($savedPaymentMethod)
            ->willReturn(true);

        // Mock the filter service
        $this->savedPaymentLoadFilterService
            ->expects($this->once())
            ->method('getPaymentTypeIdLikeExpression')
            ->with($savedPaymentMethod)
            ->willReturn("transactionAfterOrder.PAYMENTTYPEID LIKE 's-crd%'");

        $sql = LoadQueries::LOAD_TRANSACTIONS_SQL
            . ' AND (transactionAfterOrder.PAYMENTTYPEID LIKE \'s-crd%\') ORDER BY transactionAfterOrder.OXACTIONDATE';

        $statement = $this->createMock(Result::class);
        $statement->expects($this->once())->method('fetchAllAssociative')->willReturn([
            [
                'OXORDERID' => 'order1',
                'OXID' => 'txn1',
                'PAYMENTTYPEID' => 's-crd-123',
                'CURRENCY' => 'USD',
                'CUSTOMERTYPE' => 'B2C',
                'OXPAYMENTTYPE' => 'creditcard',
                'OXACTIONDATE' => '2024-08-01',
                'SAVEPAYMENT' => 1
            ]
        ]);

        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->with($sql, ['oxuserid' => $oxUserId])
            ->willReturn($statement);

        // Mock the grouping service
        $this->savedPaymentLoadGroupService
            ->expects($this->once())
            ->method('groupByPaymentTypeId')
            ->with([
                [
                    'OXORDERID' => 'order1',
                    'OXID' => 'txn1',
                    'PAYMENTTYPEID' => 's-crd-123',
                    'CURRENCY' => 'USD',
                    'CUSTOMERTYPE' => 'B2C',
                    'OXPAYMENTTYPE' => 'creditcard',
                    'OXACTIONDATE' => '2024-08-01',
                    'SAVEPAYMENT' => 1
                ]
            ])
            ->willReturn([
                [
                    'OXORDERID' => 'order1',
                    'OXID' => 'txn1',
                    'PAYMENTTYPEID' => 's-crd-123',
                    'CURRENCY' => 'USD',
                    'CUSTOMERTYPE' => 'B2C',
                    'OXPAYMENTTYPE' => 'creditcard',
                    'OXACTIONDATE' => '2024-08-01',
                    'SAVEPAYMENT' => 1
                ]
            ]);

        $result = $this->savedPaymentLoadService->getSavedPaymentTransactions($oxUserId, $savedPaymentMethod);

        $expected = [
            [
                'OXORDERID' => 'order1',
                'OXID' => 'txn1',
                'PAYMENTTYPEID' => 's-crd-123',
                'CURRENCY' => 'USD',
                'CUSTOMERTYPE' => 'B2C',
                'OXPAYMENTTYPE' => 'creditcard',
                'OXACTIONDATE' => '2024-08-01',
                'SAVEPAYMENT' => 1
            ]
        ];

        $this->assertEquals($expected, $result);
    }


    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPaymentLoadService::getSavedPaymentTransactions
     */
    public function testGetSavedPaymentTransactionsInvalidMethod()
    {
        $oxUserId = 'user123';
        $savedPaymentMethod = 'invalid';

        // Mock the method validator to return false for invalid methods
        $this->savedPaymentMethodValidator
            ->expects($this->once())
            ->method('validate')
            ->with($savedPaymentMethod)
            ->willReturn(false);

        $this->expectException(\InvalidArgumentException::class);

        $this->savedPaymentLoadService->getSavedPaymentTransactions($oxUserId, $savedPaymentMethod);
    }


    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPaymentLoadService::getSavedPaymentTransactionsByUserId
     */
    public function testGetSavedPaymentTransactionsByUserId()
    {
        $savedPaymentUserId = 'user123';

        $sql = LoadQueries::LOAD_TRANSACTIONS_BY_USER_ID_SQL;

        $statement = $this->createMock(Result::class);
        $statement->expects($this->once())->method('fetchAllAssociative')->willReturn([
            ['OXID' => 'txn1'],
            ['OXID' => 'txn2']
        ]);

        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->with($sql, ['savedPaymentUserId' => $savedPaymentUserId])
            ->willReturn($statement);

        $result = $this->savedPaymentLoadService->getSavedPaymentTransactionsByUserId($savedPaymentUserId);

        $this->assertEquals(['txn1', 'txn2'], $result);
    }
}
