<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

/**
 * @group unzer_module
 */
final class SofortCest extends BaseCest
{
    private $sofortPaymentLabel = "//label[@for='payment_oscunzer_sofort']";
    private $cookiesAcceptButton = "//button[@class='cookie-modal-accept-all button-primary']";
    private $bankLabel = "//input[@id='account-88888888']";
    private $accountNumberLabel = "//input[@id='BackendFormLOGINNAMEUSERID']";
    private $PINNumberLabel = "//input[@id='BackendFormUSERPIN']";
    private $continueButton = "//button[@class='button-right primary has-indicator']";
    private $kontoOptionInput = "//input[@id='account-1']";
    private $TANInput = "//input[@id='BackendFormTan']";

    protected function _getOXID(): string
    {
        return 'oscunzer_sofort';
    }

    /**
     * @param AcceptanceTester $I
     * @group SofortPaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Sofort payment works');
        $this->_initializeTest();
        $orderPage = $this->_choosePayment($this->sofortPaymentLabel);
        $orderPage->submitOrder();

        $sofortPaymentData = Fixtures::get('sofort_payment');

        //accept cookies
        $I->waitForElement($this->cookiesAcceptButton);
        $I->wait(1);
        $I->canSeeAndClick($this->cookiesAcceptButton);

        // first page : choose bank
        $I->wait(1);
        $I->waitForText($this->_getPrice() . ' ' . $this->_getCurrency());
        $I->waitForElement($this->bankLabel);
        $I->clickWithLeftButton($this->bankLabel);

        // second page : put in account data
        $I->waitForElement($this->accountNumberLabel);
        $I->fillField($this->accountNumberLabel, $sofortPaymentData['account_number']);
        $I->fillField($this->PINNumberLabel, $sofortPaymentData['USER_PIN']);
        $I->click($this->continueButton);

        // third page : choose konto
        $I->waitForElement($this->kontoOptionInput);
        $I->click($this->kontoOptionInput);
        $I->waitForElement($this->continueButton);
        $I->click($this->continueButton);

        // forth page : confirm payment
        $I->waitForElement($this->TANInput);
        $I->fillField($this->TANInput, $sofortPaymentData['USER_TAN']);
        $I->click($this->continueButton);

        $this->_checkSuccessfulPayment();
    }
}
