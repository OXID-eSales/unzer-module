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
    private $invoicePaymentLabel = "//label[@for='payment_oscunzer_invoice']";

    protected function _getOXID(): array
    {
        return ['oscunzer_invoice'];
    }

    /**
     * @param AcceptanceTester $I
     * @group InvoicePaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Invoice payment works');
        $this->_initializeTest();
        $orderPage = $this->_choosePayment($this->invoicePaymentLabel);
        $orderPage->submitOrder();

        $this->_checkSuccessfulPayment();
        $I->waitForText(rtrim(strip_tags(sprintf(
            Translator::translate('OSCUNZER_BANK_DETAILS_AMOUNT'),
            $this->_getPrice(),
            $this->_getCurrency()
        ))));
    }
}
