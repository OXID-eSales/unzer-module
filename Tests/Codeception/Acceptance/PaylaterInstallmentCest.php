<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\modules\osc\unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use Codeception\Util\Locator;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Codeception\Step\Basket as BasketSteps;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance\BaseCest;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

/**
 * @group unzer_module
 * @group SecondGroup
 */
final class PaylaterInstallmentCest extends BaseCest
{
    private string $paymentLabel = "//label[@for='payment_oscunzer_installment_paylater']";
    protected function getOXID(): array
    {
        return ['oscunzer_installment'];
    }

    /**
     * @param AcceptanceTester $I
     * @group checkPaymentUnavailableForB2B
     * @group ThirdGroup
     */
    public function checkInstallmentUnavailableForB2B(AcceptanceTester $I)
    {
        $I->wantToTest('Paylater Installment unavailable for B2B Customer');
        $I->updateInDatabase(
            'oxuser',
            ['oxcompany' => 'ACME'],
            ['oxid' => 'unzeruser']
        );
        $this->initializeTest();

        $I->cantSee($this->paymentLabel);
    }

    /**
     * @param AcceptanceTester $I
     * @group checkPaymentUnavailableForB2C
     * @group ThirdGroup
     */
    public function checkInstallmentAvailableForB2B(AcceptanceTester $I)
    {
        $I->wantToTest('Paylater Installment available for B2C Customer');
        $I->updateInDatabase(
            'oxuser',
            ['oxcompany' => ''],
            ['oxid' => 'unzeruser']
        );
        $this->initializeTest();

        $I->seeElement($this->paymentLabel);
    }
}
