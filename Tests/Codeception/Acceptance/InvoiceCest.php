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

class InvoiceCest extends BaseCest
{
    /**
     * @param AcceptanceTester $I
     * @group testing
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test Invoice payment works');
        $amount = 1;

        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($I);
        $basketSteps->addProductToBasket($basketItem['id'], $amount);

        $homePage = $I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $paymentSelection = $homePage->openMiniBasket()->openCheckout();

        $invoicePaymentLabel = "//label[@for='payment_oscunzer_invoice']";
        $I->waitForElement($invoicePaymentLabel);
        $I->click($invoicePaymentLabel);

        $orderPage = $paymentSelection->goToNextStep();

        $orderPage->submitOrder();

        $translator = ContainerFactory::getInstance()->getContainer()->get(Translator::class);
        $translator->setLanguage(1);

        $I->waitForText($translator->translate('THANK_YOU'));

        $I->waitForText(rtrim(strip_tags(sprintf(
            $translator->translate('OSCUNZER_BANK_DETAILS_AMOUNT'),
            $translator->formatCurrency($basketItem['bruttoprice_single'] * $amount + $basketItem['shipping_cost']),
            $basketItem['currency']
        ))));
    }
}
