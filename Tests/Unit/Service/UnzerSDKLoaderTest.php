<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidSolutionCatalysts\Unzer\Service\DebugHandler;
use OxidSolutionCatalysts\Unzer\Service\ModuleSettings;
use OxidSolutionCatalysts\Unzer\Service\UnzerSDKLoader;
use PHPUnit\Framework\TestCase;
use UnzerSDK\Unzer;

class UnzerSDKLoaderTest extends TestCase
{
    public function testSimpleSDKLoading(): void
    {
        $sut = $this->getSut([
            'getShopPrivateKey' => 's-priv-someExampleOfGoodKey',
            'isDebugMode' => false
        ]);

        $loadedSdk = $sut->getUnzerSDK();
        $this->assertInstanceOf(Unzer::class, $loadedSdk);
        $this->assertNull($loadedSdk->getDebugHandler());
        $this->assertFalse($loadedSdk->isDebugMode());
    }

    public function testSimpleSDKLoadingWithWrongKey(): void
    {
        $sut = $this->getSut([
            'getShopPrivateKey' => 'someWrongKey',
            'isDebugMode' => false
        ]);

        $this->expectExceptionMessageMatches("@^Illegal key@i");
        $this->assertInstanceOf(Unzer::class, $sut->getUnzerSDK());
    }

    public function testDebugSDKLoading(): void
    {
        $sut = $this->getSut([
            'getShopPrivateKey' => 's-priv-someExampleOfGoodKey',
            'isDebugMode' => true
        ]);

        $loadedSdk = $sut->getUnzerSDK();
        $this->assertInstanceOf(DebugHandler::class, $loadedSdk->getDebugHandler());
        $this->assertTrue($loadedSdk->isDebugMode());
    }

    protected function getSut($moduleSettingValues): UnzerSDKLoader
    {
        $moduleSettings = $this->createConfiguredMock(ModuleSettings::class, $moduleSettingValues);
        $debugHandler = $this->createPartialMock(DebugHandler::class, []);

        return new UnzerSDKLoader($moduleSettings, $debugHandler);
    }
}
