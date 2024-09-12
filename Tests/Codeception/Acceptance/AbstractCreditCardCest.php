<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;

abstract class AbstractCreditCardCest extends BaseCest
{
    public string $cardPaymentLabel = "//label[@for='payment_oscunzer_card']";
    public string $cardNumberIframe = "//iframe[contains(@id, 'unzer-number-iframe')]";
    public string $cardHolderIframe = "//iframe[contains(@id, 'unzer-holder-iframe')]";
    public string $expireDateIframe = "//iframe[contains(@id, 'unzer-expiry-iframe')]";
    public string $CVCIframe = "//iframe[contains(@id, 'unzer-cvc-iframe')]";
    public string $cardNumberInput = "//input[@id='card-number']";
    public string $cardNumberHolder = "//input[@id='card-holder']";
    public string $expireDateInput = "//input[@id='card-expiry-date']";
    public string $CVCInput = "//input[@id='card-ccv']";
    public string $toCompleteAuthentication = "Click here to complete authentication.";
    public string $useSavedCardForPayment = '//*[@id="payment-saved-cards"]/table/tbody/tr/td[3]/input';

    protected function getOXID(): array
    {
        return ['oscunzer_card'];
    }

    protected function submitCreditCardPayment(string $name): void
    {
        $orderPage = $this->choosePayment($this->cardPaymentLabel);
        $this->I->waitForPageLoad();
        $this->finishCardSubmit($name);
        $orderPage->submitOrder();
    }

    protected function checkCreditCardPayment(): void
    {
        $this->I->waitForText($this->toCompleteAuthentication, 60);
        $this->I->click($this->toCompleteAuthentication);
        $this->checkSuccessfulPayment();
    }

    protected function updateArticleStockAndFlag(int $stock, int $flag): void
    {
        $article = Fixtures::get('product');
        $this->I->updateInDatabase(
            'oxarticles',
            ['OXSTOCK' => $stock, 'OXSTOCKFLAG' => $flag],
            ['OXID' => $article['id']]
        );
    }

    /**
     * @throws \Exception
     */
    protected function finishCardSubmit(string $name): void
    {
        $fixtures = Fixtures::get($name);
        $this->I->waitForElement($this->cardNumberIframe);
        $this->I->switchToIFrame($this->cardNumberIframe);
        $this->I->wait(5);
        $this->I->fillField($this->cardNumberInput, $fixtures['cardnumber']);
        $this->I->switchToNextTab(1);
        $this->I->switchToIFrame($this->cardHolderIframe);
        $this->I->fillField($this->cardNumberHolder, $fixtures['cardholder']);
        $this->I->switchToNextTab(1);
        $this->I->switchToIFrame($this->expireDateIframe);
        $this->I->wait(5);
        $this->I->fillField($this->expireDateInput, '12/' . date('y'));
        $this->I->switchToNextTab(1);
        $this->I->wait(5);
        $this->I->switchToIFrame($this->CVCIframe);
        $this->I->fillField($this->CVCInput, $fixtures['CVC']);
        $this->I->switchToFrame(null);
    }
}
