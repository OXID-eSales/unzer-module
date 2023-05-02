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
    private $invoicePaymentLabel = "//label[@for='payment_oscunzer_invoice_old']";

    protected function _getOXID(): array
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
    public function _checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Invoice (old) payment works');
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
