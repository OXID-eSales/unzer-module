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
 * @group InvoiceSecuredCest
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
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Invoice payment works');
        $this->initializeSecuredTest();
        $this->choosePayment($this->invoicePaymentLabel);
        $this->submitOrder();

        $this->checkSuccessfulPayment(15);
    }
}
