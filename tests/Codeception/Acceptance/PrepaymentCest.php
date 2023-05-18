<?php

/**
* Copyright © OXID eSales AG. All rights reserved.
* See LICENSE file for license details.
*/

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use OxidEsales\Codeception\Module\Translation\Translator;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

/**
 * @group unzer_module
 * @group SecondGroup
 */
final class PrepaymentCest extends BaseCest
{
    private $prePaymentLabel = "//label[@for='payment_oscunzer_prepayment']";

    protected function _getOXID(): array
    {
        return ['oscunzer_prpayment'];
    }

    /**
     * @param AcceptanceTester $I
     * @group PrepaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Prepayment payment works');
        $this->_initializeTest();
        $this->_choosePayment($this->prePaymentLabel);
        $this->_submitOrder();

        $this->_checkSuccessfulPayment();
    }
}
