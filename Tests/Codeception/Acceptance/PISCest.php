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
 * @group FirstGroup
 */
final class PISCest extends BaseCest
{
    private string $pisLabel = "//label[@for='payment_oscunzer_pis']";
    private string $countrySelect = "//select[@id='XS2A-country_id']";
    private string $banknameInput = "//input[@id='XS2A-bank_code']";
    private string $banknameOptionDiv = "//div[@class='xs2a-completion-result']";
    private string $continueButton = "//button[@class='xs2a-submit']";
    private string $usernameInput = "//input[@id='XS2A-USER_NAME']";
    private string $userpinInput = "//input[@id='XS2A-USER_PIN']";
    private string $usertanInput = "//input[@id='XS2A-TAN']";
    private string $finishButton = "//a[@class='ui blue button back-btn']";

    protected function getOXID(): array
    {
        return ['oscunzer_pis'];
    }

    /**
     * THIS TEST WILL ALWAYS FAIL DUE TO AN ONGOING HTTP-503 ON THE PAYMENT SITE
     * Therefor it has been disabled by adding an underscore to the method name
     * Remove the underscore to activate the test again
     *
     * PAYMENT HAS BEEN REMOVED
     *
     * @param AcceptanceTester $I
     * @group PisPaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->markTestSkipped("Skipped test PAYMENT HAS BEEN REMOVED by UNZER");
        $I->wantToTest('Test PIS payment works');

        $this->initializeTest();
        $orderPage = $this->choosePayment($this->pisLabel);
        $orderPage->submitOrder();

        $pisPaymentData = Fixtures::get('pis_payment');

        // first page : choose bank
        $I->waitForText($this->getPrice());
        $I->waitForElement($this->countrySelect);
        $I->selectOption($this->countrySelect, "DE");
        $I->waitForElement($this->banknameInput);
        $I->fillField($this->banknameInput, $pisPaymentData['bank_number']);
        $I->wait(1);
        $I->click($this->continueButton);

        // second page : log in
        $I->waitForElement($this->usernameInput);
        $I->wait(1);
        $I->fillField($this->usernameInput, $pisPaymentData['account_number']);
        $I->waitForElement($this->userpinInput);
        $I->fillField($this->userpinInput, $pisPaymentData['USER_PIN']);
        $I->wait(1);
        $I->click($this->continueButton);

        // third page : confirm payment
        $I->waitForElement($this->usertanInput);
        $I->fillField($this->usertanInput, $pisPaymentData['USER_TAN']);
        $I->click($this->continueButton);

        // forth page : finish payment
        $I->waitForElement($this->finishButton);
        $I->click($this->finishButton);

        $this->checkSuccessfulPayment();
    }
}
