<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Unit\Controller;

use OxidEsales\TestingLibrary\UnitTestCase;
use OxidSolutionCatalysts\Unzer\Controller\PaymentController;

class PaymentControllerTest extends UnitTestCase
{
    protected $SubjectUnderTest = null;

    public function setUp(): void
    {
        $this->SubjectUnderTest = new PaymentController();
    }

    public function testSomething()
    {
        self::assertEquals(
            true,
            $this->SubjectUnderTest->doSomething()
        );
    }
}
