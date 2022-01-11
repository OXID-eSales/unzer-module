<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Exception;

use OxidSolutionCatalysts\Unzer\Service\Unzer as UnzerService;
use PHPUnit\Framework\TestCase;
use UnzerSDK\Unzer;

class UnzerPaymentTypeObjectTest extends TestCase
{
    /**
     * @dataProvider createPaymentTypePaymentTypeObject
     */
    public function testCreatePaymentTypePaymentTypeObject($extensionClass, $resourceClass)
    {
        $resource = new $resourceClass();

        $sdkMock = $this->createPartialMock(Unzer::class, ['createPaymentType']);
        $sdkMock->method('createPaymentType')->with($resource)->willReturn($resource);

        $sut = new $extensionClass(
            $sdkMock,
            $this->createPartialMock(UnzerService::class, [])
        );

        $result = $sut->getUnzerPaymentTypeObject();

        $this->assertInstanceOf($resourceClass, $result);
    }

    public function createPaymentTypePaymentTypeObject(): array
    {
        return [
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\AliPay::class,
                \UnzerSDK\Resources\PaymentTypes\Alipay::class
            ],
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\Bancontact::class,
                \UnzerSDK\Resources\PaymentTypes\Bancontact::class
            ],
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\GiroPay::class,
                \UnzerSDK\Resources\PaymentTypes\Giropay::class
            ],
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\Invoice::class,
                \UnzerSDK\Resources\PaymentTypes\Invoice::class
            ],
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\InvoiceSecured::class,
                \UnzerSDK\Resources\PaymentTypes\InvoiceSecured::class
            ],
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\PayPal::class,
                \UnzerSDK\Resources\PaymentTypes\Paypal::class
            ],
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\PIS::class,
                \UnzerSDK\Resources\PaymentTypes\PIS::class
            ],
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\PrePayment::class,
                \UnzerSDK\Resources\PaymentTypes\Prepayment::class
            ],
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\Przelewy24::class,
                \UnzerSDK\Resources\PaymentTypes\Przelewy24::class
            ],
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\Sofort::class,
                \UnzerSDK\Resources\PaymentTypes\Sofort::class
            ],
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\WeChatPay::class,
                \UnzerSDK\Resources\PaymentTypes\Wechatpay::class
            ],
        ];
    }

    /**
     * @dataProvider fetchPaymentTypeObjectDataProvider
     */
    public function testFetchPaymentTypeObject($extensionClass, $resourceClass)
    {
        $resource = $this->createPartialMock($resourceClass, []);

        $sdkMock = $this->createPartialMock(Unzer::class, ['fetchPaymentType']);
        $sdkMock->method('fetchPaymentType')->with('someId')->willReturn($resource);

        $sut = new $extensionClass(
            $sdkMock,
            $this->createConfiguredMock(UnzerService::class, [
                'getUnzerPaymentIdFromRequest' => 'someId'
            ])
        );

        $result = $sut->getUnzerPaymentTypeObject();

        $this->assertInstanceOf($resourceClass, $result);
    }

    public function fetchPaymentTypeObjectDataProvider(): array
    {
        return [
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\Card::class,
                \UnzerSDK\Resources\PaymentTypes\Card::class
            ],
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\EPS::class,
                \UnzerSDK\Resources\PaymentTypes\EPS::class
            ],
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\Ideal::class,
                \UnzerSDK\Resources\PaymentTypes\Ideal::class
            ],
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\Installment::class,
                \UnzerSDK\Resources\PaymentTypes\InstallmentSecured::class
            ],
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\Sepa::class,
                \UnzerSDK\Resources\PaymentTypes\SepaDirectDebit::class
            ],
            [
                \OxidSolutionCatalysts\Unzer\PaymentExtensions\SepaSecured::class,
                \UnzerSDK\Resources\PaymentTypes\SepaDirectDebitSecured::class
            ],
        ];
    }
}
