<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

class PayPalCest extends BaseCest
{
    private $acceptAllCookiesButton = "//button[@id='acceptAllButton']";
    private $paypalPaymentLabel = "//label[@for='payment_oscunzer_paypal']";
    private $loginInput = "//input[@id='email']";
    private $passwordInput = "//input[@id='password']";
    private $loginButton = "//button[@id='btnLogin']";
    private $submitButton = "//button[@id='payment-submit-btn']";

    /**
     * @param AcceptanceTester $I
     * @group PaypalPaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test PayPal payment works');
        $this->_setAcceptance($I);
        $this->_initializeTest();
        $orderPage = $this->_choosePayment($this->paypalPaymentLabel);
        $orderPage->submitOrder();

        $paypalPaymentData = Fixtures::get('paypal_payment');

        // accept cookies
        $I->waitForElement($this->acceptAllCookiesButton);
        $I->click($this->acceptAllCookiesButton);

        // login page
        $I->waitForElement($this->loginInput);
        $I->fillField($this->loginInput, $paypalPaymentData['username']);
        $I->fillField($this->passwordInput, $paypalPaymentData['password']);
        $I->click($this->loginButton);

        // card choose page
        $I->waitForText($this->_getPrice());
        $I->waitForElement($this->submitButton);
        $I->pressKey($this->submitButton, "\n");

        $I->waitForText($this->_getTranslator()->translate('THANK_YOU'));
    }
}
