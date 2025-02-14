<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use Doctrine\DBAL\Query\QueryBuilder;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSolutionCatalysts\Unzer\Service\SavedPaymentLoadService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentLoadFilterService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentLoadGroupService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentMethodValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Driver\Result;

class SavedPaymentLoadServiceTest extends TestCase
{
    private SavedPaymentLoadService $savedPaymentLoadService;
    private QueryBuilder|MockObject $queryBuilder;
    private SavedPaymentMethodValidator|MockObject $savedPaymentMethodValidator;
    private SavedPaymentLoadFilterService|MockObject $savedPaymentLoadFilterService;
    private SavedPaymentLoadGroupService|MockObject $savedPaymentLoadGroupService;
    private QueryBuilderFactoryInterface|MockObject $queryBuilderFactory;

    protected function setUp(): void
    {
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $this->queryBuilderFactory->method('create')->willReturn($this->queryBuilder);

        $this->savedPaymentMethodValidator = $this->createMock(SavedPaymentMethodValidator::class);
        $this->savedPaymentLoadFilterService = $this->createMock(SavedPaymentLoadFilterService::class);
        $this->savedPaymentLoadGroupService = $this->createMock(SavedPaymentLoadGroupService::class);

        $this->savedPaymentLoadService = new SavedPaymentLoadService(
            $this->queryBuilderFactory,
            $this->savedPaymentMethodValidator,
            $this->savedPaymentLoadFilterService,
            $this->savedPaymentLoadGroupService
        );
    }

    public function testGetSavedPaymentTransactionsValidMethod()
    {
        $oxUserId = 'user123';
        $savedPaymentMethod = SavedPaymentLoadService::SAVED_PAYMENT_CREDIT_CARD;

        $this->savedPaymentMethodValidator
            ->expects($this->once())
            ->method('validate')
            ->with($savedPaymentMethod)
            ->willReturn(true);

        $this->savedPaymentLoadFilterService
            ->expects($this->once())
            ->method('getPaymentTypeIdLikeExpression')
            ->with($savedPaymentMethod)
            ->willReturn("transactionAfterOrder.PAYMENTTYPEID LIKE 's-crd%'");

        $this->queryBuilder
            ->expects($this->once())
            ->method('select')
            ->with('*')
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->once())
            ->method('from')
            ->with('transactionAfterOrder')
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->once())
            ->method('where')
            ->with('OXUSERID = :oxuserid')
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->with('oxuserid', $oxUserId)
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->once())
            ->method('andWhere')
            ->with("transactionAfterOrder.PAYMENTTYPEID LIKE 's-crd%'")
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->once())
            ->method('orderBy')
            ->with('OXACTIONDATE')
            ->willReturnSelf();

        $statement = $this->createMock(Result::class);
        $statement->expects($this->once())
            ->method('fetchAllAssociative')
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

        $this->queryBuilder
            ->expects($this->once())
            ->method('execute')
            ->willReturn($statement);

        $this->savedPaymentLoadGroupService
            ->expects($this->once())
            ->method('groupByPaymentTypeId')
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

    public function testGetSavedPaymentTransactionsInvalidMethod()
    {
        $oxUserId = 'user123';
        $savedPaymentMethod = 'invalid';

        $this->savedPaymentMethodValidator
            ->expects($this->once())
            ->method('validate')
            ->with($savedPaymentMethod)
            ->willReturn(false);

        $this->expectException(\InvalidArgumentException::class);

        $this->savedPaymentLoadService->getSavedPaymentTransactions($oxUserId, $savedPaymentMethod);
    }

    public function testGetSavedPaymentTransactionsByUserId()
    {
        $savedPaymentUserId = 'user123';

        $this->queryBuilder
            ->expects($this->once())
            ->method('select')
            ->with('OXID')
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->once())
            ->method('from')
            ->with('transactionAfterOrder')
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->once())
            ->method('where')
            ->with('OXUSERID = :savedPaymentUserId')
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->with('savedPaymentUserId', $savedPaymentUserId)
            ->willReturnSelf();

        $statement = $this->createMock(Result::class);
        $statement->expects($this->once())
            ->method('fetchAllAssociative')
            ->willReturn([
                ['OXID' => 'txn1'],
                ['OXID' => 'txn2']
            ]);

        $this->queryBuilder
            ->expects($this->once())
            ->method('execute')
            ->willReturn($statement);

        $result = $this->savedPaymentLoadService->getSavedPaymentTransactionsByUserId($savedPaymentUserId);

        $this->assertEquals(['txn1', 'txn2'], $result);
    }
}
