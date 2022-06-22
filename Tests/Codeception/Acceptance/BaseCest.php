<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
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
    }

    public function _after(AcceptanceTester $I): void
    {
    }

    /**
     *
     */
    public function _initializeTest()
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
     * @param string $label
     * @return Page
     */
    public function _choosePayment(string $label): Page
    {
        $this->I->waitForElement($label);
        $this->I->click($label);

        return $this->paymentSelection->goToNextStep();
    }

    /**
     * @param AcceptanceTester $I
     * @return void
     */
    public function _setAcceptance(AcceptanceTester $I)
    {
        $this->I = $I;
    }

    /**
     * @return AcceptanceTester
     */
    public function _getAcceptance(): AcceptanceTester
    {
        return $this->I;
    }

    /**
     * @return Translator
     */
    public function _getTranslator(): Translator
    {
        if (!isset($this->translator)) {
            if (!ContainerFactory::getInstance()->getContainer()->has(Translator::class)) {
                $this->translator = oxNew(Translator::class);
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
    public function _getPrice(): string
    {
        $basketItem = Fixtures::get('product');
        return $this->_getTranslator()->formatCurrency(
            $basketItem['bruttoprice_single'] * $this->amount + $basketItem['shipping_cost']
        );
    }

    /**
     * @return string currency
     */
    public function _getCurrency(): string
    {
        $basketItem = Fixtures::get('product');
        return $basketItem['currency'];
    }
}
