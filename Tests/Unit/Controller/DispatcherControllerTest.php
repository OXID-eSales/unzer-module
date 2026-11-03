<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Controller;

use OxidEsales\Eshop\Application\Model\Order;
use OxidSolutionCatalysts\Unzer\Service\FlexibleSerializer;
use OxidSolutionCatalysts\Unzer\Service\Transaction;
use OxidSolutionCatalysts\Unzer\Service\UnzerWebhooks;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use OxidSolutionCatalysts\Unzer\Controller\DispatcherController;
use OxidSolutionCatalysts\Unzer\Model\TmpOrder;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use UnzerSDK\Resources\Payment as UnzerResourcePayment;

class DispatcherControllerTest extends TestCase
{
    public function testFinalizeTmpOrderExecutesCorrectly()
    {
        $tmpOrderMock = $this->createMock(TmpOrder::class);
        $tmpOrderMock->method('load')->willReturn(true);

        $paymentMock = $this->createMock(UnzerResourcePayment::class);

        $order = oxNew(Order::class);
        $orderId = 'testOrderId';
        $order->assign(['OXID' => $orderId]);

        $tmpData = [
            'OXID' => 'testTmpOrderId',
            'TMPORDER' => base64_encode(serialize(['order' => $order]))
        ];
        /** @var DispatcherController $controller */
        $controller = $this->getDispatcherControllerPartialMock();

        $result = $controller->finalizeTmpOrder($paymentMock, $tmpOrderMock, $tmpData, false);

        $this->assertEquals('success', $result);
    }

    public function testFinalizeTmpOrderReturnsErrorOnInvalidData()
    {
        $tmpOrderMock = $this->createMock(TmpOrder::class);
        $tmpOrderMock->method('load')->willReturn(false);

        $paymentMock = $this->createMock(UnzerResourcePayment::class);

        $tmpData = [
            'OXID' => 'testTmpOrderId',
            'TMPORDER' => base64_encode(serialize(['invalid_data' => 'value']))
        ];

        $controller = $this->getDispatcherControllerPartialMock();

        $result = $controller->finalizeTmpOrder($paymentMock, $tmpOrderMock, $tmpData, false);

        $this->assertEquals('error', $result);
    }

    /**
     * @dataProvider retrieveUrlProvider
     */
    public function testIsInvalidRequestChecksRetrieveUrl(string $retrieveUrl, bool $expectedInvalid): void
    {
        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->method('isValidTransactionTypeId')->willReturn(true);

        $controller = $this->getMockBuilder(DispatcherController::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $property = new \ReflectionProperty(DispatcherController::class, 'transaction');
        $property->setAccessible(true);
        $property->setValue($controller, $transactionMock);

        $method = new \ReflectionMethod(DispatcherController::class, 'isInvalidRequest');
        $method->setAccessible(true);

        $this->assertSame(
            $expectedInvalid,
            $method->invoke($controller, parse_url($retrieveUrl), 's-chg-1')
        );
    }

    public static function retrieveUrlProvider(): array
    {
        return [
            'production' => ['https://api.unzer.com/v1/payments/s-pay-1/charges/s-chg-1', false],
            'sandbox' => ['https://sbx-api.unzer.com/v1/payments/s-pay-1/charges/s-chg-1', false],
            'sandbox legacy name' => ['https://sbx-api.heidelpay.com/v1/payments/s-pay-1/charges/s-chg-1', false],
            'http' => ['http://api.unzer.com/v1/payments/s-pay-1/charges/s-chg-1', true],
            'foreign host' => ['https://attacker.example/v1/payments/s-pay-1/charges/s-chg-1', true],
            'look-alike host' => ['https://api.unzer.com.attacker.example/v1/payments/s-pay-1', true],
            'no scheme and host' => ['/v1/payments/s-pay-1/charges/s-chg-1', true],
        ];
    }

    public function testIsInvalidRequestRejectsUnknownTypeId(): void
    {
        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->method('isValidTransactionTypeId')->willReturn(false);

        $controller = $this->getMockBuilder(DispatcherController::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $property = new \ReflectionProperty(DispatcherController::class, 'transaction');
        $property->setAccessible(true);
        $property->setValue($controller, $transactionMock);

        $method = new \ReflectionMethod(DispatcherController::class, 'isInvalidRequest');
        $method->setAccessible(true);

        $this->assertTrue(
            $method->invoke($controller, parse_url('https://api.unzer.com/v1/payments/s-pay-1'), 's-pay-1')
        );
    }

    private function getDispatcherControllerPartialMock(): MockObject
    {
        $controller = $this->getMockBuilder(DispatcherController::class)
            ->onlyMethods([
                'getUnzerWebhooks',
                'getUnzerSdkLoader',
                'getFlexibleSerializer',
                'returnError',
                'returnSuccess'
            ])
            ->getMock();

        $unzerWebhooksMock = $this->createMock(UnzerWebhooks::class);
        $controller->method('getUnzerWebhooks')
            ->willReturn($unzerWebhooksMock);

        $controller->method('getUnzerSdkLoader')
            ->willReturn($this->createMock(UnzerSDKLoader::class));

        $controller->method('returnError')
            ->willReturn('error');

        $controller->method('returnSuccess')
            ->willReturn('success');

        $flexibleSerializer = new FlexibleSerializer();
        $controller->method('getFlexibleSerializer')
            ->willReturn($flexibleSerializer);

        return $controller;
    }
}
