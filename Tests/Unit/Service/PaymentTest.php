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
use OxidSolutionCatalysts\Unzer\Service\Unzer as UnzerService;
use OxidSolutionCatalysts\Unzer\Service\PaymentExtensionLoader;
use OxidSolutionCatalysts\Unzer\Service\Transaction as TransactionService;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use PHPUnit\Framework\TestCase;
use UnzerSDK\Unzer;

class PaymentTest extends TestCase
{
    /**
     * @dataProvider executePaymentStatusDataProvider
     */
    public function testRegularExecuteUnzerPaymentFlow(string $expectedValue): void
    {
        $sut = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs([
                $this->getSessionMock(),
                $this->getExtensionLoaderMock(),
                $this->getTranslatorMock(),
                $this->getUnzerServiceMock(),
                $this->getUnzerSDKLoaderMock(),
                $this->getTransactionServiceMock()
            ])
            ->onlyMethods(['removeTemporaryOrder', 'getUnzerPaymentStatus', 'getSessionUnzerPayment', 'executeUnzerPayment'])
            ->getMock();

        $sut->expects($this->never())->method('removeTemporaryOrder');
        $sut->method('getUnzerPaymentStatus')->willReturn($expectedValue);
        $this->assertSame($expectedValue != 'ERROR', $sut->executeUnzerPayment($this->getPaymentModelMock()));
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
        $sut = new PaymentService(
            $this->getSessionMock(),
            $this->getExtensionLoaderMock(),
            $this->getTranslatorMock(),
            $this->getUnzerServiceMock(),
            $this->getUnzerSDKLoaderMock(),
            $this->getTransactionServiceMock()
        );

        $this->expectException(Redirect::class);

        try {
            $sut->executeUnzerPayment($this->getPaymentModelMock());
        } catch (Redirect $exception) {
            $this->assertSame("someUrl", $exception->getDestination());

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

        $paymentExtension = $this->getPaymentExtensionMock();
        $paymentExtension->method('execute')->willThrowException($unzerException);

        $sut = $this->getPaymentServiceMock($this->getTranslatorMock());

        $this->expectException(RedirectWithMessage::class);

        try {
            $sut->executeUnzerPayment($this->getPaymentModelMock());
        } catch (RedirectWithMessage $exception) {
            $this->assertSame("someUrl", $exception->getDestination());
            $this->assertSame("specialTranslation", $exception->getMessageKey());

            throw $exception;
        }
    }

    public function testRegularExceptionCaseConvertedToRedirectWithMessage(): void
    {
        $paymentExtension = $this->getPaymentExtensionMock();
        $paymentExtension->method('execute')->willThrowException(new \Exception("clientMessage"));

        $sut = $this->getPaymentServiceMock($this->getTranslatorMock());

        $this->expectException(RedirectWithMessage::class);

        try {
            $sut->executeUnzerPayment($this->getPaymentModelMock());
        } catch (RedirectWithMessage $exception) {
            $this->assertSame("someUrl", $exception->getDestination());
            $this->assertSame("clientMessage", $exception->getMessageKey());

            throw $exception;
        }
    }

    protected function getSessionMock()
    {
        $sessionStub = $this->createPartialMock(Session::class, ['getVariable', 'getBasket', 'getUser']);
        $sessionStub->method('getVariable')
            ->willReturnCallback(function ($param) {
                return $param === 'PaymentId' ? 'examplePaymentId' : 'someValue';
            });

        $sessionStub->method('getBasket')->willReturn(
            $this->createConfiguredMock(BasketModel::class, [
                'getBasketCurrency' => $this->getBasketCurrency()
            ])
        );
        $sessionStub->method('getUser')->willReturn(
            $this->createConfiguredMock(UserModel::class, [
                'getId' => 'someId'
            ])
        );
        return $sessionStub;
    }

    protected function getTransactionServiceMock()
    {
        return $this->createPartialMock(TransactionService::class, ['writeTransactionToDB']);
    }

    protected function getPaymentServiceMock($translatorMock)
    {
        $basketModel = $this->createConfiguredMock(BasketModel::class, [
            'getBasketCurrency' => $this->getBasketCurrency()
        ]);

        $sut = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs([
                $this->createConfiguredMock(Session::class, [
                    'getUser' => $this->createConfiguredMock(UserModel::class, []),
                    'getBasket' => $basketModel
                ]),
                $this->getExtensionLoaderMock(),
                $translatorMock,
                $this->getUnzerServiceMock(),
                $this->getUnzerSDKLoaderMock(),
                $this->createPartialMock(TransactionService::class, [])
            ])
            ->onlyMethods(['removeTemporaryOrder'])
            ->getMock();
        $sut->expects($this->once())->method('removeTemporaryOrder');
        return $sut;
    }

    protected function getExtensionLoaderMock()
    {
        $paymentModel = $this->getPaymentModelMock();
        $paymentExtension = $this->getPaymentExtensionMock();
        $paymentExtension->method('execute')->willThrowException(new Redirect("someDestination"));

        $extensionLoader = $this->getMockBuilder(PaymentExtensionLoader::class)
            ->setConstructorArgs([
                $this->getUnzerSDKLoaderMock(),
                $this->getUnzerServiceMock()
            ])->onlyMethods(['getPaymentExtension'])
            ->getMock();

        $extensionLoader->expects($this->once())
            ->method('getPaymentExtension')
            ->with($paymentModel)
            ->willReturn($paymentExtension);

        return $extensionLoader;
    }

    protected function getTranslatorMock()
    {
        $translatorMock = $this->createPartialMock(Translator::class, ['translateCode']);
        $translatorMock->method('translateCode')
            ->with("No error id provided", "clientMessage")
            ->willReturn("specialTranslation");
        return $translatorMock;
    }

    protected function getPaymentExtensionMock()
    {
        return $this->createPartialMock(UnzerPayment::class, ['execute', 'getUnzerPaymentTypeObject']);
    }

    protected function getPaymentModelMock()
    {
        return $this->createConfiguredMock(PaymentModel::class, []);
    }

    protected function getUnzerServiceMock()
    {
        $unzerServiceMock = $this->createPartialMock(UnzerService::class, ['prepareOrderRedirectUrl']);
        $unzerServiceMock->method('prepareOrderRedirectUrl')
            ->willReturn('someUrl');
        return $unzerServiceMock;
    }

    protected function getUnzerSDKMock()
    {
        return $this->createConfiguredMock(Unzer::class, []);
    }

    protected function getUnzerSDKLoaderMock()
    {
        $UnzerSDKMock = $this->getUnzerSDKMock();
        $unzerSDKLoaderMock = $this->createPartialMock(UnzerSDKLoader::class, [
            'getUnzerSDK'
        ]);

        $basketCurrency = $this->getBasketCurrency();
        $currencyName = $basketCurrency->name;

        $unzerSDKLoaderMock->method('getUnzerSDK')
            ->with('', $currencyName)
            ->willReturn($UnzerSDKMock);
        return $unzerSDKLoaderMock;
    }
    protected function getBasketCurrency()
    {
        $basketCurrency = new \stdClass();
        $basketCurrency->name = 'EUR';
        return $basketCurrency;
    }
}
