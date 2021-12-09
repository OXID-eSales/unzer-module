<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Step\Basket as BasketSteps;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

final class PaymentsAvailableCest extends BaseCest
{
    private $paymentMethods = [
        "SEPA Direct Debit",
        "SEPA Direct Debit Secured",
    ];

    /**
     * @param AcceptanceTester $I
     */
    public function checkPaymentsAvailable(AcceptanceTester $I)
    {
        $I->wantToTest('Test payment methods are available');

        $basketItem = Fixtures::get('product');
        $basketSteps = new BasketSteps($I);
        $basketSteps->addProductToBasket($basketItem['id'], 1);

        $homePage = $I->openShop();
        $clientData = Fixtures::get('client');
        $homePage->loginUser($clientData['username'], $clientData['password']);

        $homePage->openMiniBasket()->openCheckout();

        foreach ($this->paymentMethods as $onePaymentMethod) {
            $I->waitForText($onePaymentMethod);
        }
    }
}
