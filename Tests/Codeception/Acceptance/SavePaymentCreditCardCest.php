<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\modules\osc\unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Codeception\Page\Page;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance\AbstractCreditCardCest;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance\Helper\CreditCardCestHelper;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance\Traits\BasketModalSettingTrait;

/**
 * @group unzer_module
 * @group SecondGroup
 * @group SavePayment
 * @group SavePaymentCreditCardCest
 */
final class SavePaymentCreditCardCest extends AbstractCreditCardCest
{
    use BasketModalSettingTrait;

    private string $savePaymentCheckboxSelector = "#oscunzersavepayment";
    private string $accountLinkSelector = "//*[@id='account_menu']/ul/li[1]/a";
    private string $savePaymentDeleteButtonSelector = "button.btn-danger.delete-cc";

    public function testCanPayWithSavingPayment()
    {
        $this->I->wantToTest('I can pay with credit card and save the credit card for further payments');

        // make 2 saved payments
        $this->makePayment();
        $this->makePayment(true);

        // assert its shown only one time
        $this->assertSavedPaymentIsInAccount();
    }

    /**
     *
     * @depends testCanPayWithSavingPayment
     */
    public function testCanPayWithSavedPayment()
    {
        $this->I->wantToTest('I can pay with credit card which i have saved before');
        $orderPage = $this->checkoutUntilConfirmation();
        $this->I->waitForPageLoad();
        $this->I->click($this->useSavedCardForPayment);
        $orderPage->submitOrder();

        $standardCestHelper = new CreditCardCestHelper();
        $standardCestHelper->checkCreditCardPayment($this->I);
    }

    protected function getOXID(): array
    {
        return ['oscunzer_card'];
    }

    private function makePayment($recurringPayment = false): void
    {
        $orderPage = $this->checkoutUntilConfirmation(!$recurringPayment);

        if ($recurringPayment) {
            $this->I->waitForElementClickable('#newccard');
            $this->I->click('#newccard');
        }

        $this->I->waitForElementClickable($this->savePaymentCheckboxSelector);
        $this->I->click($this->savePaymentCheckboxSelector);
        $this->finishCardSubmit('visa_payment');

        $orderPage->submitOrder();

        $standardCestHelper = new CreditCardCestHelper();
        $standardCestHelper->checkCreditCardPayment($this->I);
    }

    private function checkoutUntilConfirmation($withLogin = true): Page
    {
        $standardCestHelper = new CreditCardCestHelper();
        $homePage = $standardCestHelper->openShop($this->I);

        if ($withLogin) {
            $standardCestHelper->login($homePage);
        }

        $standardCestHelper->addProductToBasket($this->I);

        $paymentCheckoutPage = $standardCestHelper->openCheckout($homePage);

        return $standardCestHelper->choosePayment($this->cardPaymentLabel, $paymentCheckoutPage, $this->I);
    }

    private function assertSavedPaymentIsInAccount()
    {
        $this->I->openShop()->openAccountPage();
        $this->I->click($this->accountLinkSelector);
        $this->I->waitForElementClickable($this->translatedSavedPaymentsLinkSelector());
        $this->I->click($this->translatedSavedPaymentsLinkSelector());

        $fixtures = Fixtures::get('visa_payment');
        $cardNumber = $fixtures['cardnumber'];

        $deleteButtons = $this->I->grabMultiple($this->savePaymentDeleteButtonSelector);
        $this->I->assertCount(1, $deleteButtons);
        $savedCardElement = $this->I->grabMultiple(
            $this->getCreditCardAccountTableElementSelector($cardNumber)
        );
        $this->I->assertCount(1, $savedCardElement);
    }

    private function getCreditCardAccountTableElementSelector(string $cardNumber)
    {
        $firstFiveNumbers = substr($cardNumber, 0, 6);

        return "//th[contains(text(), '{$firstFiveNumbers}')]";
    }

    private function translatedSavedPaymentsLinkSelector(): string
    {
        return "//a[contains(text(), '"
            . Translator::translate('OSCUNZER_SAVED_PAYMENTS')
            . "')]";
    }
}
