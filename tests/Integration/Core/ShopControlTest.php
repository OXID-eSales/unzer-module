<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Core;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Utils;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\Unzer\Core\ShopControl;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Exception\RedirectWithMessage;
use OxidSolutionCatalysts\Unzer\Exception\UnzerException;

class ShopControlTest extends UnitTestCase
{
    /**
     * @dataProvider customStandardExceptionTestDataProvider
     */
    public function testHandleCustomException($expectedException): void
    {
        /** @var \Throwable $exceptionMock */
        $exceptionMock = $this->createPartialMock($expectedException, ['debugOut']);
        $exceptionMock->expects($this->once())->method('debugOut');
        /* _runOnce is the earliest method call in the ShopControl.
         That is why the exception is simulated in this method.
         !Important, this test only works with Config "iDebug" = 0,
         since in the other cases with exceptions too much OXID is made,
         which makes further mocking very time-consuming.
        */
        $mock = $this->createPartialMock(ShopControl::class, ['_runOnce']);
        $mock->method('_runOnce')->willThrowException($exceptionMock);

        $mock->start();
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

        /* _runOnce is the earliest method call in the ShopControl.
         That is why the exception is simulated in this method.
         !Important, this test only works with Config "iDebug" = 0,
         since in the other cases with exceptions too much OXID is made,
         which makes further mocking very time-consuming.
        */
        $mock = $this->createPartialMock(ShopControl::class, ['_runOnce']);
        $mock->method('_runOnce')->willThrowException(new Redirect($redirectDestination));

        $utilsMock = $this->createPartialMock(Utils::class, ['redirect']);
        $utilsMock->expects($this->once())
            ->method('redirect')
            ->with($redirectDestination)
            ->willReturn('ok');
        Registry::set(Utils::class, $utilsMock);

        $mock->start();
    }

    public function testHandleMessageRedirectException(): void
    {
        $redirectDestination = 'someDestination';

        /* _runOnce is the earliest method call in the ShopControl.
         That is why the exception is simulated in this method.
         !Important, this test only works with Config "iDebug" = 0,
         since in the other cases with exceptions too much OXID is made,
         which makes further mocking very time-consuming.
        */
        $mock = $this->createPartialMock(ShopControl::class, ['_runOnce']);
        $mock->method('_runOnce')->willThrowException(
            new RedirectWithMessage($redirectDestination, 'MESSAGE', ['param1'])
        );

        $utilsMock = $this->createPartialMock(Utils::class, ['redirect']);
        $utilsMock->expects($this->once())
            ->method('redirect')
            ->with($redirectDestination)
            ->willReturn('ok');
        Registry::set(Utils::class, $utilsMock);

        $utilsViewMock = $this->createPartialMock(UtilsView::class, ['addErrorToDisplay']);
        $utilsViewMock->expects($this->once())
            ->method('addErrorToDisplay');
        Registry::set(UtilsView::class, $utilsViewMock);

        $mock->start();
    }
}
