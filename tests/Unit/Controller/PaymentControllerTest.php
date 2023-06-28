<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Controller;

use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidSolutionCatalysts\Unzer\Controller\PaymentController;

class PaymentControllerTest extends IntegrationTestCase
{
    protected $SubjectUnderTest = null;

    public function setUp(): void
    {
        class_alias(
            \OxidEsales\Eshop\Application\Controller\PaymentController::class,
            'OxidSolutionCatalysts\Unzer\Controller\PaymentController_parent'
        );

        $this->SubjectUnderTest = new PaymentController();
    }

    public function testSomething()
    {
        class_alias(
            \OxidEsales\Eshop\Application\Controller\PaymentController::class,
            'OxidSolutionCatalysts\Unzer\Controller\PaymentController_parent'
        );

        self::assertEquals(
            true,
            $this->SubjectUnderTest->doSomething()
        );
    }
}
