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
 * @group InvoiceCest
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
        $I->wantToTest('Test Invoice (old) payment works');
        $this->initializeTest();
        $this->choosePayment($this->invoicePaymentLabel);
        $this->submitOrder();

        $this->checkSuccessfulPayment(15);
    }
}
