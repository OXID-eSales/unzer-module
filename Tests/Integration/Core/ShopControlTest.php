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
    /**
     * @dataProvider customStandardExceptionTestDataProvider
     */
    public function testHandleCustomException($expectedException): void
    {
        class_alias(
            \OxidEsales\Eshop\Core\ShopControl::class,
            'OxidSolutionCatalysts\Unzer\Core\ShopControl_parent'
        );
        $mock = $this->createPartialMock(ShopControl::class, ['isAdmin']);
        $mock->method('isAdmin')->willThrowException(new $expectedException());

        try {
            $mock->start();
        } catch (\Exception $exception){
            $this->assertInstanceOf($expectedException, $exception);
            $this->assertLoggedException($expectedException);
        }
    }

    public function customStandardExceptionTestDataProvider(): array
    {
        return [
            [UnzerException::class],
            [StandardException::class]
        ];
    }


    public function testHandleRedirectException(): void
    {
        $redirectDestination = 'someDestination';

        class_alias(
            \OxidEsales\Eshop\Core\ShopControl::class,
            'OxidSolutionCatalysts\Unzer\Core\ShopControl_parent'
        );
        $mock = $this->createPartialMock(ShopControl::class, ['isAdmin']);
        $mock->method('isAdmin')->willThrowException(new Redirect($redirectDestination));

        $utilsMock = $this->createPartialMock(Utils::class, ['redirect']);
        $utilsMock->expects($this->once())
            ->method('redirect')
            ->with($redirectDestination)
            ->willReturn('ok');
        Registry::set(Utils::class, $utilsMock);

        $mock->start();
    }
}
