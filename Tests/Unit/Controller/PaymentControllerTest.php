<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @author        OXID Solution Catalyst
 * @link          https://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2021
 *
 * @package unzer-devbox
 * Created: 2021-10-26, ja
 */

namespace OxidSolutionCatalysts\Unzer\Tests\Controller;

use \OxidSolutionCatalysts\Unzer\Controller\PaymentController;

class PaymentControllerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    protected $SubjectUnderTest = null;

    public function setUp(): void
    {
        $this->SubjectUnderTest = new PaymentController;
    }

    public function testSomething()
    {
        self::assertEquals(
            true,
            $this->SubjectUnderTest->doSomething()
        );
    }
}
