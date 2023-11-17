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
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidSolutionCatalysts\Unzer\Core\ShopControl;
use OxidSolutionCatalysts\Unzer\Exception\Redirect;
use OxidSolutionCatalysts\Unzer\Exception\RedirectWithMessage;
use OxidSolutionCatalysts\Unzer\Exception\UnzerException;

class ShopControlTest extends IntegrationTestCase
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
        $someException = new $expectedException();

        $mock = $this->createPartialMock(ShopControl::class, ['isAdmin', 'logException']);
        $mock->method('isAdmin')->willThrowException($someException);

        $this->addToAssertionCount(1);
        $mock->expects($this->once())->method('logException')->with($someException);

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

    public function testHandleMessageRedirectException(): void
    {
        $redirectDestination = 'someDestination';

        class_alias(
            \OxidEsales\Eshop\Core\ShopControl::class,
            'OxidSolutionCatalysts\Unzer\Core\ShopControl_parent'
        );
        $mock = $this->createPartialMock(ShopControl::class, ['isAdmin']);
        $mock->method('isAdmin')->willThrowException(
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