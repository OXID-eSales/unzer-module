<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidEsales\Eshop\Core\Language;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    /**
     * @dataProvider translateDataProvider
     */
    public function testTranslate($expectedKey, $key): void
    {
        $languageMock = $this->createPartialMock(Language::class, ['translateString']);
        $languageMock->expects($this->once())
            ->method('translateString')
            ->with($expectedKey)
            ->willReturn('translation');

        $sut = new Translator($languageMock);
        $this->assertSame(
            'translation',
            $sut->translate($key, 'default message')
        );
    }

    public function translateDataProvider(): array
    {
        return [
            ['oscunzer_testMESSAGEKEY', 'testMESSAGEKEY'],
            ['oscunzer_apiMESSAGEKEY', 'API.apiMESSAGEKEY'],
            ['oscunzer_corMESSAGEKEY', 'COR.corMESSAGEKEY'],
            ['oscunzer_sdmMESSAGEKEY', 'SDM.sdmMESSAGEKEY'],
        ];
    }

    public function testTranslateNotFound(): void
    {
        $languageMock = $this->createConfiguredMock(Language::class, [
            'isTranslated' => false
        ]);

        $sut = new Translator($languageMock);
        $this->assertSame(
            'default message',
            $sut->translate('testmessagekey', 'default message')
        );
    }
}
