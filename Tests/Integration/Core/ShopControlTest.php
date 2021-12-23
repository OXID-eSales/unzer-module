<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Core;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\Unzer\Core\ShopControl;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Exception\UnzerException;

class ShopControlTest extends UnitTestCase
{
    public function testHandleCustomException(): void
    {
        $exMock = $this->createPartialMock(UnzerException::class, ['debugOut']);
        $exMock->expects($this->once())->method('debugOut');

        $this->expectException(get_class($exMock));

        $mock = $this->createPartialMock(ShopControl::class, ['getConfig']);
        $mock->method('getConfig')->willThrowException($exMock);

        $mock->start('someClass', 'getTitleSuffix');
    }

    public function testHandleCustomStandardException(): void
    {
        $exMock = $this->createPartialMock(StandardException::class, ['debugOut']);
        $exMock->expects($this->once())->method('debugOut');

        $this->expectException(get_class($exMock));

        $mock = $this->createPartialMock(ShopControl::class, ['getConfig']);
        $mock->method('getConfig')->willThrowException($exMock);

        $mock->start('someClass', 'getTitleSuffix');
    }

    public function testHandleRedirectException(): void
    {
        $redirectDestination = 'someDestination';

        $mock = $this->createPartialMock(ShopControl::class, ['getConfig',]);
        $mock->method('getConfig')->willThrowException(new Redirect($redirectDestination));

        $utilsMock = $this->createPartialMock(Utils::class, ['redirect']);
        $utilsMock->expects($this->once())
            ->method('redirect')
            ->with($redirectDestination)
            ->willReturn('ok');
        Registry::set(Utils::class, $utilsMock);

        $mock->start('someClass', 'getTitleSuffix');
    }
}
