<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use Facebook\WebDriver\Exception\ElementClickInterceptedException;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

/**
 * @group unzer_module
 * @group SecondGroup
 * @group SavePaymentsCards
 */
final class SavePaymentCreditCardCest extends BaseCest
{
    private string $cardPaymentLabel = "//label[@for='payment_oscunzer_card']";
    private string $cardNumberIframe = "//iframe[contains(@id, 'unzer-number-iframe')]";
    private string $cardHolderIframeInput = "//iframe[contains(@id, 'unzer-holder-iframe')]";
    private string $expireDateIframe = "//iframe[contains(@id, 'unzer-expiry-iframe')]";
    private string $CVCIframe = "//iframe[contains(@id, 'unzer-cvc-iframe')]";
    private string $cardNumberInput = "//input[@id='card-number']";
    private string $cardHolderInput = "//input[@id='card-holder']";
    private string $expireDateInput = "//input[@id='card-expiry-date']";
    private string $CVCInput = "//input[@id='card-ccv']";
    private string $toCompleteAuthentication = "Click here to complete authentication.";
    private string $newCard = "#newccard";
    private string $saveCard = "#oscunzersavepayment";
    private string $savedPaymentsLocator = "#savedPayments";

    /**
     * @group unzer_module
     * @group SavePaymentsCards
     * @group testPaymentCardCanSavePayment
     * @throws \Exception
     */
    public function testPaymentCardCanSavePayment(AcceptanceTester $I): void
    {
        $I->wantToTest('if a user can save card as a payment method');
        $this->updateArticleStockAndFlag();
        $this->initializeTest();
        $I->wait(5);
        $this->submitCreditCardPaymentAndSavePayment($I, 'mastercard_payment', false, true);
        $this->checkCreditCardPayment();
    }

    /**
     * @group unzer_module
     * @group SavePaymentsCards
     * @depends testPaymentCardCanSavePayment
     * @throws \Exception
     */
    public function testPaymentUsingSavedCardWorks(AcceptanceTester $I): void
    {
        $I->wantToTest('if user can pay with a saved card');
        $this->updateArticleStockAndFlag();
        $this->initializeTest();

        $this->useSavedCardToPay();
        $this->checkCreditCardPayment();
    }

    /**
     * @group unzer_module
     * @group SavePaymentsCards
     * @depends testPaymentUsingSavedCardWorks
     * @throws \Exception
     */
    public function testCannotSaveCardTwice(AcceptanceTester $I)
    {
        $this->I->wantToTest('user can not save a card twice');
        $this->initializeTest();
        $this->submitCreditCardPaymentAndSavePayment($I, 'mastercard_payment', true, true);
        $this->I->amOnPage('/index.php?cl=account');
        $this->I->click($this->savedPaymentsLocator);
        $this->I->see("545301******9543");
        $pageSource = $I->grabPageSource();
        $occurrences = substr_count($pageSource, '545301******9543');
        $this->I->assertEquals(1, $occurrences, 'CC Saving OK');
    }

    /**
     * @group unzer_module
     * @group SecondGroup
     * @depends testCannotSaveCardTwice
     */
    public function testRemoveSavedCardFromAccount(AcceptanceTester $I): void
    {
        $I->wantToTest("if saved card is visible in the user's account and can be deleted");
        $I->wait(5);
        $this->initializeTest();
        $this->I->amOnPage('/index.php?cl=account');
        $this->I->click($this->savedPaymentsLocator);
        $this->I->see("545301******9543");

        $this->I->wait(5);
        $this->I->seeElement("//*[@id='uzr_collect']/button");
        $this->I->wait(5);
        $this->I->submitForm("#uzr_collect", [], 'deletePayment');
        $I->waitForPageLoad();
        $I->dontSee("545301******9543");
    }

    protected function getOXID(): array
    {
        return ['oscunzer_card'];
    }

    private function submitCreditCardPaymentAndSavePayment(
        AcceptanceTester $I,
        string $cardType,
        bool $newCard,
        bool $saveCard
    ): void {
        $this->choosePayment($this->cardPaymentLabel);

        $I->waitForPageLoad();
        $I->wantTo("add and save a new card");

        if ($newCard) {
            $I->scrollTo($this->newCard);
            $I->wait(2);
            $I->click($this->newCard);
        }

        if ($saveCard) {
            $I->scrollTo($this->saveCard);
            $I->wait(2);
            $I->click($this->saveCard);
        }

        $this->finishCardSubmit($cardType);

        $this->submitOrder();
    }

    private function useSavedCardToPay(): void
    {
        $this->choosePayment($this->cardPaymentLabel);

        $this->I->waitForPageLoad();
        $this->I->wait(10);

        $this->I->wantTo('use saved payment to pay');
        $this->I->scrollTo("#payment-saved-cards input.paymenttypeid[type='radio']");
        $this->I->wait(5);
        $this->I->click("#payment-saved-cards input.paymenttypeid[type='radio']");

        $this->submitOrder();
    }

    private function finishCardSubmit(string $cardType): void
    {
        $fixtures = Fixtures::get($cardType);
        $this->I->waitForElement($this->cardNumberIframe);
        $this->I->switchToIFrame($this->cardNumberIframe);
        $this->I->wait(5);
        $this->I->fillField($this->cardNumberInput, $fixtures['cardnumber']);
        $this->I->switchToNextTab(1);
        $this->I->switchToIFrame($this->cardHolderIframeInput);
        $this->I->fillField($this->cardHolderInput, $fixtures['cardholder']);
        $this->I->switchToNextTab(1);
        $this->I->switchToIFrame($this->expireDateIframe);
        $this->I->fillField($this->expireDateInput, '12/' . date('y'));
        $this->I->switchToNextTab(1);
        $this->I->switchToIFrame($this->CVCIframe);
        $this->I->fillField($this->CVCInput, $fixtures['CVC']);
        $this->I->switchToFrame(null);
    }

    private function checkCreditCardPayment(): void
    {
        $this->I->waitForText($this->toCompleteAuthentication, 60);
        $this->I->click($this->toCompleteAuthentication);

        $this->checkSuccessfulPayment();
    }

    private function updateArticleStockAndFlag(): void
    {
        $article = Fixtures::get('product');
        $this->I->updateInDatabase(
            'oxarticles',
            ['OXSTOCK' => 15, 'OXSTOCKFLAG' => 1],
            ['OXID' => $article['id']]
        );
    }
}
