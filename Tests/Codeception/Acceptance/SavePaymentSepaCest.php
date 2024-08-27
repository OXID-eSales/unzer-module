<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\modules\osc\unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance\AbstractSepaCest;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance\Helper\StandardCestHelper;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance\Traits\BasketModalSettingTrait;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

/**
 * @group unzer_module
 * @group SecondGroup
 * @group SavePayment
 * @group SavePaymentSepaCest
 */
final class SavePaymentSepaCest extends AbstractSepaCest
{
    use BasketModalSettingTrait;

    private string $savePaymentCheckboxSelector = "#oscunzersavepayment";
    private string $accountLinkSelector = "//*[@id='account_menu']/ul/li[1]/a";
    private string $savePaymentDeleteButtonSelector = "button.btn-danger.delete-sepa";

    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test SEPA Direct Debit payment works and can save payment');

        // make 2 saved payments
        $this->makePayment();
        $this->makePayment(true);

        // assert its shown only one time
        $this->assertSavedPaymentIsInAccount();
    }

    private function makePayment($recurringPayment = false)
    {
        $standardCestHelper = new StandardCestHelper();
        $homePage = $standardCestHelper->openShop($this->I);

        if (!$recurringPayment) {
            $standardCestHelper->login($homePage);
        }

        $standardCestHelper->addProductToBasket($this->I);

        $paymentCheckoutPage = $standardCestHelper->openCheckout($homePage);

        $orderPage = $standardCestHelper->choosePayment($this->sepaPaymentLabel, $paymentCheckoutPage, $this->I);

        if ($recurringPayment) {
            $this->I->waitForElementClickable('#newccard');
            $this->I->click('#newccard');
        }

        $this->I->waitForElementClickable($this->savePaymentCheckboxSelector);
        $this->I->click($this->savePaymentCheckboxSelector);

        $payment = $standardCestHelper->getSepaPaymentFixtures();
        $this->I->fillField($this->IBANInput, $payment['IBAN']);

        if (!$recurringPayment) {
            $this->I->click("#oscunzersepaagreement");
        } else {
            $this->I->click("(//input[@name='oscunzersepaagreement'])[2]");
        }
        $this->I->wait(1);

        $orderPage->submitOrder();

        $standardCestHelper->checkSuccessfulPayment($this->I);
    }

    private function assertSavedPaymentIsInAccount()
    {
        $this->I->openShop()->openAccountPage();
        $this->I->click($this->accountLinkSelector);
        $this->I->waitForElementClickable($this->translatedSavedPaymentsLinkSelector());
        $this->I->click($this->translatedSavedPaymentsLinkSelector());

        $fixtures = Fixtures::get('sepa_payment');
        $iban = $fixtures['IBAN'];

        $deleteButtons = $this->I->grabMultiple($this->savePaymentDeleteButtonSelector);
        $this->I->assertCount(1, $deleteButtons);
        $savedPaymentElement = $this->I->grabMultiple(
            $this->getIbanAccountTableElementSelector($iban)
        );
        $this->I->assertCount(1, $savedPaymentElement);
    }

    private function getIbanAccountTableElementSelector(string $iban)
    {
        return "//td[contains(text(), '{$iban}')]";
    }

    private function translatedSavedPaymentsLinkSelector(): string
    {
        return "//a[contains(text(), '"
            . Translator::translate('OSCUNZER_SAVED_PAYMENTS')
            . "')]";
    }
}
