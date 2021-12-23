<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Core;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\Unzer\Core\ShopControl;
use OxidSolutionCatalysts\Unzer\Exception\UnzerException;

class ShopControlTest extends UnitTestCase
{
    public function testHandleCustomException()
    {
        $this->expectExceptionMessage('xxx');

        $mock = $this->createPartialMock(ShopControl::class, [
            'getConfig',
        ]);
        $mock->method('getConfig')->willThrowException(new UnzerException('xxx'));
        $mock->start('someClass', 'getTitleSuffix');
    }

    public function testHandleCustomStandardException()
    {
        $this->expectExceptionMessage('xxx');

        $mock = $this->createPartialMock(ShopControl::class, [
            'getConfig',
        ]);

        $mock->method('getConfig')->willThrowException(new StandardException('xxx'));
        $mock->start('someClass', 'getTitleSuffix');
    }
}
