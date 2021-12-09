<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Codeception\Step\Basket as BasketSteps;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

final class SEPADirectDebitCest extends BaseCest
{
    /**
     * @param AcceptanceTester $I
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->wantToTest('Test SEPA Direct Debit payment works');

        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($I);
        $basketSteps->addProductToBasket($basketItem['id'], 1);

        $homePage = $I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $paymentSelection = $homePage->openMiniBasket()->openCheckout();

        $sepaPaymentLabel = "//label[@for='payment_oscunzer_sepa']";
        $I->waitForElement($sepaPaymentLabel);
        $I->click($sepaPaymentLabel);

        $orderPage = $paymentSelection->goToNextStep();

        $I->fillField("//input[contains(@id, 'unzer-iban-input')]", "DE89370400440532013000");
        $I->click("#oscunzersepaagreement");
        $I->wait(1);

        $orderPage->submitOrder();

        $I->waitForText(Translator::translate('THANK_YOU'));
    }
}
