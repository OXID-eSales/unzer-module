<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use Codeception\Util\Locator;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

/**
 * @group unzer_module
 * @group SecondGroup
 */
final class PaylaterInvoiceCest extends BaseCest
{
    private $invoicePaymentLabel = "//label[@for='payment_oscunzer_invoice']";
    private $invoiceInstallment = "//label[@for='payment_oscunzer_installment_paylater']";
    private $IBANInput = "//input[contains(@id, 'unzer-iban-input')]";
    private $holderInput = "//input[contains(@id, 'unzer-holder-input')]";

    protected function _getOXID(): array
    {
        return ['oscunzer_invoice'];
    }

    protected function fillB2Cdata(AcceptanceTester $I)
    {
        $consentCheckbox = Locator::firstElement('.unzerUI .checkbox');
        $I->click($consentCheckbox);
        // use birthdate 10.10.1990
        $I->wait(2);
        $I->fillField('#birthdate_day', 10);
        $monthPicker = Locator::find('button', ['data-id' => 'birthdate_month']);
        $I->click($monthPicker);
        $I->wait(2);
        $I->click(['css' => "#consumer_common li[data-original-index='10']"]);
        $I->wait(2);
        $I->fillField('#birthdate_year', 1990);
    }

    protected function fillInstallementData(AcceptanceTester $I)
    {
        $I->wait(2);
        $I->fillField('#birthdate_day', 10);
        $monthPicker = Locator::find('button', ['data-id' => 'birthdate_month']);
        $I->click($monthPicker);
        $I->wait(2);
        $I->click(['css' => "#oxDateForInstallment li[data-original-index='10']"]);
        $I->wait(2);
        $I->fillField('#birthdate_year', 1990);

        $I->wait(20);
        $planLocator = Locator::find('div', ['id' => 'localID-3']);
        $I->click($planLocator);

        $payment = Fixtures::get('sepa_payment');
        $I->fillField($this->IBANInput, $payment['IBAN']);

        $holder = Fixtures::get('sepa_payment');
        $I->fillField($this->holderInput, $payment['IBANHolder']);
#unzer-iban-input-1709567355550
    }

    protected function fillB2Bdata(AcceptanceTester $I)
    {
        // same as B2C
        $this->fillB2Cdata($I);
        // B2B specific
        $companyTypeSelect = Locator::find('button', ['data-id' => 'unzer_company_form']);
        $I->wait(2);
        $I->click($companyTypeSelect);
        $I->wait(2);
        $I->click(['css' => "#consumer_b2b li[data-original-index='1']"]);
    }

    /**
     * @param AcceptanceTester $I
     * @group PaylaterInvoiceB2CEUR
     */
    public function checkPaymentB2CEURWorks(AcceptanceTester $I)
    {
        $I->wantToTest('PaylaterInvoice B2C EUR payment works');
        $this->_initializeSecuredTest();
        $orderPage = $this->_choosePayment($this->invoicePaymentLabel);
        $this->fillB2Cdata($I);
        $I->scrollTo('#orderConfirmAgbBottom');
        $orderPage->submitOrder();

        $this->_checkSuccessfulPayment();
    }

    /**
     * @group InstallementCest
     * @group SecondGroup
     */
    public function checkPaymentInstallementWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Paylater Invoice Installement (Ratenkauf) works');
        $this->_initializeSecuredTest();
        $orderPage = $this->_choosePayment($this->invoiceInstallment);
        $this->fillInstallementData($I);
        $I->scrollTo('#orderConfirmAgbBottom');
        $orderPage->submitOrder();

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
        $orderPage = $this->_choosePayment($this->invoicePaymentLabel);
        $this->fillB2Bdata($I);
        $orderPage->submitOrder();

        $this->_checkSuccessfulPayment(40);
    }
}
