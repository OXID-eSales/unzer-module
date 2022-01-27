<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Integration\Controller;

use OxidEsales\Eshop\Application\Controller\PaymentController;
use OxidEsales\TestingLibrary\UnitTestCase;

class PaymentControllerTest extends UnitTestCase
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
