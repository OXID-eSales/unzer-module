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

final class PaymentsAvailableCest extends BaseCest
{
    private $paymentMethods = [
        'OSCUNZER_PAYMENT_METHOD_SEPA',
        'OSCUNZER_PAYMENT_METHOD_SEPA-SECURED',
        'OSCUNZER_PAYMENT_METHOD_INVOICE',
        'OSCUNZER_PAYMENT_METHOD_PREPAYMENT'
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

        $translator = ContainerFactory::getInstance()->getContainer()->get(Translator::class);
        $translator->setLanguage(1);
        foreach ($this->paymentMethods as $onePaymentMethod) {
            $I->waitForText($translator->translate($onePaymentMethod));
        }
    }
}
