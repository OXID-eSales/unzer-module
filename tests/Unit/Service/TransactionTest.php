<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidEsales\Eshop\Core\UtilsDate;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidSolutionCatalysts\Unzer\Model\Transaction;
use OxidSolutionCatalysts\Unzer\Service\Context;
use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\EmbeddedResources\Amount;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;

class TransactionTest extends IntegrationTestCase
{
    private const FIXED_DATE = '2021-12-10 16:44:54';
    private const FIXED_TIMESTAMP = 1639151094;
    private const MOCK_USER_ID = 'userId';
    private const MOCK_ORDER_ID = 'orderId';
    private const MOCK_SHOP_ID = 5;

    private function createModelMock(array $methods): Transaction
    {
        return $this->createPartialMock(Transaction::class, $methods);
    }

    private function createBasicParams(bool $includeMetadata = false): array
    {
        $params = [
            'oxorderid' => self::MOCK_ORDER_ID,
            'oxuserid' => self::MOCK_USER_ID,
            'oxshopid' => self::MOCK_SHOP_ID,
            'oxactiondate' => self::FIXED_DATE,
            'customertype' => 'B2C'
        ];

        if ($includeMetadata) {
            $params['metadata'] = '""';
        }

        return $params;
    }

    private function createPaymentMock(): Payment
    {
        return $this->createConfiguredMock(Payment::class, [
            'getAmount' => $this->createConfiguredMock(Amount::class, [
                'getTotal' => 10.20,
                'getRemaining' => 0.0
            ]),
            'getCurrency' => 'specialCurrency',
            'getId' => 'unzerPaymentId',
            'getStateName' => 'statename',
            'getInitialTransaction' => $this->createConfiguredMock(AbstractTransactionType::class, [
                'getShortId' => 'unzerShortId',
                'getTraceId' => null
            ]),
            'getMetadata' => $this->createConfiguredMock(Metadata::class, [
                'jsonSerialize' => 'metadataJson'
            ]),
            'getCustomer' => $this->createConfiguredMock(Customer::class, [
                'getId' => 'unzerCustomerId'
            ])
        ]);
    }

    private function getExpectedTransactionData(): array
    {
        return [
            'oxorderid' => self::MOCK_ORDER_ID,
            'shortid' => 'unzerShortId',
            'traceid' => null,
            'oxshopid' => self::MOCK_SHOP_ID,
            'oxuserid' => self::MOCK_USER_ID,
            'oxactiondate' => self::FIXED_DATE,
            'amount' => 10.20,
            'currency' => 'specialCurrency',
            'typeid' => 'unzerPaymentId',
            'oxaction' => 'statename',
            'metadata' => 'metadataJson',
            'customerid' => 'unzerCustomerId',
            'customertype' => 'B2C',
            'remaining' => 0.0
        ];
    }

    private function createTransactionServiceMock(Transaction $model, array $params): TransactionService
    {
        $sut = $this->getMockBuilder(TransactionService::class)
            ->setConstructorArgs([
                $this->createConfiguredMock(Context::class, ['getCurrentShopId' => self::MOCK_SHOP_ID]),
                $this->createConfiguredMock(UtilsDate::class, ['getTime' => self::FIXED_TIMESTAMP])
            ])
            ->onlyMethods(['getNewTransactionObject', 'getBasicSaveParameters'])
            ->getMock();

        $sut->method('getNewTransactionObject')->willReturn($model);
        $sut->method('getBasicSaveParameters')->willReturn($params);

        return $sut;
    }

    public function testEmptyPaymentWriteTransactionToDB(): void
    {
        $model = $this->createModelMock(['assign']);
        $params = $this->createBasicParams(true);
        $service = $this->createTransactionServiceMock($model, $params);

        $model->expects($this->once())
            ->method('assign')
            ->with($params);

        $service->writeTransactionToDB(self::MOCK_ORDER_ID, self::MOCK_USER_ID, null);
    }

    public function testWriteTransactionToDBSucceedsWhenTransactionDoesNotExist(): void
    {
        $model = $this->createModelMock(['assign', 'load']);
        $service = $this->createTransactionServiceMock($model, $this->createBasicParams());
        $payment = $this->createPaymentMock();

        $model->expects($this->exactly(2))
            ->method('load')
            ->willReturn(false);

        $model->expects($this->once())
            ->method('assign')
            ->with($this->getExpectedTransactionData());

        $result = $service->writeTransactionToDB(self::MOCK_ORDER_ID, self::MOCK_USER_ID, $payment);
        $this->assertTrue($result);
    }

    public function testWriteTransactionToDBFailsWhenTransactionExists(): void
    {
        $model = $this->createModelMock(['assign', 'load']);
        $service = $this->createTransactionServiceMock($model, $this->createBasicParams());
        $payment = $this->createPaymentMock();

        $model->expects($this->once())
            ->method('load')
            ->willReturn(true);

        $model->expects($this->never())
            ->method('assign');

        $result = $service->writeTransactionToDB(self::MOCK_ORDER_ID, self::MOCK_USER_ID, $payment);
        $this->assertFalse($result);
    }

    public function testGetNewTransactionObject(): void
    {
        $service = new class (
            $this->createPartialMock(Context::class, []),
            $this->createConfiguredMock(UtilsDate::class, [])
        ) extends TransactionService {
            public function testGetNewTransactionObject()
            {
                return $this->getNewTransactionObject();
            }
        };

        $transaction = $service->testGetNewTransactionObject();

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertNull($transaction->getId());
    }
}
