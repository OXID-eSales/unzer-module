<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSolutionCatalysts\Unzer\Service\SavedPaymentSaveService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentSessionService;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\UserIdService;
use PHPUnit\Framework\TestCase;

class SavedPaymentSaveServiceTest extends TestCase
{
    private SavedPaymentSaveService $service;
    private QueryBuilderFactoryInterface $queryBuilderFactory;
    private QueryBuilder $queryBuilder;
    private UserIdService $userIdService;
    private SavedPaymentSessionService $sessionService;
    private ExpressionBuilder $expressionBuilder;

    protected function setUp(): void
    {
        $this->expressionBuilder = $this->createMock(ExpressionBuilder::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
        $this->userIdService = $this->createMock(UserIdService::class);
        $this->sessionService = $this->createMock(SavedPaymentSessionService::class);

        $this->queryBuilder->method('expr')->willReturn($this->expressionBuilder);
        $this->queryBuilderFactory->method('create')->willReturn($this->queryBuilder);

        $this->service = new SavedPaymentSaveService(
            $this->queryBuilderFactory,
            $this->userIdService,
            $this->sessionService
        );
    }

    public function testUnsetSavedPayments(): void
    {
        $transactionIds = ['id1', 'id2'];

        $this->queryBuilder
            ->expects($this->once())
            ->method('update')
            ->with('oscunzertransaction')
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->once())
            ->method('set')
            ->with('SAVEPAYMENT', ':savePaymentValue')
            ->willReturnSelf();

        $this->expressionBuilder
            ->expects($this->once())
            ->method('in')
            ->with('OXID', ':transactionIds')
            ->willReturn('OXID IN (:transactionIds)');

        $this->queryBuilder
            ->expects($this->once())
            ->method('where')
            ->with('OXID IN (:transactionIds)')
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(
                ['savePaymentValue', 0],
                ['transactionIds', $transactionIds, Connection::PARAM_STR_ARRAY]
            )
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->service->unsetSavedPayments($transactionIds);
        $this->assertTrue($result);
    }

    public function testUnsetSavedPaymentsReturnsFalseWhenNoRowsAffected(): void
    {
        $transactionIds = ['id1'];

        $this->queryBuilder
            ->method('update')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('set')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('where')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('setParameter')
            ->willReturnSelf();

        $this->queryBuilder
            ->method('execute')
            ->willReturn(0);

        $result = $this->service->unsetSavedPayments($transactionIds);
        $this->assertFalse($result);
    }
}
