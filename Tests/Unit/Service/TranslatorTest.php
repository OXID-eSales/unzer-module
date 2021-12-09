<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Service;

use OxidEsales\Eshop\Core\Language;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    public function testTranslate(): void
    {
        $languageMock = $this->createPartialMock(Language::class, ['translateString']);
        $languageMock->expects($this->once())
            ->method('translateString')
            ->with('oscunzer_MESSAGEKEY')
            ->willReturn('translation');

        $sut = new Translator($languageMock);
        $this->assertSame(
            'translation',
            $sut->translate('testMESSAGEKEY', 'default message')
        );
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
