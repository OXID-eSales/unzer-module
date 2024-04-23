<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use Codeception\Util\Locator;
use OxidEsales\Codeception\Page\Home;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Codeception\Page\Page;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;
use OxidEsales\Codeception\Module\Translation\Translator;

abstract class BaseCest
{
    private int $amount = 1;
    private AcceptanceTester $I;
    private Page $paymentSelection;

    public function _before(AcceptanceTester $I): void
    {
        foreach ($this->getOXID() as $payment) {
            $I->updateInDatabase(
                'oxpayments',
                ['OXACTIVE' => 1],
                ['OXID' => $payment]
            );

            $I->haveInDatabase(
                'oxobject2payment',
                ['OXID' => 'test' . $payment,
                    'OXOBJECTID' => 'a7c40f631fc920687.20179984',
                    'OXPAYMENTID' => $payment,
                    'OXTYPE' => 'oxcountry'
                ]
            );
        }

        $product = Fixtures::get('product');
        $I->updateInDatabase(
            'oxarticles',
            ['OXSTOCK' => 10],
            ['OXID' => $product['id']]
        );

        $I->updateInDatabase(
            'oxdelivery',
            ['oxsort' => 15],
            ['oxtitle' => 'Standard']
        );

        // activate some countries and assign country to the payment.
        // Also make sure, that delivery set and delivery costs are properly assigned
        $aCountryId2paymentId = [
            'a7c40f6320aeb2ec2.72885259' => 'oscunzer_eps', // AT -> EPS
            'a7c40f632e04633c9.47194042' => 'oscunzer_bancontact', // BE -> Bancontact
            'a7c40f632cdd63c52.64272623' => 'oscunzer_ideal', // NL -> IDEAL
            '8f241f1109624d3f8.50953605' => 'oscunzer_przelewy24', // PL -> Przelewy24
        ];
        $country = [
            'a7c40f6320aeb2ec2.72885259' => 'AT', // AT -> EPS
            'a7c40f632e04633c9.47194042' => 'BE', // BE -> Bancontact
            'a7c40f632cdd63c52.64272623' => 'NL', // NL -> IDEAL
            '8f241f1109624d3f8.50953605' => 'PL', // PL -> Przelewy24
        ];
        // must use "Standard (3.90)", other options could change the price and fail the test
        $delSet = [
            '1b842e73470578914.54719298' => 'DEU', // Versandkostenregel
            'oxidstandard' => 'STD', // Versandart
        ];
        foreach ($aCountryId2paymentId as $countryId => $paymentId) {
            $tmpId = $paymentId . '.' . $country[$countryId];
            $I->updateInDatabase(
                'oxcountry',
                ['OXACTIVE' => '1'],
                ['OXID' => $countryId]
            );

            $I->haveInDatabase(
                'oxobject2payment',
                [
                    'OXID' => $tmpId,
                    'OXOBJECTID' => $countryId,
                    'OXPAYMENTID' => $paymentId,
                    'OXTYPE' => 'oxcountry'
                ]
            );

            foreach ($delSet as $delId => $delShort) {
                $tmpOxid = $tmpId . '.' . $delShort;

                $I->haveInDatabase(
                    'oxobject2delivery',
                    [
                        'OXID' => $tmpOxid . '.c',
                        'OXDELIVERYID' => $delId,
                        'OXOBJECTID' => $countryId,
                        'OXTYPE' => 'oxcountry'
                    ]
                );
                $I->haveInDatabase(
                    'oxobject2delivery',
                    [
                        'OXID' => $tmpOxid . '.d',
                        'OXDELIVERYID' => $delId,
                        'OXOBJECTID' => $countryId,
                        'OXTYPE' => 'oxdelset'
                    ]
                );
            }
        }

        $this->I = $I;
    }

    public function _after(AcceptanceTester $I): void
    {
        $I->clearShopCache();
    }

    protected function initializeTest(): void
    {
        $miniBasketMenuElement = '//button[@class="btn btn-minibasket"]';
        $this->I->openShop();

        $basketItem = Fixtures::get('product');

        $params['fnc'] = 'tobasket';
        $params['aid'] = $basketItem['id'];
        $params['am'] = $this->amount;
        $params['anid'] = $basketItem['id'];

        if ($this->I->seePageHasElement('input[name=stoken]')) {
            $params['stoken'] = $this->I->grabValueFrom('input[name=stoken]');
        }

        if ($this->I->seePageHasElement('input[name=force_sid]')) {
            $params['force_sid'] = $this->I->grabValueFrom('input[name=force_sid]');
        }

        $this->I->amOnPage('/index.php?' . http_build_query($params));
        $this->I->waitForElement($miniBasketMenuElement);
        $this->I->waitForPageLoad();

        $this->loginUser('client');

        $this->I->waitForElementClickable($miniBasketMenuElement, 15);
        $this->I->click($miniBasketMenuElement);

        $this->I->waitForText(Translator::translate('DISPLAY_BASKET'));
        $this->I->click(Translator::translate('DISPLAY_BASKET'));
        $this->I->waitForPageLoad();

        $this->I->waitForText(Translator::translate('CHECKOUT'));
        $this->I->click(Translator::translate('CHECKOUT'));
        $this->I->waitForPageLoad();

        $this->I->waitForText(Translator::translate('NEXT'), 10);
        $this->I->click(Translator::translate('NEXT'));
        $this->I->waitForPageLoad();
    }

    protected function initializeSecuredTest(): void
    {
        $miniBasketMenuElement = '//button[@class="btn btn-minibasket"]';
        $this->I->openShop();

        $basketItem = Fixtures::get('product');

        $params['fnc'] = 'tobasket';
        $params['aid'] = $basketItem['id'];
        $params['am'] = $this->amount;
        $params['anid'] = $basketItem['id'];

        if ($this->I->seePageHasElement('input[name=stoken]')) {
            $params['stoken'] = $this->I->grabValueFrom('input[name=stoken]');
        }

        if ($this->I->seePageHasElement('input[name=force_sid]')) {
            $params['force_sid'] = $this->I->grabValueFrom('input[name=force_sid]');
        }

        $this->I->amOnPage('/index.php?' . http_build_query($params));
        $this->I->waitForElement($miniBasketMenuElement);
        $this->I->waitForPageLoad();

        $this->loginUser('secured_client');

        $this->I->waitForElementClickable($miniBasketMenuElement);
        $this->I->click($miniBasketMenuElement);

        $this->I->waitForText(Translator::translate('DISPLAY_BASKET'));
        $this->I->click(Translator::translate('DISPLAY_BASKET'));
        $this->I->waitForPageLoad();

        $this->I->waitForText(Translator::translate('CHECKOUT'));
        $this->I->click(Translator::translate('CHECKOUT'));
        $this->I->waitForPageLoad();

        $this->I->waitForText(Translator::translate('NEXT'));
        $this->I->click(Translator::translate('NEXT'));
        $this->I->waitForPageLoad();
    }

    protected function loginUser(string $type): void
    {
        $accountMenuButton = "//button[contains(@aria-label,'Usercenter')]";
        $form = "//form[@class='px-3 py-2']";
        $userLoginName = '#loginEmail';
        $userLoginPassword = '#loginPasword';
        $userLoginButton = '//button[@class="btn btn-primary"]';

        $clientData = Fixtures::get($type);

        $this->I->amOnPage('/index.php');
        $this->I->waitForPageLoad();
        $this->I->waitForElementVisible($accountMenuButton);
        $this->I->waitForElementClickable($accountMenuButton);
        $this->I->scrollTo($accountMenuButton);
        $this->I->wait(10);

        try {
            $this->I->click($accountMenuButton);
        } catch (\Facebook\WebDriver\Exception\ElementClickInterceptedException $e) {
            $this->I->makeScreenshot('cannotClickAccount');
        }

        $this->I->waitForElementClickable($form, 15);

        $this->I->waitForElementVisible($userLoginName);
        $this->I->fillField($userLoginName, $clientData['username']);
        $this->I->fillField($userLoginPassword, $clientData['password']);
        $this->I->click($userLoginButton);
        $this->I->waitForPageLoad();
    }

    protected function choosePayment(string $label): void
    {
        $nextStepButton = '//button[@class="btn btn-highlight btn-lg w-100"]';

        $this->I->scrollTo($label);
        $this->I->wait(3);
        $this->I->waitForElement($label);
        $this->I->click($label);

        $this->I->click($nextStepButton);
        $this->I->waitForPageLoad();
    }

    protected function checkSuccessfulPayment(int $timeout = 5): void
    {
        $this->I->waitForDocumentReadyState();
        $this->I->waitForPageLoad();
        $this->I->waitForText(Translator::translate('THANK_YOU'), $timeout);
    }

    protected function submitOrder(): void
    {
        $submitButton = '#submitOrder';

        $this->I->waitForElement($submitButton);
        $this->I->scrollTo($submitButton);
        $this->I->wait(5);
        $this->I->click($submitButton);
        $this->I->waitForPageLoad();
    }

    /**
     * @return AcceptanceTester
     */
    protected function getAcceptance(): AcceptanceTester
    {
        return $this->I;
    }

    /**
     * @return string price of order
     */
    protected function getPrice(): string
    {
        $basketItem = Fixtures::get('product');
        return Registry::getLang()->formatCurrency(
            $basketItem['bruttoprice_single'] * $this->amount + $basketItem['shipping_cost']
        );
    }

    /**
     * @return string currency
     */
    protected function getCurrency(): string
    {
        $basketItem = Fixtures::get('product');
        return $basketItem['currency'];
    }

    abstract protected function getOXID(): array;

    protected function checkElementExists($element, $I): bool
    {
        try {
            $isFound = $I->seeElement($element);
        } catch (\Exception $e) {
            $isFound = false;
        }
        return $isFound;
    }
}
