<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidEsales\Eshop\Application\Model\User as UserModel;
use OxidEsales\Eshop\Application\Model\Basket as BasketModel;
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Exception\RedirectWithMessage;
use OxidSolutionCatalysts\Unzer\PaymentExtensions\UnzerPayment;
use OxidSolutionCatalysts\Unzer\Service\Payment as PaymentService;
use OxidSolutionCatalysts\Unzer\Service\PaymentExtensionLoader;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\Unzer as UnzerService;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    /**
     * @dataProvider executePaymentStatusDataProvider
     */
    public function testRegularExecuteUnzerPaymentFlow(string $expectedValue): void
    {
        $paymentModel = $this->createConfiguredMock(PaymentModel::class, []);
        $paymentExtension = $this->createConfiguredMock(UnzerPayment::class, [
            'execute' => true
        ]);

        $extensionLoader = $this->createPartialMock(PaymentExtensionLoader::class, [
            'getPaymentExtension'
        ]);
        $extensionLoader->expects($this->once())
            ->method('getPaymentExtension')
            ->with($paymentModel)
            ->willReturn($paymentExtension);

        $sessionStub = $this->createPartialMock(Session::class, ['getVariable', 'getBasket', 'getUser']);
        $sessionStub->method('getVariable')->with('PaymentId')->willReturn('examplePaymentId');
        $sessionStub->method('getBasket')->willReturn($this->createConfiguredMock(BasketModel::class, []));
        $sessionStub->method('getUser')->willReturn($this->createConfiguredMock(UserModel::class, []));

        $sut = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs([
                $sessionStub,
                $extensionLoader,
                $this->createPartialMock(Translator::class, []),
                $this->createConfiguredMock(UnzerService::class, []),
                $this->createPartialMock(UnzerSDKLoader::class, [])
            ])
            ->onlyMethods(['removeTemporaryOrder', 'getUnzerPaymentStatus'])
            ->getMock();
        $sut->expects($this->never())->method('removeTemporaryOrder');
        $sut->method('getUnzerPaymentStatus')->willReturn($expectedValue);

        $this->assertSame($expectedValue != 'ERROR', $sut->executeUnzerPayment($paymentModel));
    }

    public function executePaymentStatusDataProvider(): array
    {
        return [
            ['OK'],
            ['NOT_FINISHED'],
            ['ERROR']
        ];
    }

    public function testUnzerRedirectReThrownFlow(): void
    {
        $paymentModel = $this->createConfiguredMock(PaymentModel::class, []);
        $paymentExtension = $this->createPartialMock(UnzerPayment::class, ['execute', 'getUnzerPaymentTypeObject']);
        $paymentExtension->method('execute')->willThrowException(new Redirect("someDestination"));

        $extensionLoader = $this->createPartialMock(PaymentExtensionLoader::class, [
            'getPaymentExtension'
        ]);
        $extensionLoader->expects($this->once())
            ->method('getPaymentExtension')
            ->with($paymentModel)
            ->willReturn($paymentExtension);

        $sut = new PaymentService(
            $this->createConfiguredMock(Session::class, [
                'getUser' => $this->createConfiguredMock(UserModel::class, []),
                'getBasket' => $this->createConfiguredMock(BasketModel::class, [])
            ]),
            $extensionLoader,
            $this->createPartialMock(Translator::class, []),
            $this->createPartialMock(UnzerService::class, []),
            $this->createPartialMock(UnzerSDKLoader::class, [])
        );

        $this->expectException(Redirect::class);

        try {
            $sut->executeUnzerPayment($paymentModel);
        } catch (Redirect $exception) {
            $this->assertSame("someDestination", $exception->getDestination());

            throw $exception;
        }
    }

    public function testUnzerApiExceptionCaseConvertedToRedirectWithMessage(): void
    {
        $unzerException = new \UnzerSDK\Exceptions\UnzerApiException(
            "merchantMessage",
            "clientMessage",
            "specialCode"
        );

        $paymentModel = $this->createConfiguredMock(PaymentModel::class, []);
        $paymentExtension = $this->createPartialMock(UnzerPayment::class, ['execute', 'getUnzerPaymentTypeObject']);
        $paymentExtension->method('execute')->willThrowException($unzerException);

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

        $unzerServiceMock = $this->createPartialMock(UnzerService::class, ['prepareOrderRedirectUrl']);
        $unzerServiceMock->method('prepareOrderRedirectUrl')
            ->willReturn('someUrl');

        $sut = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs([
                $this->createConfiguredMock(Session::class, [
                    'getUser' => $this->createConfiguredMock(UserModel::class, []),
                    'getBasket' => $this->createConfiguredMock(BasketModel::class, [])
                ]),
                $extensionLoader,
                $translatorMock,
                $unzerServiceMock,
                $this->createPartialMock(UnzerSDKLoader::class, [])
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
        $paymentExtension = $this->createPartialMock(UnzerPayment::class, ['execute', 'getUnzerPaymentTypeObject']);
        $paymentExtension->method('execute')->willThrowException(new \Exception("clientMessage"));

        $extensionLoader = $this->createPartialMock(PaymentExtensionLoader::class, [
            'getPaymentExtension'
        ]);
        $extensionLoader->expects($this->once())
            ->method('getPaymentExtension')
            ->with($paymentModel)
            ->willReturn($paymentExtension);

        $unzerServiceMock = $this->createPartialMock(UnzerService::class, ['prepareOrderRedirectUrl']);
        $unzerServiceMock->method('prepareOrderRedirectUrl')
            ->willReturn('someUrl');

        $sut = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs([
                $this->createConfiguredMock(Session::class, [
                    'getUser' => $this->createConfiguredMock(UserModel::class, []),
                    'getBasket' => $this->createConfiguredMock(BasketModel::class, [])
                ]),
                $extensionLoader,
                $this->createPartialMock(Translator::class, []),
                $unzerServiceMock,
                $this->createPartialMock(UnzerSDKLoader::class, [])
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
