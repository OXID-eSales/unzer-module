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
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

use OxidEsales\Codeception\Module\Translation\Translator as BaseTranslator;

abstract class BaseCest
{
    private int $amount = 1;
    private Translator $translator;
    private AcceptanceTester $I;
    private Page $paymentSelection;

    public function _before(AcceptanceTester $I): void
    {
        $I->updateInDatabase(
            'oxpayments',
            ['OXACTIVE' => 1],
            empty($this->_getOXID()) ? [] : ['OXID' => $this->_getOXID()]
        );

        $this->I = $I;
    }

    public function _after(AcceptanceTester $I): void
    {
        $I->clearShopCache();
        $I->cleanUp();
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
        $this->I->waitForPageLoad();
        $this->I->waitForText(BaseTranslator::translate('THANK_YOU'));
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
        return ContainerFactory::getInstance()->getContainer()->get(Translator::class)->formatCurrency(
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

    abstract protected function _getOXID(): string;
}
