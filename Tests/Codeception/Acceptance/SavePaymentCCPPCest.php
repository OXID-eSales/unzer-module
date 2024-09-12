<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\modules\osc\unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Page\Page;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance\BaseCest;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;
use OxidEsales\Codeception\Step\Basket as BasketSteps;

/**
 * @group unzer_module
 * @group SecondGroup
 * @group SavePayment
 */
final class SavePaymentCCPPCest extends \OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance\AbstractCreditCardCest
{
    public string $cardPaymentLabel = "//label[@for='payment_oscunzer_card']";
    public string $cardNumberIframe = "//iframe[contains(@id, 'unzer-number-iframe')]";
    public string $expireDateIframe = "//iframe[contains(@id, 'unzer-expiry-iframe')]";
    public string $CVCIframe = "//iframe[contains(@id, 'unzer-cvc-iframe')]";
    public string $cardNumberInput = "//input[@id='card-number']";
    public string $expireDateInput = "//input[@id='card-expiry-date']";
    public string $CVCInput = "//input[@id='card-ccv']";
    public string $toCompleteAuthentication = "Click here to complete authentication.";
    public string $newCard = "#addNewCardCheckboxLabel";
    public string $saveCard = "#oscunzersavepayment";
    public string $useSavedCardForPayment = '//*[@id="payment-saved-cards"]/table/tbody/tr/td[3]/input';
    public string $acceptAllCookiesButton = "//button[@id='acceptAllButton']";
    public string $paypalPaymentLabel = "//label[@for='payment_oscunzer_paypal']";
    public string $loginInput = "#email";
    private string $passwordInput = "#password";
    private string $loginButton = "#btnLogin";
    private string $submitButton = "#payment-submit-btn";
    private string $globalSpinnerDiv = "//div[@data-testid='global-spinner']";
    private string $savePaypalPayment = "#oscunzersavepayment";
    private string $firstSavedPaypalPayment = "//*[@id='payment-saved-cards']/table/tbody/tr/td[3]/input";
    private string $savedPaymentsLocator = "//*[@id='account_menu']/ul/li[1]/a";
    private string $savePayPalAccountInNextStepInputSelector = "//*[@id='oscunzersavepayment']";
    private string $savePaymentDeletePayPalButtonSelector = "button.btn-danger.delete-paypal";
    private string $savePaymentDeleteCreditCardButtonSelector = "button.btn-danger.delete-cc";
    public string $cardNumberHolder = "//input[@id='card-holder']";

    /**
     * @group unzer_module
     * @group SecondGroup
     * @throws \Exception
     * @group SavePaymentCCPPCest
     */
    public function testPaymentWorksWithSavingPayment(AcceptanceTester $I)
    {
        $I->wantToTest('if PayPal payment works and save payment flag is clickable');
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
     * @group unzer_module
     * @group SecondGroup
     * @group SavePaymentCCPPCest
     * @depends testPaymentWorksWithSavingPayment
     */
    public function testSavedPaypalPaymentIsVisibleInAccount(AcceptanceTester $I): void
    {
        $I->wantToTest("if saved paypal payment is visible in the user's account");

        $homePage = $this->I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $I->openShop()->openAccountPage();
        $I->click("//*[@id='account_menu']/ul/li[1]/a");
        $I->see($clientData['username']);

        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($this->I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);
        $I->openShop()->openMiniBasket()->openCheckout();
        $orderPage = $this->choosePayment($this->paypalPaymentLabel);
        $I->waitForElementClickable($this->firstSavedPaypalPayment);
        $I->wantTo('use saved payment to pay');
        $I->seeAndClick($this->savePayPalAccountInNextStepInputSelector);
        $I->wait(2);
        $orderPage->submitOrder();

        $paypalPaymentData = Fixtures::get('paypal_payment');

        // accept cookies
        $I->waitForDocumentReadyState();
        $I->wait(5);
        if ($this->checkElementExists($this->acceptAllCookiesButton, $I)) {
            $I->click($this->acceptAllCookiesButton);
        }

        // login page
        $I->wait(30);
        $I->waitForElement($this->loginInput);
        $I->fillField($this->loginInput, $paypalPaymentData['username']);
        $I->fillField($this->passwordInput, $paypalPaymentData['password']);
        $I->waitForDocumentReadyState();
        $I->click($this->loginButton);
        $I->waitForElementNotVisible($this->globalSpinnerDiv, 60);

        $I->wait(30);
        $I->waitForText($this->getPrice());
        $I->waitForElement($this->submitButton);
        $I->executeJS("document.getElementById('payment-submit-btn').click();");
        $I->wait(30);
        $I->waitForElementNotVisible($this->globalSpinnerDiv, 60);
        $I->wait(10);

        $this->checkSuccessfulPayment();
    }

    /**
     * @group unzer_module
     * @group SecondGroup
     * @group SavePaymentCCPPCest
     * @depends testSavedPaypalPaymentIsVisibleInAccount
     */
    public function testCannotAddSamePPSecondTime(AcceptanceTester $I): void
    {
        $I->wantToTest("if user cannot save Paypal Account twice");

        $homePage = $this->I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $I->openShop()->openAccountPage();
        $I->click("//*[@id='account_menu']/ul/li[1]/a");
        $I->see("paypal-buyer@unzer.com");
        $I->openShop();

        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($this->I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);
        $I->openShop()->openMiniBasket()->openCheckout();
        $orderPage = $this->choosePayment($this->paypalPaymentLabel);
        $I->waitForElementClickable($this->firstSavedPaypalPayment);
        $I->wantTo('use new Paypal account');
        $I->seeAndClick($this->savePaypalPayment);
        $orderPage->submitOrder();

        $paypalPaymentData = Fixtures::get('paypal_payment');

        // accept cookies
        $I->waitForDocumentReadyState();
        $I->wait(5);
        if ($this->checkElementExists($this->acceptAllCookiesButton, $I)) {
            $I->click($this->acceptAllCookiesButton);
        }

        // login page
        $I->wait(30);
        $I->waitForElement($this->loginInput);
        $I->fillField($this->loginInput, $paypalPaymentData['username']);
        $I->fillField($this->passwordInput, $paypalPaymentData['password']);
        $I->wait(30);
        $I->click($this->loginButton);
        $I->waitForElementNotVisible($this->globalSpinnerDiv, 60);

        $I->waitForDocumentReadyState();
        $I->waitForText($this->getPrice());
        $I->waitForElement($this->submitButton);
        $I->executeJS("document.getElementById('payment-submit-btn').click();");
        $I->wait(30);
        $I->waitForElementNotVisible($this->globalSpinnerDiv, 60);
        $I->wait(10);

        $this->checkSuccessfulPayment();

        $I->openShop()->openAccountPage();
        $I->click("//*[@id='account_menu']/ul/li[1]/a");
        $I->see("paypal-buyer@unzer.com");

        $deleteButtons = $I->grabMultiple($this->savePaymentDeletePayPalButtonSelector);
        $I->assertCount(1, $deleteButtons);
    }

    /**
     * @group unzer_module
     * @group SecondGroup
     * @throws \Exception
     * @group SavePaymentCCPPCest
     * @depends testCannotAddSamePPSecondTime
     */
    public function testSavedPaymentIsAvailableInAccountAndCanBeDeleted(AcceptanceTester $I)
    {
        $I->wantToTest("if saved paypal payment is visible in the user's account and can be deleted");
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
     * @group unzer_module
     * @group SecondGroup
     * @group   SavePaymentCCPPCest
     * @depends testPaymentCardCanSavePayment
     * @throws \Exception
     */
    public function testPaymentUsingSavedCardWorks(AcceptanceTester $I)
    {
        $I->wantToTest('if user can pay with a saved card');
        $this->updateArticleStockAndFlag(15, 1);
        $this->initializeTest();

        $this->useSavedCardToPay();
        $this->checkCreditCardPayment();
    }

    /**
     * @group unzer_module
     * @group SecondGroup
     * @group   SavePaymentCCPPCest
     * @depends testSavedPaypalPaymentIsVisibleInAccount
     * @throws \Exception
     */
    public function testPaymentCardCanSavePayment(AcceptanceTester $I)
    {
        $I->wantToTest('if a user can save card as a payment method');
        $this->updateArticleStockAndFlag(15, 1);
        $this->initializeTest();

        $this->submitCreditCardPaymentAndSavePayment('mastercard_payment', false, true);
        $this->checkCreditCardPayment();
    }

    /**
     * @group unzer_module
     * @group SecondGroup
     * @group SavePaymentCCPPCest
     * @depends testPaymentUsingSavedCardWorks
     * @throws \Exception
     */
    public function testCannotSaveCardTwice(AcceptanceTester $I)
    {
        $I->wantToTest('user can not save a card twice');
        $this->updateArticleStockAndFlag(15, 1);
        $this->initializeTest();

        $this->submitCreditCardPaymentAndSavePayment('mastercard_payment', true, true);
        $I->openShop()->openAccountPage();
        $I->click("//*[@id='account_menu']/ul/li[1]/a");
        $I->see("545301******9543");

        $deleteButtons = $I->grabMultiple($this->savePaymentDeleteCreditCardButtonSelector);
        $I->assertCount(1, $deleteButtons, 'CC Saving OK');
    }

    /**
     * @group unzer_module
     * @group SecondGroup
     * @group SavePaymentCCPPCest
     * @depends testCannotSaveCardTwice
     */
    public function testRemoveSavedCardFromAccount(AcceptanceTester $I): void
    {
        $I->wantToTest("if saved card is visible in the user's account and can be deleted");
        $I->wait(5);
        $homePage = $this->I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $I->openShop()->openAccountPage();
        $I->click($this->savedPaymentsLocator);
        $I->see("545301******9543");

        $I->wait(5);
        $I->seeElement("//*[@id='uzr_collect']/button");
        $I->wait(5);
        $I->submitForm("#uzr_collect", [], 'deletePayment');
        $I->waitForPageLoad();
        $I->dontSee("545301******9543");
    }

    /**
     * @group unzer_module
     * @group SecondGroup
     * @throws \Exception
     * @group SavePaymentCCPPCest
     * @depends testRemoveSavedCardFromAccount
     */
    public function testSavedPaypalPaymentIsNotVisibleInCheckoutAfterDelete(AcceptanceTester $I): void
    {
        $I->wantToTest("if saved paypal payment is removed from the user's account");

        $homePage = $this->I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($this->I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);
        $I->openShop()->openMiniBasket()->openCheckout();
        $this->choosePayment($this->paypalPaymentLabel);
        $I->dontSee("paypal-buyer@unzer.com");
    }

    protected function getOXID(): array
    {
        return ['oscunzer_paypal'];
    }

    /**
     * @throws \Exception
     */
    private function submitCreditCardPaymentAndSavePayment(string $string, bool $newCard, bool $saveCard): void
    {
        $orderPage = $this->choosePayment($this->cardPaymentLabel);

        $this->I->waitForPageLoad();
        $this->I->wantTo("add and save a new card");

        if ($newCard) {
            $this->I->waitForElementClickable($this->newCard, 30);
            $this->I->click($this->newCard);
        }

        if ($saveCard) {
            $this->I->waitForElementClickable($this->saveCard, 30);
            $this->I->click($this->saveCard);
        }

        $this->finishCardSubmit($string);

        $orderPage->submitOrder();
    }

    private function useSavedCardToPay()
    {
        $orderPage = $this->choosePayment($this->cardPaymentLabel);

        $this->I->waitForPageLoad();
        $this->I->wait(30);
        $this->I->click($this->useSavedCardForPayment);

        $orderPage->submitOrder();
    }
}
