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
    private string $savePaypalPayment = "#oscunzersavepayment";
    private string $firstSavedPaypalPayment = "//*[@id='payment-saved-cards']/table/tbody/tr/td[2]/input";

    protected function getOXID(): array
    {
        return ['oscunzer_paypal'];
    }

    /**
     * @group PaypalPaymentTest00
     * @group PaypalPaymentTest0
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

    /**
     * @throws \Exception
     * @group PaypalPaymentTest
     * @group PaypalPaymentTest1
     */
    public function testPaymentWorksWithSavingPayment(AcceptanceTester $I)
    {
        $I->wantToTest('Test PayPal payment works and save payment flag is clickable');
        $this->initializeTest();
        $orderPage = $this->choosePayment($this->paypalPaymentLabel);
        $I->waitForElementClickable($this->savePaypalPayment);
        $I->click($this->savePaypalPayment);
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

        // card choose page
        $I->waitForDocumentReadyState();
        $I->waitForText($this->getPrice());
        $I->waitForElement($this->submitButton);
        $I->executeJS("document.getElementById('payment-submit-btn').click();");
        $I->waitForDocumentReadyState();
        $I->waitForElementNotVisible($this->globalSpinnerDiv, 60);
        $I->wait(10);

        $this->checkSuccessfulPayment();
    }

    /**
     * @group PaypalPaymentTest
     * @group PaypalPaymentTest2
     * @depends testPaymentWorksWithSavingPayment
     */
    public function testSavedPaypalPaymentIsVisibleInAccount(AcceptanceTester $I): void
    {
        $I->wantToTest("Test if saved paypal payment is visible in the user's account");

        $homePage = $this->I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $I->openShop()->openAccountPage();
        $I->click("//*[@id='account_menu']/ul/li[1]/a");
        $I->see("paypal-buyer@unzer.com");

        $I->wantToTest("Test if the saved Paypal payment can pay");
        $I->openShop();
        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($this->I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);
        $this->paymentSelection = $homePage->openMiniBasket()->openCheckout();
        $orderPage = $this->choosePayment($this->paypalPaymentLabel);
        $I->waitForElementClickable($this->firstSavedPaypalPayment);
        $I->wantTo('use saved payment to pay');
        $I->seeAndClick('//*[@id="payment-saved-cards"]/table/tbody/tr/td[2]/input');
        $I->wait(15);
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

        $I->waitForDocumentReadyState();
        $I->waitForText($this->getPrice());
        $I->waitForElement($this->submitButton);
        $I->executeJS("document.getElementById('payment-submit-btn').click();");
        $I->waitForDocumentReadyState();
        $I->waitForElementNotVisible($this->globalSpinnerDiv, 60);
        $I->wait(10);

        $this->checkSuccessfulPayment();
    }

    /**
     * @throws \Exception
     * @group PaypalPaymentTest
     * @group PaypalPaymentTest4
     * @depends testSavedPaypalPaymentIsVisibleInAccount
     */
    public function testSavedPaymentIsAvailableInAccountAndCanBeDeleted(AcceptanceTester $I)
    {
        $I->wantToTest("Test if saved paypal payment is visible in the user's account and can be deleted");
        $I->wait(5);
        $homePage = $this->I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $I->openShop()->openAccountPage();
        $I->click("//*[@id='account_menu']/ul/li[1]/a");
        $I->see("paypal-buyer@unzer.com");

        $I->wait(5);
        $I->seeElement("//*[@id='uzr_collect']/button");
        $I->wait(5);
        $I->submitForm("#uzr_collect", [], 'deletePayment');
        $I->waitForPageLoad();
        $I->dontSee("paypal-buyer@unzer.com");
    }

    /**
     * @throws \Exception
     * @group PaypalPaymentTest
     * @group PaypalPaymentTest5
     * @depends testSavedPaymentIsAvailableInAccountAndCanBeDeleted
     */
    public function testSavedPaypalPaymentIsNotVisibleInCheckoutAfterDelete(AcceptanceTester $I): void
    {
        $I->wantToTest("Test if saved paypal payment is removed from the user's account");

        $homePage = $this->I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($this->I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);
        $this->paymentSelection = $homePage->openMiniBasket()->openCheckout();
        $this->choosePayment($this->paypalPaymentLabel);
        $I->dontSee("paypal-buyer@unzer.com");
    }
}
