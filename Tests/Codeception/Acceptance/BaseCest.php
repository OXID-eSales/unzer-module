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

abstract class BaseCest
{
    private int $amount = 1;
    private int $language = 1;
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
        $this->I->waitForText($this->_getTranslator()->translate('THANK_YOU'));
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
     * @return Translator
     */
    protected function _getTranslator(): Translator
    {
        if (!isset($this->translator)) {
            if (!ContainerFactory::getInstance()->getContainer()->has(Translator::class)) {
                $this->translator = oxNew(Translator::class, Registry::getLang());
            } else {
                $this->translator = ContainerFactory::getInstance()->getContainer()->get(Translator::class);
            }

            $this->translator->setLanguage($this->language);
        }

        return $this->translator;
    }

    /**
     * @return string price of order
     */
    protected function _getPrice(): string
    {
        $basketItem = Fixtures::get('product');
        return $this->_getTranslator()->formatCurrency(
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
