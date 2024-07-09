<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;
use OxidEsales\Codeception\Step\Basket as BasketSteps;

/**
 * @group unzer_module
 * @group SecondGroup
 */
final class PayPalCest extends BaseCest
{
    private string $acceptAllCookiesButton = "//button[@id='acceptAllButton']";
    private string $paypalPaymentLabel = "//label[@for='payment_oscunzer_paypal']";
    private string $loginInput = "#email";
    private string $passwordInput = "#password";
    private string $loginButton = "#btnLogin";
    private string $submitButton = "#payment-submit-btn";
    private string $globalSpinnerDiv = "//div[@data-testid='global-spinner']";

    protected function getOXID(): array
    {
        return ['oscunzer_paypal'];
    }

    /**
     * @group unzer_module
     * @group SecondGroup
     * @group PaypalPaymentTest
     */
    public function testPaymentWorksWithoutSaving(AcceptanceTester $I)
    {
        $I->wantToTest('Test PayPal payment works');
        $this->initializeTest();
        $orderPage = $this->choosePayment($this->paypalPaymentLabel);
        $orderPage->submitOrder();

        $paypalPaymentData = Fixtures::get('paypal_payment');

        // accept cookies
        $I->waitForDocumentReadyState();
        $I->wait(5);
        if ($this->checkElementExists($this->acceptAllCookiesButton, $I)) {
            $I->click($this->acceptAllCookiesButton);
        }

        // login page
        $I->waitForDocumentReadyState();
        $I->waitForElement($this->loginInput);
        $I->fillField($this->loginInput, $paypalPaymentData['username']);
        $I->fillField($this->passwordInput, $paypalPaymentData['password']);
        $I->waitForDocumentReadyState();
        $I->click($this->loginButton);
        $I->waitForElementNotVisible($this->globalSpinnerDiv, 60);

        // paypal choose page
        $I->waitForDocumentReadyState();
        $I->waitForText($this->getPrice());
        $I->waitForElement($this->submitButton);
        $I->executeJS("document.getElementById('payment-submit-btn').click();");
        $I->waitForDocumentReadyState();
        $I->waitForElementNotVisible($this->globalSpinnerDiv, 60);
        $I->wait(10);

        $this->checkSuccessfulPayment();
    }
}
