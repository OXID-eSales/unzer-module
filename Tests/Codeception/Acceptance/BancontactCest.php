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
 * @group ThirdGroup
 */
final class BancontactCest extends BaseCest
{
    private $bancontactLabel = "//label[@for='payment_oscunzer_bancontact']";
    private $cardNumberInput = "//input[@name='cardNumber']";
    private $monthExpiredSelect = "//select[@class='expirationSelect']";
    private $yearExpiredSelect = ".panel-body .form-row select.expirationSelect:last-of-type";
    private $cvvCodeInput = "//input[@name='cvvCode']";
    private $continueButton = "//button[@class='btn btn-primary']";

    public function _before(AcceptanceTester $I): void
    {
        parent::_before($I);

        // Bancontact is now only available in BE, BaseCest should make all the necessary setup
        // User is assigned to BE
        $user = Fixtures::get('client');
        $I->updateInDatabase(
            'oxuser',
            ['oxcountryid' => 'a7c40f632e04633c9.47194042'], // BE
            ['oxusername' => $user['username']]
        );
    }

    public function _after(AcceptanceTester $I): void
    {
        $user = Fixtures::get('client');
        $I->updateInDatabase(
            'oxuser',
            ['oxcountryid' => 'a7c40f631fc920687.20179984'], // DE
            ['oxusername' => $user['username']]
        );
    }

    protected function _getOXID(): array
    {
        return ['oscunzer_bancontact'];
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     */
    private function _prepareBancontactTest(AcceptanceTester $I)
    {
        $this->_initializeTest();
        $orderPage = $this->_choosePayment($this->bancontactLabel);
        $orderPage->submitOrder();
    }

    /**
     * @param string $name Fixtures name
     * @return void
     */
    private function _submitBancontactPayment(string $name)
    {
        $price = str_replace(',', '.', $this->_getPrice());
        $fixtures = Fixtures::get($name);

        $this->_getAcceptance()->waitForText($price);
        $this->_getAcceptance()->waitForElement($this->cardNumberInput);
        $this->_getAcceptance()->fillField($this->cardNumberInput, $fixtures['cardnumber']);
        $this->_getAcceptance()->selectOption($this->monthExpiredSelect, 12);
        $this->_getAcceptance()->selectOption($this->yearExpiredSelect, date('Y'));
        $this->_getAcceptance()->fillField($this->cvvCodeInput, $fixtures['CVC']);
        $this->_getAcceptance()->click($this->continueButton);

        $this->_getAcceptance()->waitForPageLoad();
        $this->_getAcceptance()->waitForText($price);
        $this->_getAcceptance()->waitForElement($this->continueButton);
        $this->_getAcceptance()->click($this->continueButton);
    }

    /**
     * @return void
     */
    private function _checkBancontactPayment()
    {
        $this->_checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group BancontactPaymentTest
     */
    public function checkMastercardPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Bancontact Mastercard payment works');
        $this->_prepareBancontactTest($I);
        $this->_submitBancontactPayment('mastercard_payment');
        $this->_checkBancontactPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group BancontactPaymentTest
     */
    public function checkVisaPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Bancontact Visa payment works');
        $this->_prepareBancontactTest($I);
        $this->_submitBancontactPayment('visa_payment');
        $this->_checkBancontactPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group BancontactPaymentTest
     */
    public function checkMaestroPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Bancontact Maestro payment works');
        $this->_prepareBancontactTest($I);
        $this->_submitBancontactPayment('maestro_payment');
        $this->_checkBancontactPayment();
    }
}
