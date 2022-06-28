<?php

/**
* Copyright © OXID eSales AG. All rights reserved.
* See LICENSE file for license details.
*/

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

final class PrepaymentCest extends BaseCest
{
    private $prePaymentLabel = "//label[@for='payment_oscunzer_prepayment']";

    /**
     * @param AcceptanceTester $I
     * @group PrepaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Prepayment payment works');
        $I->updateInDatabase('oxpayments', ['OXACTIVE' => 1], ['OXID' => 'oscunzer_prpayment']);
        $this->_setAcceptance($I);
        $this->_initializeTest();
        $orderPage = $this->_choosePayment($this->prePaymentLabel);
        $orderPage->submitOrder();

        $I->waitForText($this->_getTranslator()->translate('THANK_YOU'));

        $I->waitForText(rtrim(strip_tags(sprintf(
            $this->_getTranslator()->translate('OSCUNZER_BANK_DETAILS_AMOUNT'),
            $this->_getPrice(),
            $this->_getCurrency()
        ))));
    }
}
