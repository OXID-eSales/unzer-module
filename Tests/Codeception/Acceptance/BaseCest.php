<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Codeception\Page\Page;
use OxidEsales\Codeception\Step\Basket as BasketSteps;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;
use OxidEsales\Codeception\Module\Translation\Translator;

abstract class BaseCest
{
    private int $amount = 1;
    private AcceptanceTester $I;
    private Page $paymentSelection;

    public function _before(AcceptanceTester $I): void
    {
        foreach ($this->_getOXID() as $payment) {
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

    /**
     * @return void
     */
    protected function _initializeTest()
    {
        $this->I->openShop();

        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($this->I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);

        $homePage = $this->I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $this->paymentSelection = $homePage->openMiniBasket()->openCheckout();
    }

    /**
     * @return void
     */
    protected function _initializeSecuredTest()
    {
        $this->I->openShop();

        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($this->I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);

        $homePage = $this->I->openShop();
        $clientData = Fixtures::get('secured_client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $this->paymentSelection = $homePage->openMiniBasket()->openCheckout();
    }

    /**
     * @param string $label
     * @return Page
     */
    protected function _choosePayment(string $label): Page
    {
        $this->I->waitForElement($label);
        $this->I->click($label);

        return $this->paymentSelection->goToNextStep();
    }

    /**
     * @return void
     */
    protected function _checkSuccessfulPayment()
    {
        $this->I->waitForDocumentReadyState();
        $this->I->waitForPageLoad();
        $this->I->waitForText(Translator::translate('THANK_YOU'));
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     */
    protected function _setAcceptance(AcceptanceTester $I)
    {
        $this->I = $I;
    }

    /**
     * @return AcceptanceTester
     */
    protected function _getAcceptance(): AcceptanceTester
    {
        return $this->I;
    }

    /**
     * @return string price of order
     */
    protected function _getPrice(): string
    {
        $basketItem = Fixtures::get('product');
        return Registry::getLang()->formatCurrency(
            $basketItem['bruttoprice_single'] * $this->amount + $basketItem['shipping_cost']
        );
    }

    /**
     * @return string currency
     */
    protected function _getCurrency(): string
    {
        $basketItem = Fixtures::get('product');
        return $basketItem['currency'];
    }

    abstract protected function _getOXID(): array;

    /**
     * If element is found return the text, if not return false
     * @param $element
     * @return bool
     */
    protected function _grabTextFromElementWhenPresent($element, $I)
    {
        try {
            $I->seeElement($element);
            $isFound = $I->grabTextFrom($element);
        } catch (\Exception $e) {
            $isFound = false;
        }
        return $isFound;
    }

    /**
     * @param $element
     * @return bool
     */
    protected function _checkElementExists($element, $I)
    {
        try {
            $isFound = $I->seeElement($element);
        } catch (\Exception $e) {
            $isFound = false;
        }
        return $isFound;
    }
}
