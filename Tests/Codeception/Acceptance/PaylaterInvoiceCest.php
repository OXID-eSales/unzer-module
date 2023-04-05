<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Locator;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

/**
 * @group unzer_module
 * @group PaylaterInvoice
 */
final class PaylaterInvoiceCest extends BaseCest
{
    private $invoicePaymentLabel = "//label[@for='payment_oscunzer_invoice']";

    protected function _getOXID(): array
    {
        return ['oscunzer_invoice'];
    }

    protected function handleB2CElements(AcceptanceTester $I)
    {
        $consentCheckbox = Locator::firstElement('.unzerUI .checkbox');
        $I->click($consentCheckbox);
        // use birthdate 10.10.1990
        $I->fillField('#birthdate_day', 10);
        $monthPicker = Locator::find('button', ['data-id' => 'birthdate_month']);
        $I->click($monthPicker);
        $monthElem = Locator::find('li', ['data-original-index' => 10]);
        $I->click($monthElem);
        $I->fillField('#birthdate_year', 1990);
    }

    /**
     * @param AcceptanceTester $I
     * @group PaylaterInvoicePaymentTest
     */
    public function checkPaymentB2CEURWorks(AcceptanceTester $I)
    {
        $I->wantToTest('PaylaterInvoice B2C EUR payment works');
        $this->_initializeSecuredTest();
        $orderPage = $this->_choosePayment($this->invoicePaymentLabel);
        $this->handleB2CElements($I);
        $orderPage->submitOrder();

        $this->_checkSuccessfulPayment();
    }
}