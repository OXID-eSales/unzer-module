<?php

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;

abstract class AbstractCreditCardCest extends BaseCest
{
    protected string $cardPaymentLabel = "//label[@for='payment_oscunzer_card']";
    protected string $cardNumberIframe = "//iframe[contains(@id, 'unzer-number-iframe')]";
    protected string $expireDateIframe = "//iframe[contains(@id, 'unzer-expiry-iframe')]";
    protected string $CVCIframe = "//iframe[contains(@id, 'unzer-cvc-iframe')]";
    protected string $cardNumberInput = "//input[@id='card-number']";
    protected string $expireDateInput = "//input[@id='card-expiry-date']";
    protected string $CVCInput = "//input[@id='card-ccv']";
    protected string $toCompleteAuthentication = "Click here to complete authentication.";
    protected string $useSavedCardForPayment = '//*[@id="payment-saved-cards"]/table/tbody/tr/td[3]/input';

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
        $this->I->switchToIFrame($this->expireDateIframe);
        $this->I->fillField($this->expireDateInput, '12/' . date('y'));
        $this->I->switchToNextTab(1);
        $this->I->switchToIFrame($this->CVCIframe);
        $this->I->fillField($this->CVCInput, $fixtures['CVC']);
        $this->I->switchToFrame(null);
    }
}
