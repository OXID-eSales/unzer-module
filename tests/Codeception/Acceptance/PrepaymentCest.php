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

    protected function getOXID(): array
    {
        return ['oscunzer_prepayment'];
    }

    /**
     * @param AcceptanceTester $I
     * @group PrepaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Prepayment payment works');
        $this->initializeTest();
        $this->choosePayment($this->prePaymentLabel);
        $this->submitOrder();

        $this->checkSuccessfulPayment(30);

        // This text doesn't appear on Thankye page for some reason, only in the email
        // possible ToDo: check the thank you page

        $I->waitForText(rtrim(strip_tags(sprintf(
            Translator::translate('OSCUNZER_BANK_DETAILS_AMOUNT'),
            $this->getPrice(),
            $this->getCurrency()
        ))));

    }
}
