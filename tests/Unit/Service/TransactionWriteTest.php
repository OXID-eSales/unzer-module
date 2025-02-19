<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\UtilsDate;
use OxidSolutionCatalysts\Unzer\Model\Transaction;
use OxidSolutionCatalysts\Unzer\Service\Context;
use OxidSolutionCatalysts\Unzer\Service\DebugHandler;
use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use OxidSolutionCatalysts\Unzer\Model\Transaction as TransactionModel;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\ConnectionException;

class TransactionWriteTest extends TestCase
{
    private TransactionService|MockObject $transactionService;
    private TransactionModel|MockObject $transactionMock;
    private MockObject $debugHandlerMock;

    protected function setUp(): void
    {
        $this->transactionMock = $this->createMock(Transaction::class);
        $this->debugHandlerMock = $this->createMock(DebugHandler::class);

        $this->transactionService = $this->getMockBuilder(TransactionService::class)
            ->setConstructorArgs([
                $this->createConfiguredMock(Context::class, ['getCurrentShopId' => 5]),
                $this->createConfiguredMock(UtilsDate::class, ['getTime' => time()]),
            ])
            ->onlyMethods(['getNewTransactionObject', 'prepareTransactionOxid', 'getServiceFromContainer'])
            ->getMock();

        $this->transactionService->method('getNewTransactionObject')->willReturn($this->transactionMock);
        $this->transactionService->method('getServiceFromContainer')->willReturn($this->debugHandlerMock);

        $oxid = '12345';
        $this->oOrder = oxNew(\OxidSolutionCatalysts\Unzer\Model\Order::class);
        $this->oOrder->setId($oxid);
    }

    public function testSaveTransactionWithDuplicateEntry(): void
    {
        $params = ['metadata' => '{}', 'otherParam' => 'value'];
        $oxid = '12345';

        $this->transactionService->method('prepareTransactionOxid')->willReturn($oxid);
        $this->transactionMock->method('load')->with($oxid)->willReturn(false);

        // Create UniqueConstraintViolationException directly
        $uniqueConstraintException = new UniqueConstraintViolationException(
            'Duplicate entry',
            $this->createMock(\Doctrine\DBAL\Driver\DriverException::class)
        );

        $dbException = new DatabaseErrorException(
            'duplicate',
            1062,
            $uniqueConstraintException
        );

        $this->transactionMock->method('save')
            ->willThrowException($dbException);

        $this->debugHandlerMock->expects($this->once())
            ->method('log')
            ->with($this->stringContains('saveTransaction: duplicate'));

        try {
            $result = $this->transactionService->saveTransaction($params, $this->oOrder);
            $this->assertTrue($result, 'saveTransaction should return true even when a DatabaseErrorException occurs.');
        } catch (\Throwable $e) {
            $this->fail('saveTransaction threw an unexpected exception: ' . $e->getMessage());
        }
    }

    public function testSaveTransactionWithOtherDatabaseError(): void
    {
        $params = ['metadata' => '{}', 'otherParam' => 'value'];
        $oxid = '67890';

        $this->transactionService->method('prepareTransactionOxid')->willReturn($oxid);
        $this->transactionMock->method('load')->with($oxid)->willReturn(false);

        $connectionException = new ConnectionException(
            'Database connection failed',
            $this->createMock(\Doctrine\DBAL\Driver\DriverException::class)
        );

        $dbException = new DatabaseErrorException(
            'Other database error',
            1062,
            $connectionException
        );

        $this->transactionMock->method('save')
            ->willThrowException($dbException);

        $this->debugHandlerMock->expects($this->once())
            ->method('log')
            ->with($this->stringContains('saveTransaction: Other database error'));

        try {
            $result = $this->transactionService->saveTransaction($params, $this->oOrder);
            $this->assertTrue(
                $result,
                'saveTransaction should return true even when other DatabaseErrorException occurs.'
            );
        } catch (\Throwable $e) {
            $this->fail('saveTransaction threw an unexpected exception: ' . $e->getMessage());
        }
    }

    public function testSaveTransactionSuccess(): void
    {
        $params = ['metadata' => '{}', 'otherParam' => 'value'];
        $oxid = '11223';

        $this->transactionService->method('prepareTransactionOxid')->willReturn($oxid);

        $this->transactionMock->method('load')->with($oxid)->willReturn(false);
        $this->transactionMock->expects($this->once())->method('assign')->with($params);
        $this->transactionMock->expects($this->once())->method('setId')->with($oxid);
        $this->transactionMock->expects($this->once())->method('save');

        $result = $this->transactionService->saveTransaction($params, $this->oOrder);

        $this->assertTrue($result, 'saveTransaction should return true when transaction is successfully saved.');
    }

    public function testGetNewTransactionObject(): void
    {
        $transactionService = new class (
            $this->createPartialMock(Context::class, []),
            $this->createConfiguredMock(UtilsDate::class, [])
        ) extends TransactionService {
            public function testGetNewTransactionObject()
            {
                return $this->getNewTransactionObject();
            }
        };

        $item = $transactionService->testGetNewTransactionObject();
        $this->assertInstanceOf(Transaction::class, $item);
        $this->assertNull($item->getId());
    }
}
