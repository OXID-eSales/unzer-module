<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Service;

use OxidEsales\Eshop\Application\Model\Payment as PaymentModel;
use OxidEsales\Eshop\Core\Session;
use OxidSolutionCatalysts\Unzer\Service\PaymentExtensionLoader;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Service\Unzer;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use PHPUnit\Framework\TestCase;

class PaymentExtensionLoaderTest extends TestCase
{
    /**
     * @dataProvider checkPaymentTypeCanBeLoadedDataProvider
     */
    public function testPaymentTypeCanBeLoaded($paymentId, $paymentClass): void
    {
        $paymentStub = $this->createPartialMock(PaymentModel::class, ['getId']);
        $paymentStub->method('getId')->willReturn($paymentId);

        $sessionStub = $this->createPartialMock(Session::class, []);
        $sdkLoaderMock = $this->createPartialMock(UnzerSDKLoader::class, ['getUnzerSDK']);
        $sdkLoaderMock->method('getUnzerSDK')->willReturn(
            $this->createPartialMock(\UnzerSDK\Unzer::class, [])
        );

        $sut = new PaymentExtensionLoader(
            $sessionStub,
            $sdkLoaderMock,
            $this->createPartialMock(Translator::class, []),
            $this->createPartialMock(Unzer::class, [])
        );

        $loadedPaymentType = $sut->getPaymentExtension($paymentStub);
        $this->assertInstanceOf($paymentClass, $loadedPaymentType);
        $this->assertSame($paymentId, $loadedPaymentType->getID());
    }

    public function checkPaymentTypeCanBeLoadedDataProvider(): array
    {
        $testCases = [];

        foreach (PaymentExtensionLoader::UNZERCLASSNAMEMAPPING as $key => $className) {
            $testCases[] = [
                'paymentId' => $key,
                'paymentClass' => $className
            ];
        }

        return $testCases;
    }
}
