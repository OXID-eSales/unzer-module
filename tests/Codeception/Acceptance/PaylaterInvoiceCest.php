<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Locator;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;
use UnzerSDK\Constants\CompanyTypes;

/**
 * @group unzer_module
 * @group SecondGroup
 */
final class PaylaterInvoiceCest extends BaseCest
{
    private $invoicePaymentLabel = "//label[@for='payment_oscunzer_invoice']";

    protected function _getOXID(): array
    {
        return ['oscunzer_invoice'];
    }

    protected function fillB2Cdata(AcceptanceTester $I)
    {
        $consentCheckbox = Locator::firstElement('.unzerUI .checkbox');
        $I->scrollTo($consentCheckbox);
        $I->wait(2);
        $I->click($consentCheckbox);
        // use birthdate 10.10.1990
        $I->wait(2);
        $I->fillField('#birthdate_day', 10);
        $monthPicker = '#birthdate_month';
        $I->click($monthPicker);
        $I->wait(2);
        $I->click(['css' => "#consumer_common option[value='10']"]);
        $I->wait(2);
        $I->fillField('#birthdate_year', 1990);
    }

    protected function fillB2Bdata(AcceptanceTester $I)
    {
        // same as B2C
        $this->fillB2Cdata($I);
        // B2B specific
        $companyTypeSelect = '#unzer_company_form';
        $I->wait(2);
        $I->click($companyTypeSelect);
        $I->wait(2);
        $I->click(['css' => "#consumer_b2b option[value='" . CompanyTypes::ASSOCIATION . "']"]);
        $I->click($companyTypeSelect);
        $I->wait(2);
    }

    /**
     * @param AcceptanceTester $I
     * @group PaylaterInvoiceB2CEUR
     */
    public function checkPaymentB2CEURWorks(AcceptanceTester $I)
    {
        $I->wantToTest('PaylaterInvoice B2C EUR payment works');
        $this->_initializeSecuredTest();
        $this->_choosePayment($this->invoicePaymentLabel);
        $this->fillB2Cdata($I);
        $this->_submitOrder();

        $this->_checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group PaylaterInvoiceB2BEUR
     */
    public function checkPaymentB2BEURWorks(AcceptanceTester $I)
    {
        $I->wantToTest('PaylaterInvoice B2B EUR payment works');
        // if user has a company it will trigger B2B mode
        $I->updateInDatabase(
            'oxuser',
            ['oxcompany' => 'ACME'],
            ['oxid' => 'unzersecureuser']
        );
        $this->_initializeSecuredTest();
        $this->_choosePayment($this->invoicePaymentLabel);
        $this->fillB2Bdata($I);
        $this->_submitOrder();

        $this->_checkSuccessfulPayment();
    }
}
