<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Core;

use OxidEsales\Eshop\Core\ShopControl as ShopControlAlias;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\Unzer\Core\ShopControl;
use OxidSolutionCatalysts\Unzer\Exception\UnzerException;

class ShopControlTest extends UnitTestCase
{
    public function testHandleBaseException()
    {
        $this->expectExceptionMessage('xxx');

        $mock = $this->createPartialMock(ShopControlAlias::class, [
            'getConfig',
        ]);
        $mock->method('getConfig')->willThrowException(new UnzerException('xxx'));
        $mock->start('someClass', 'getTitleSuffix');
    }
}
