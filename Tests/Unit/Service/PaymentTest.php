<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\Exception\RedirectWithMessage;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\UnzerPayment;
use OxidSolutionCatalysts\Unzer\Service\Payment as PaymentService;
use OxidSolutionCatalysts\Unzer\Service\PaymentExtensionLoader;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\Unzer as UnzerService;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    /**
     * @dataProvider executePaymentStatusDataProvider
     */
    public function testRegularExecuteUnzerPaymentFlow(bool $expectedValue): void
    {
        $paymentModel = $this->createConfiguredMock(PaymentModel::class, []);
        $paymentExtension = $this->createConfiguredMock(UnzerPayment::class, [
            'execute' => true,
            'checkPaymentStatus' => $expectedValue
        ]);

        $extensionLoader = $this->createPartialMock(PaymentExtensionLoader::class, [
            'getPaymentExtension'
        ]);
        $extensionLoader->expects($this->once())
            ->method('getPaymentExtension')
            ->with($paymentModel)
            ->willReturn($paymentExtension);

        $sut = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs([
                $this->createPartialMock(Session::class, []),
                $extensionLoader,
                $this->createPartialMock(Translator::class, []),
                $this->createConfiguredMock(UnzerService::class, [])
            ])
            ->onlyMethods(['removeTemporaryOrder'])
            ->getMock();
        $sut->expects($this->never())->method('removeTemporaryOrder');

        $this->assertSame($expectedValue, $sut->executeUnzerPayment($paymentModel));
    }

    public function executePaymentStatusDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    public function testUnzerApiExceptionCaseConvertedToRedirectWithMessage(): void
    {
        $unzerException = new \UnzerSDK\Exceptions\UnzerApiException(
            "merchantMessage",
            "clientMessage",
            "specialCode"
        );

        $paymentModel = $this->createConfiguredMock(PaymentModel::class, []);
        $paymentExtension = $this->createConfiguredMock(UnzerPayment::class, [
            'execute' => $this->throwException($unzerException)
        ]);

        $extensionLoader = $this->createPartialMock(PaymentExtensionLoader::class, [
            'getPaymentExtension'
        ]);
        $extensionLoader->expects($this->once())
            ->method('getPaymentExtension')
            ->with($paymentModel)
            ->willReturn($paymentExtension);

        $translatorMock = $this->createPartialMock(Translator::class, ['translateCode']);
        $translatorMock->method('translateCode')
            ->with("specialCode", "clientMessage")
            ->willReturn("specialTranslation");

        $unzerServiceMock = $this->createPartialMock(UnzerService::class, ['prepareRedirectUrl']);
        $unzerServiceMock->method('prepareRedirectUrl')
            ->with(UnzerPayment::CONTROLLER_URL)
            ->willReturn('someUrl');

        $sut = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs([
                $this->createPartialMock(Session::class, []),
                $extensionLoader,
                $translatorMock,
                $unzerServiceMock
            ])
            ->onlyMethods(['removeTemporaryOrder'])
            ->getMock();
        $sut->expects($this->once())->method('removeTemporaryOrder');

        $this->expectException(RedirectWithMessage::class);

        try {
            $sut->executeUnzerPayment($paymentModel);
        } catch (RedirectWithMessage $exception) {
            $this->assertSame("someUrl", $exception->getDestination());
            $this->assertSame("specialTranslation", $exception->getMessageKey());

            throw $exception;
        }
    }

    public function testRegularExceptionCaseConvertedToRedirectWithMessage(): void
    {
        $paymentModel = $this->createConfiguredMock(PaymentModel::class, []);
        $paymentExtension = $this->createConfiguredMock(UnzerPayment::class, [
            'execute' => $this->throwException(new \Exception("clientMessage"))
        ]);

        $extensionLoader = $this->createPartialMock(PaymentExtensionLoader::class, [
            'getPaymentExtension'
        ]);
        $extensionLoader->expects($this->once())
            ->method('getPaymentExtension')
            ->with($paymentModel)
            ->willReturn($paymentExtension);

        $unzerServiceMock = $this->createPartialMock(UnzerService::class, ['prepareRedirectUrl']);
        $unzerServiceMock->method('prepareRedirectUrl')
            ->with(UnzerPayment::CONTROLLER_URL)
            ->willReturn('someUrl');

        $sut = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs([
                $this->createPartialMock(Session::class, []),
                $extensionLoader,
                $this->createPartialMock(Translator::class, []),
                $unzerServiceMock
            ])
            ->onlyMethods(['removeTemporaryOrder'])
            ->getMock();
        $sut->expects($this->once())->method('removeTemporaryOrder');

        $this->expectException(RedirectWithMessage::class);

        try {
            $sut->executeUnzerPayment($paymentModel);
        } catch (RedirectWithMessage $exception) {
            $this->assertSame("someUrl", $exception->getDestination());
            $this->assertSame("clientMessage", $exception->getMessageKey());

            throw $exception;
        }
    }
}
