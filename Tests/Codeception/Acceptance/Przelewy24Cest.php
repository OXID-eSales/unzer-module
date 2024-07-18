<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

/**
 * @group unzer_module
 * @grpup ThirdGroup
 */
final class Przelewy24Cest extends BaseCest
{
    private string $przelewy24PaymentLabel = "//label[@for='payment_oscunzer_przelewy24']";
    private string $bankLink = "//div[@data-for='MBANK_-_MTRANSFER-0-0-tip']";
    private string $submitButton = "#user_account_pbl_correct";

    protected function getOXID(): array
    {
        return ['oscunzer_przelewy24'];
    }

    public function _before(AcceptanceTester $I): void
    {
        parent::_before($I);
        $oConfig = Registry::getConfig();
        $oConfig->saveSystemConfigParameter(
            'arr',
            'aCurrencies',
            [0 => 'PLN@ 4.66@ ,@ @ zł@ 2']
        );
        $oConfig->setActShopCurrency(0);
        // Przelewy24 is now only available in PL, BaseCest should make all the necessary setup
        // User is assigned to PL
        $user = Fixtures::get('client');
        $I->updateInDatabase(
            'oxuser',
            ['oxcountryid' => '8f241f1109624d3f8.50953605'], // PL
            ['oxusername' => $user['username']]
        );
    }

    public function _after(AcceptanceTester $I): void
    {
        $oConfig = Registry::getConfig();
        $oConfig->saveSystemConfigParameter(
            'arr',
            'aCurrencies',
            [
                0 => 'EUR@ 1.00@ ,@ .@ €@ 2',
                1 => 'GBP@ 0.8565@ .@  @ £@ 2',
                2 => 'CHF@ 1.4326@ ,@ .@ <small>CHF</small>@ 2',
                3 => 'USD@ 1.2994@ .@  @ $@ 2',
            ]
        );
        $oConfig->setActShopCurrency(0);

        $user = Fixtures::get('client');
        $I->updateInDatabase(
            'oxuser',
            ['oxcountryid' => 'a7c40f631fc920687.20179984'], // DE
            ['oxusername' => $user['username']]
        );
        parent::_after($I);
    }

    /**
     * @param AcceptanceTester $I
     * @group Przelewy24PaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Przelewy24 payment works');

        $this->initializeTest();

        $orderPage = $this->choosePayment($this->przelewy24PaymentLabel);
        $orderPage->submitOrder();

        // first page : choose bank
        $I->waitForDocumentReadyState();
        $I->waitForElement($this->bankLink);
        $I->wait(5);
        $I->click($this->bankLink);

        // second page : payment
        $I->waitForDocumentReadyState();
        $I->waitForElement($this->submitButton);
        $I->wait(5);
        $I->click($this->submitButton);

        // third page : expect end
        $I->waitForDocumentReadyState();
        $I->waitForJS("return !!window.jQuery", 60);

        $this->checkSuccessfulPayment();
    }
}
