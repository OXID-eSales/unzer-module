<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Controller;

use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidSolutionCatalysts\Unzer\Controller\PaymentController;

class PaymentControllerTest extends IntegrationTestCase
{
    protected $SubjectUnderTest = null;

    public function setUp(): void
    {
        $this->SubjectUnderTest = oxNew(PaymentController::class);
    }

    public function testSomething()
    {
        self::assertEquals(
            true,
            $this->SubjectUnderTest->doSomething()
        );
    }
}
