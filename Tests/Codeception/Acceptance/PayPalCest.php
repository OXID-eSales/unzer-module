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
 * @group SecondGroup
 */
final class PayPalCest extends BaseCest
{
    private $acceptAllCookiesButton = "//button[@id='acceptAllButton']";
    private $paypalPaymentLabel = "//label[@for='payment_oscunzer_paypal']";
    private $loginInput = "//input[@id='email']";
    private $passwordInput = "//input[@id='password']";
    private $loginButton = "//button[@id='btnLogin']";
    private $submitButton = "//button[@id='payment-submit-btn']";
    private $globalSpinnerDiv = "//div[@data-testid='global-spinner']";

    protected function _getOXID(): array
    {
        return ['oscunzer_paypal'];
    }

    /**
     * @param AcceptanceTester $I
     * @group PaypalPaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test PayPal payment works');
        $this->_initializeTest();
        $orderPage = $this->_choosePayment($this->paypalPaymentLabel);
        $orderPage->submitOrder();

        $paypalPaymentData = Fixtures::get('paypal_payment');

        // accept cookies
        $I->waitForDocumentReadyState();
        $I->waitForElement($this->acceptAllCookiesButton);
        $I->click($this->acceptAllCookiesButton);

        // login page
        $I->waitForDocumentReadyState();
        $I->waitForElement($this->loginInput);
        $I->fillField($this->loginInput, $paypalPaymentData['username']);
        $I->fillField($this->passwordInput, $paypalPaymentData['password']);
        $I->waitForDocumentReadyState();
        $I->click($this->loginButton);
        $I->waitForElementNotVisible($this->globalSpinnerDiv, 60);

        // card choose page
        $I->waitForDocumentReadyState();
        $I->waitForText($this->_getPrice());
        $I->waitForElement($this->submitButton);
        $I->executeJS("document.getElementById('payment-submit-btn').click();");
        $I->waitForElementNotVisible($this->globalSpinnerDiv, 60);
        $I->wait(10);

        $I->waitForDocumentReadyState();
        $this->_checkSuccessfulPayment();
    }
}
