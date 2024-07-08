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
final class CreditCardCest extends BaseCest
{
    private string $cardPaymentLabel = "//label[@for='payment_oscunzer_card']";
    private string $cardNumberIframe = "//iframe[contains(@id, 'unzer-number-iframe')]";
    private string $expireDateIframe = "//iframe[contains(@id, 'unzer-expiry-iframe')]";
    private string $CVCIframe = "//iframe[contains(@id, 'unzer-cvc-iframe')]";
    private string $cardNumberInput = "//input[@id='card-number']";
    private string $expireDateInput = "//input[@id='card-expiry-date']";
    private string $CVCInput = "//input[@id='card-ccv']";
    private string $toCompleteAuthentication = "Click here to complete authentication.";
    private string $confirmSavePayment = "#oscunzersavepayment";
    private mixed $fixtures;
    private string $useSavedCardForPayment = '//*[@id="payment-saved-cards"]/table/tbody/tr/td[3]/input';

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     * @group CreditCardPaymentTest
     */
    public function checkPaymentUsingMastercardWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Credit Card payment using Mastercard works');
        $this->updateArticleStockAndFlag(15, 1);
        $this->prepareCreditCardTest($I);

        $this->submitCreditCardPaymentAndSavePayment('mastercard_payment');
        $this->checkCreditCardPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group CreditCardPaymentTest
     * @depends checkPaymentUsingMastercardWorks
     */
    public function checkPaymentUsingMastercardWithLastStockItemWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Credit Card payment using Mastercard with last stock item works');
        $this->updateArticleStockAndFlag(1, 3);
        $this->prepareCreditCardTest($I);

        $this->useSavedCardToPay();
        $this->checkCreditCardPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group CreditCardPaymentTest
     */
    public function checkPaymentUsingVisaWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Credit Card payment using Visa works');
        $this->updateArticleStockAndFlag(15, 1);
        $this->prepareCreditCardTest($I);

        $this->submitCreditCardPayment('visa_payment', true);
        $this->checkCreditCardPayment();
    }

    /**
     * @param AcceptanceTester $I
     * @group CreditCardPaymentTest
     * @depends checkPaymentUsingMastercardWorks
     */
    public function checkPaymentUsingVisaCanSavePayment(AcceptanceTester $I)
    {
        $I->wantToTest('Test Credit Card payment using saved MC works');
        $this->updateArticleStockAndFlag(15, 1);
        $this->prepareCreditCardTest($I);

        $this->useSavedCardToPay();
        $this->checkCreditCardPayment();
    }

    /**
    * @param AcceptanceTester $I
    * @group CreditCardPaymentTest
    * @depends checkPaymentUsingVisaCanSavePayment
    */
    public function removeSavedCardFromAccount(AcceptanceTester $I)
    {
        $I->wantToTest("if saved paypal card is visible in the user's account and can be deleted");
        $I->wait(5);
        $homePage = $this->I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $I->openShop()->openAccountPage();
        $I->click("//*[@id='account_menu']/ul/li[1]/a");
        $I->see("card");

        $I->wait(5);
        $I->seeElement("//*[@id='uzr_collect']/button");
        $I->wait(5);
        $I->submitForm("#uzr_collect", [], 'deletePayment');
        $I->waitForPageLoad();
        $I->dontSee("paypal-buyer@unzer.com");
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     * @group CreditCardPaymentTest
     * @depends checkPaymentUsingVisaWorks
     * @depends checkPaymentUsingMastercardWorks
     */
    public function checkPaymentUsingVisaWithLastStockItemWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Credit Card payment using Visa with last stock item works');
        $this->updateArticleStockAndFlag(1, 3);
        $this->prepareCreditCardTest($I);

        $this->submitCreditCardPayment('visa_payment', false);

        $this->checkCreditCardPayment();
    }

    /**
     * @throws \Exception
     */
    private function submitCreditCardPaymentAndSavePayment(string $string)
    {
        $orderPage = $this->choosePayment($this->cardPaymentLabel);

        $this->I->waitForPageLoad();
        $this->I->waitForElementClickable($this->confirmSavePayment);
        $this->I->click($this->confirmSavePayment);
        $this->finishCardSubmit($string);

        $orderPage->submitOrder();
    }

    /**
     * @throws \Exception
     */
    private function finishCardSubmit(string $name): void
    {
        $fixtures = Fixtures::get($name);
        $this->I->waitForElement($this->cardNumberIframe);
        $this->I->switchToIFrame($this->cardNumberIframe);
        $this->I->wait(5);
        $this->I->fillField($this->cardNumberInput, $fixtures['cardnumber']);
        $this->I->switchToNextTab(1);
        $this->I->switchToIFrame($this->expireDateIframe);
        $this->I->fillField($this->expireDateInput, '12/' . date('y'));
        $this->I->switchToNextTab(1);
        $this->I->switchToIFrame($this->CVCIframe);
        $this->I->fillField($this->CVCInput, $fixtures['CVC']);
        $this->I->switchToFrame(null);
    }

    protected function getOXID(): array
    {
        return ['oscunzer_card'];
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     */
    private function prepareCreditCardTest(AcceptanceTester $I)
    {
        $this->initializeTest();
    }

    /**
     * @param string $name Fixtures name
     * @return void
     */
    private function submitCreditCardPayment(string $name, bool $newCard)
    {
        $orderPage = $this->choosePayment($this->cardPaymentLabel);

        $this->I->waitForPageLoad();

        if ($newCard) {
            $this->I->click('//*[@id="newccard"]');
        }

        $this->finishCardSubmit($name);

        $orderPage->submitOrder();
    }

    private function useSavedCardToPay()
    {
        $orderPage = $this->choosePayment($this->cardPaymentLabel);

        $this->I->waitForPageLoad();
        $this->I->click($this->useSavedCardForPayment);

        $orderPage->submitOrder();
    }

    /**
     * @return void
     */
    private function checkCreditCardPayment()
    {
        $this->I->waitForText($this->toCompleteAuthentication, 60);
        $this->I->click($this->toCompleteAuthentication);

        $this->checkSuccessfulPayment();
    }

    /**
     * @param $stock int is oxarticles->OXSTOCK
     * @param $flag int is oxarticles->OXSTOCKFLAG
     * @return void
     */
    private function updateArticleStockAndFlag($stock, $flag)
    {
        $article = Fixtures::get('product');
        $this->I->updateInDatabase(
            'oxarticles',
            ['OXSTOCK' => $stock, 'OXSTOCKFLAG' => $flag],
            ['OXID' => $article['id']]
        );
    }
}
