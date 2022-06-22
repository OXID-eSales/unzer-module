<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidSolutionCatalysts\Unzer\Service\Translator;
use OxidEsales\Codeception\Step\Basket as BasketSteps;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\Page\AlipayPage;

class AlipayCest extends BaseCest
{
    private $amount = 1;
    /**
     * @param AcceptanceTester $I
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Alipay payment works');

        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);

        $homePage = $I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $paymentSelection = $homePage->openMiniBasket()->openCheckout();

        $alipayPaymentLabel = "//label[@for='payment_oscunzer_alipay']";
        $I->waitForElement($alipayPaymentLabel);
        $I->click($alipayPaymentLabel);

        $orderPage = $paymentSelection->goToNextStep();

        $orderPage->submitOrder();

        $translator = ContainerFactory::getInstance()->getContainer()->get(Translator::class);
        $translator->setLanguage(1);
        $price = str_replace(
            $translator->formatCurrency($basketItem['bruttoprice_single'] * $this->amount +
                $basketItem['shipping_cost']),
            ',',
            '.'
        );

        $alipayPage = new AlipayPage($I);
        $alipayClientData = Fixtures::get('alipay_client');
        $alipayPage->login($alipayClientData['username'], $alipayClientData['password'], $price);
        $alipayPage->choosePaymentMethod(1);
        $alipayPage->paymentSuccessful($price);

        $I->waitForText($translator->translate('THANK_YOU'));
    }

    /**
     * @param AcceptanceTester $I
     */
    public function checkSomeLPMPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Alipay Some LPM payment works');

        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);

        $homePage = $I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $paymentSelection = $homePage->openMiniBasket()->openCheckout();

        $alipayPaymentLabel = "//label[@for='payment_oscunzer_alipay']";
        $I->waitForElement($alipayPaymentLabel);
        $I->click($alipayPaymentLabel);

        $orderPage = $paymentSelection->goToNextStep();

        $orderPage->submitOrder();

        $translator = ContainerFactory::getInstance()->getContainer()->get(Translator::class);
        $translator->setLanguage(1);
        $price = str_replace(
            $translator->formatCurrency($basketItem['bruttoprice_single'] * $this->amount +
                $basketItem['shipping_cost']),
            ',',
            '.'
        );

        $alipayPage = new AlipayPage($I);
        $alipayClientData = Fixtures::get('alipay_client');
        $alipayPage->login($alipayClientData['username'], $alipayClientData['password'], $price);
        $alipayPage->choosePaymentMethod(2);
        $alipayPage->paymentSuccessful($price);

        $I->waitForText($translator->translate('THANK_YOU'));
    }

    /**
     * @param AcceptanceTester $I
     */
    public function checkAnotherLPMPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Another LPM Alipay payment works');

        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);

        $homePage = $I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $paymentSelection = $homePage->openMiniBasket()->openCheckout();

        $alipayPaymentLabel = "//label[@for='payment_oscunzer_alipay']";
        $I->waitForElement($alipayPaymentLabel);
        $I->click($alipayPaymentLabel);

        $orderPage = $paymentSelection->goToNextStep();

        $orderPage->submitOrder();

        $translator = ContainerFactory::getInstance()->getContainer()->get(Translator::class);
        $translator->setLanguage(1);
        $price = str_replace(
            $translator->formatCurrency($basketItem['bruttoprice_single'] * $this->amount +
                $basketItem['shipping_cost']),
            ',',
            '.'
        );

        $alipayPage = new AlipayPage($I);
        $alipayClientData = Fixtures::get('alipay_client');
        $alipayPage->login($alipayClientData['username'], $alipayClientData['password'], $price);
        $alipayPage->choosePaymentMethod(3);
        $alipayPage->paymentSuccessful($price);

        $I->waitForText($translator->translate('THANK_YOU'));
    }

    /**
     * @param AcceptanceTester $I
     */
    public function checkOneMoreLPMPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test One more LPM Alipay payment works');

        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($I);
        $basketSteps->addProductToBasket($basketItem['id'], $this->amount);

        $homePage = $I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $paymentSelection = $homePage->openMiniBasket()->openCheckout();

        $alipayPaymentLabel = "//label[@for='payment_oscunzer_alipay']";
        $I->waitForElement($alipayPaymentLabel);
        $I->click($alipayPaymentLabel);

        $orderPage = $paymentSelection->goToNextStep();

        $orderPage->submitOrder();

        $translator = ContainerFactory::getInstance()->getContainer()->get(Translator::class);
        $translator->setLanguage(1);
        $price = str_replace(
            $translator->formatCurrency($basketItem['bruttoprice_single'] * $this->amount +
                $basketItem['shipping_cost']),
            ',',
            '.'
        );

        $alipayPage = new AlipayPage($I);
        $alipayClientData = Fixtures::get('alipay_client');
        $alipayPage->login($alipayClientData['username'], $alipayClientData['password'], $price);
        $alipayPage->choosePaymentMethod(4);
        $alipayPage->paymentSuccessful($price);

        $I->waitForText($translator->translate('THANK_YOU'));
    }
}
