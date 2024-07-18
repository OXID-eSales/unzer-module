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
final class InvoiceCest extends BaseCest
{
    private string $invoicePaymentLabel = "//label[@for='payment_oscunzer_invoice_old']";

    protected function getOXID(): array
    {
        return ['oscunzer_invoice_old'];
    }

    /**
     * This payment method is deprecated and will be removed in the future
     * Test has been deactivated
     *
     * @param AcceptanceTester $I
     * @group InvoicePaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->markTestSkipped("This payment method is deprecated and will be removed in the future");
        $I->wantToTest('Test Invoice (old) payment works');
        $this->initializeTest();
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
