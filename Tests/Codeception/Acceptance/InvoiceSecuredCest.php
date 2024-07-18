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
 * @group ThirdGroup
 */
final class InvoiceSecuredCest extends BaseCest
{
    private string $invoicePaymentLabel = "//label[@for='payment_oscunzer_invoice-secured']";

    protected function getOXID(): array
    {
        return ['oscunzer_invoice-secured'];
    }

    /**
     * @param AcceptanceTester $I
     * @group InvoiceSecuredPaymentTest
     */
    public function _checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Invoice payment works');
        $this->initializeSecuredTest();
        $orderPage = $this->choosePayment($this->invoicePaymentLabel);
        $orderPage->submitOrder();

        $this->checkSuccessfulPayment();
        $I->waitForText(rtrim(strip_tags(sprintf(
            Translator::translate('OSCUNZER_BANK_DETAILS_AMOUNT'),
            $this->getPrice(),
            $this->getCurrency()
        ))));
    }
}
