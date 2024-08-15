<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service\SavedPayment;

use OxidSolutionCatalysts\Unzer\Service\DebugHandler;
use OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentFetchPaymentTypeService;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use OxidSolutionCatalysts\Unzer\Tests\Unit\Service\SavedPayment\Model\TestCardPaymentType;
use PHPUnit\Framework\TestCase;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\Paypal;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\Unzer;

class SavedPaymentFetchPaymentTypeServiceTest extends TestCase
{
    /** @var SavedPaymentFetchPaymentTypeService */
    private $fetchPaymentTypeService;

    /** @var UnzerSDKLoader|\PHPUnit\Framework\MockObject\MockObject */
    private $unzerSDKLoader;

    /** @var Unzer|\PHPUnit\Framework\MockObject\MockObject */
    private $unzerSDK;

    /** @var DebugHandler|\PHPUnit\Framework\MockObject\MockObject */
    private $debugHandler;

    protected function setUp(): void
    {
        $this->unzerSDKLoader = $this->createMock(UnzerSDKLoader::class);
        $this->debugHandler = $this->createMock(DebugHandler::class);
        $this->fetchPaymentTypeService = new SavedPaymentFetchPaymentTypeService(
            $this->unzerSDKLoader,
            $this->debugHandler
        );
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentFetchPaymentTypeService::fetchPaymentTypes
     */
    public function testFetchPaymentTypesSuccess()
    {
        $paymentTypes = [
            'crd1' => new TestCardPaymentType('4012001037461114', '03/30', null, 'Visa'),
            'ppl1' => new Paypal(),
            'sdd1' => new SepaDirectDebit('DE89370400440532013000')
        ];

        $savedPaymentTransactions = [
            [
                'PAYMENTTYPEID' => 'crd1',
                'OXPAYMENTTYPE' => 'somePaymentId1',
                'CURRENCY' => 'USD',
                'CUSTOMERTYPE' => 'B2B',
                'OXID' => 'txn1'
            ],
            [
                'PAYMENTTYPEID' => 'ppl1',
                'OXPAYMENTTYPE' => 'somePaymentId2',
                'CURRENCY' => 'EUR',
                'CUSTOMERTYPE' => 'B2C',
                'OXID' => 'txn2'
            ],
            [
                'PAYMENTTYPEID' => 'sdd1',
                'OXPAYMENTTYPE' => 'somePaymentId3',
                'CURRENCY' => 'GBP',
                'CUSTOMERTYPE' => 'B2B',
                'OXID' => 'txn3'
            ]
        ];

        $this->unzerSDK = $this->createMock(Unzer::class);
        $this->unzerSDKLoader
            ->expects($this->exactly(3))
            ->method('getUnzerSDK')
            ->willReturn($this->unzerSDK);

        $this->unzerSDK
            ->expects($this->exactly(3))
            ->method('fetchPaymentType')
            ->will($this->returnCallback(function ($paymentTypeId) use ($paymentTypes) {
                return $paymentTypes[$paymentTypeId];
            }));

        $this->debugHandler
            ->expects($this->exactly(0))
            ->method('log');

        $result = $this->fetchPaymentTypeService->fetchPaymentTypes($savedPaymentTransactions);

        $this->assertArrayHasKey('Visa', $result);
        $this->assertArrayHasKey('paypal', $result);
        $this->assertArrayHasKey('sepa', $result);
        $this->assertCount(1, $result['Visa']);
        $this->assertCount(1, $result['paypal']);
        $this->assertCount(1, $result['sepa']);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentFetchPaymentTypeService::fetchPaymentTypes
     */
    public function testFetchPaymentTypesExceptionHandling()
    {
        $savedPaymentTransactions = [
            [
                'PAYMENTTYPEID' => 'crd1',
                'OXPAYMENTTYPE' => 'somePaymentId1',
                'CURRENCY' => 'USD',
                'CUSTOMERTYPE' => 'B2B',
                'OXID' => 'txn1'
            ]
        ];

        $this->unzerSDK = $this->createMock(Unzer::class);
        $this->unzerSDKLoader
            ->expects($this->once())
            ->method('getUnzerSDK')
            ->willReturn($this->unzerSDK);

        $this->unzerSDK
            ->expects($this->once())
            ->method('fetchPaymentType')
            ->willThrowException(new UnzerApiException('Error', 500));

        $this->debugHandler
            ->expects($this->once())
            ->method('log')
            ->with($this->stringContains('Unknown error code while creating the PaymentList'));

        $this->unzerSDKLoader
            ->expects($this->exactly(1))
            ->method('getUnzerSDK');

        $result = $this->fetchPaymentTypeService->fetchPaymentTypes($savedPaymentTransactions);

        $this->assertEmpty($result);
    }

    /**
     * @covers \OxidSolutionCatalysts\Unzer\Service\SavedPayment\SavedPaymentFetchPaymentTypeService::fetchPaymentTypes
     */
    public function testFetchPaymentTypesInvalidPaymentTypeId()
    {
        $savedPaymentTransactions = [
            [
                'PAYMENTTYPEID' => '',
                'OXPAYMENTTYPE' => 'somePaymentId1',
                'CURRENCY' => 'USD',
                'CUSTOMERTYPE' => 'B2B',
                'OXID' => 'txn1'
            ]
        ];

        $result = $this->fetchPaymentTypeService->fetchPaymentTypes($savedPaymentTransactions);

        $this->assertEmpty($result);
    }
}
