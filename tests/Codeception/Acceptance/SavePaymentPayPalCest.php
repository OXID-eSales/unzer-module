<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;
use OxidEsales\Codeception\Step\Basket as BasketSteps;

/**
 * @group unzer_module
 * @group SecondGroup
 * @group SavePaymentsPayPal
 */
final class SavePaymentPayPalCest extends BaseCest
{
    private string $paypalPaymentLabel = "//label[@for='payment_oscunzer_paypal']";
    private string $loginInput = "#email";
    private string $passwordInput = "#password";
    private string $loginButton = "#btnLogin";
    private string $submitButton = "#payment-submit-btn";
    private string $globalSpinnerDiv = "//div[@data-testid='global-spinner']";
    private string $savePaypalPayment = "#oscunzersavepayment";
    private string $firstSavedPaypalPayment = "//*[@id='payment-saved-cards']/table/tbody/tr/td[2]/input";
    private string $savedPaymentsLocator = "#savedPayments";
    private string $acceptAllCookiesButton = "//button[@id='acceptAllButton']";
    private string $newPaypal = "#oscunzersavepayment";

    /**
     * @group unzer_module
     * @group SecondGroup
     * @group SavePaymentsPayPal
     * @throws \Exception
     */
    public function testPaymentWorksWithSavingPayment(AcceptanceTester $I)
    {
        $I->wantToTest('if PayPal payment works and save payment flag is clickable');
        $this->initializeTest();
        $this->choosePayment($this->paypalPaymentLabel);
        $I->scrollTo($this->savePaypalPayment);
        $I->waitForElementClickable($this->savePaypalPayment, 15);
        $I->wait(10);
        $I->click($this->savePaypalPayment);
        $this->submitOrder();

        $paypalPaymentData = Fixtures::get('paypal_payment');

        $this->handlePayPalLogin($I, $paypalPaymentData);
        $this->completePayPalPayment($I);

        $this->checkSuccessfulPayment();
    }

    /**
     * @group unzer_module
     * @group SecondGroup
     * @group SavePaymentsPayPal
     * @depends testPaymentWorksWithSavingPayment
     */
    public function testSavedPaypalPaymentIsVisibleInAccount(AcceptanceTester $I): void
    {
        $I->wantToTest("if saved paypal payment is visible in the user's account");

        $this->loginUser('client');

        $I->openShop()->openAccountPage();
        $I->click("//*[@id='wrapper']/div/div/div[2]/div[1]/div/div/a");
        $I->see("paypal-buyer@unzer.com");

        $this->placeOrderWithSavedPayPal($I);

        $this->checkSuccessfulPayment();
    }

    /**
     * @group unzer_module
     * @group SecondGroup
     * @group SavePaymentsPayPal
     * @depends testSavedPaypalPaymentIsVisibleInAccount
     */
    public function testCannotAddSamePPSecondTime(AcceptanceTester $I): void
    {
        $this->I->wantToTest("if user cannot save Paypal Account twice");
        $this->initializeTest();
        $this->placeOrderWithNewPayPal($I);
        $this->checkSuccessfulPayment();
        $this->checkPayPalSavedOnlyOnce($I);
    }

    /**
     * @group unzer_module
     * @group SecondGroup
     * @group SavePaymentsPayPal
     * @depends testCannotAddSamePPSecondTime
     */
    public function testSavedPaymentIsAvailableInAccountAndCanBeDeleted(AcceptanceTester $I)
    {
        $I->wantToTest("if saved paypal payment is visible in the user's account and can be deleted");
        $I->wait(5);
        $this->loginUser('client');

        $this->I->amOnPage('/index.php?cl=account');
        $this->I->click($this->savedPaymentsLocator);
        $this->I->see("paypal-buyer@unzer.com");

        $this->deletePayPalPayment($I);
    }

    /**
     * @group unzer_module
     * @group SecondGroup
     * @group SavePaymentsPayPal
     * @depends testSavedPaymentIsAvailableInAccountAndCanBeDeleted
     */
    public function testSavedPaypalPaymentIsNotVisibleInCheckoutAfterDelete(AcceptanceTester $I): void
    {
        $I->wantToTest("if saved paypal payment is removed from the user's account");
        $this->loginUser('client');

        $this->I->amOnPage('/index.php?cl=account');
        $this->I->click($this->savedPaymentsLocator);
        $this->I->dontSee("paypal-buyer@unzer.com");
    }

    protected function getOXID(): array
    {
        return ['oscunzer_paypal'];
    }

    private function handlePayPalLogin(AcceptanceTester $I, array $paypalPaymentData): void
    {
        $I->waitForDocumentReadyState();
        $I->wait(5);
        if ($this->checkElementExists($this->acceptAllCookiesButton, $I)) {
            $I->click($this->acceptAllCookiesButton);
        }

        $I->waitForDocumentReadyState();
        $I->waitForElement($this->loginInput);
        $I->fillField($this->loginInput, $paypalPaymentData['username']);
        $I->fillField($this->passwordInput, $paypalPaymentData['password']);
        $I->waitForDocumentReadyState();
        $I->click($this->loginButton);
        $I->waitForElementNotVisible($this->globalSpinnerDiv, 60);
    }

    private function completePayPalPayment(AcceptanceTester $I): void
    {
        $I->waitForDocumentReadyState();
        $I->waitForText($this->getPrice());
        $I->waitForElement($this->submitButton);
        $I->executeJS("document.getElementById('payment-submit-btn').click();");
        $I->waitForDocumentReadyState();
        $I->waitForElementNotVisible($this->globalSpinnerDiv, 60);
        $I->wait(10);
    }

    private function placeOrderWithSavedPayPal(AcceptanceTester $I): void
    {
        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);
        $I->openShop()->openMiniBasket();
        $I->waitForText(Translator::translate('CHECKOUT'));
        $I->click(Translator::translate('CHECKOUT'));
        $I->waitForPageLoad();
        $this->choosePayment($this->paypalPaymentLabel);
        $I->waitForElementClickable($this->firstSavedPaypalPayment);
        $I->wantTo('use saved payment to pay');
        $I->scrollTo('//*[@id="payment-saved-cards"]/table/tbody/tr/td[2]/input');
        $I->wait(5);
        $I->click('#payment-saved-cards > table > tbody > tr > td:nth-child(3) > input');
        $this->submitOrder();

        $paypalPaymentData = Fixtures::get('paypal_payment');
        $this->handlePayPalLogin($I, $paypalPaymentData);
        $this->completePayPalPayment($I);
    }

    private function placeOrderWithNewPayPal(AcceptanceTester $I): void
    {
        $this->choosePayment($this->paypalPaymentLabel);
        $I->waitForElementClickable($this->firstSavedPaypalPayment);
        $I->wantTo('use new Paypal account');
        $this->I->scrollTo("#oscunzersavepayment_unzer");
        $this->I->wait(1);
        $this->I->click($this->newPaypal);
        $this->submitOrder();

        $paypalPaymentData = Fixtures::get('paypal_payment');
        $this->handlePayPalLogin($I, $paypalPaymentData);
        $this->completePayPalPayment($I);
    }

    private function checkPayPalSavedOnlyOnce(AcceptanceTester $I): void
    {
        $I->amOnPage('/index.php?cl=account');
        $I->click($this->savedPaymentsLocator);
        $I->see("paypal-buyer@unzer.com");
        $pageSource = $I->grabPageSource();
        $occurrences = substr_count($pageSource, 'paypal-buyer@unzer.com');
        $I->assertEquals(1, $occurrences, 'Paypal Saving OK');
    }

    private function deletePayPalPayment(AcceptanceTester $I): void
    {
        $I->wait(5);
        $I->seeElement("//*[@id='uzr_collect']/button");
        $I->wait(5);
        $I->submitForm("#uzr_collect", [], 'deletePayment');
        $I->waitForPageLoad();
        $I->dontSee("paypal-buyer@unzer.com");
    }
}
