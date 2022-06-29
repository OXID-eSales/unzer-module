<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

class PISCest extends BaseCest
{
    private $pisLabel = "//label[@for='payment_oscunzer_pis']";
    private $banknameInput = "//input[@id='XS2A-bank_code']";
    private $continueButton = "//button[@class='xs2a-submit']";
    private $usernameInput = "//input[@id='XS2A-USER_NAME']";
    private $userpinInput = "//input[@id='XS2A-USER_PIN']";
    private $usertanInput = "//input[@id='XS2A-TAN']";
    private $finishButton = "//a[@class='ui blue button back-btn']";

    /**
     * @param AcceptanceTester $I
     * @group PisPaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test PayPal payment works');
        $I->updateInDatabase('oxpayments', ['OXACTIVE' => 1], ['OXID' => 'oscunzer_pis']);
        $this->_setAcceptance($I);
        $this->_initializeTest();
        $orderPage = $this->_choosePayment($this->pisLabel);
        $orderPage->submitOrder();

        $pisPaymentData = Fixtures::get('pis_payment');

        // first page : choose bank
        $I->waitForText($this->_getPrice());
        $I->waitForElement($this->banknameInput);
        $I->fillField($this->banknameInput, $pisPaymentData['bank_number']);
        $I->click($this->continueButton);

        // second page : log in
        $I->waitForElement($this->usernameInput);
        $I->fillField($this->usernameInput, $pisPaymentData['account_number']);
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

        $I->waitForText($this->_getTranslator()->translate('THANK_YOU'));
    }
}
