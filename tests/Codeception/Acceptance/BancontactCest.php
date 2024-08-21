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

    protected function getOXID(): array
    {
        return ['oscunzer_bancontact'];
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     */
    private function prepareBancontactTest(AcceptanceTester $I)
    {
        $this->initializeTest();

        $this->choosePayment($this->bancontactLabel);
        $this->submitOrder();
    }

    /**
     * @param string $name Fixtures name
     * @return void
     */
    private function submitBancontactPayment(string $name)
    {
        $price = str_replace(',', '.', $this->getPrice());
        $fixtures = Fixtures::get($name);

        $this->getAcceptance()->waitForText($price);
        $this->getAcceptance()->waitForElement($this->cardNumberInput);
        $this->getAcceptance()->fillField($this->cardNumberInput, $fixtures['cardnumber']);
        $this->getAcceptance()->selectOption($this->monthExpiredSelect, 12);
        $this->getAcceptance()->selectOption($this->yearExpiredSelect, date('Y'));
        $this->getAcceptance()->fillField($this->cvvCodeInput, $fixtures['CVC']);
        $this->getAcceptance()->click($this->continueButton);

        $this->getAcceptance()->waitForPageLoad();
        $this->getAcceptance()->waitForText($price);
        $this->getAcceptance()->waitForElement($this->continueButton, 30);
        $this->getAcceptance()->click($this->continueButton);
    }

    /**
     * @return void
     */
    private function checkBancontactPayment()
    {
        $this->checkSuccessfulPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group BancontactPaymentTest
     */
    public function checkMastercardPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Bancontact Mastercard payment works');
        $this->prepareBancontactTest($I);
        $this->submitBancontactPayment('mastercard_payment');
        $this->checkBancontactPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group BancontactPaymentTest
     */
    public function checkVisaPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Bancontact Visa payment works');
        $this->prepareBancontactTest($I);
        $this->submitBancontactPayment('visa_payment');
        $this->checkBancontactPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group BancontactPaymentTest
     */
    public function checkMaestroPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Bancontact Maestro payment works');
        $this->prepareBancontactTest($I);
        $this->submitBancontactPayment('maestro_payment');
        $this->checkBancontactPayment();
    }
}
