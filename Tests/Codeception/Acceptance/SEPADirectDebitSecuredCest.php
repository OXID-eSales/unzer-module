<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

/**
 * @group unzer_module
 * @group SecondGroup
 */
final class SEPADirectDebitSecuredCest extends BaseCest
{
    private string $sepaPaymentLabel = "//label[@for='payment_oscunzer_sepa-secured']";
    private string $IBANInput = "//input[contains(@id, 'unzer-iban-input')]";

    protected function getOXID(): array
    {
        return ['oscunzer_sepa-secured'];
    }

    /**
     * DEPRECATED / PAYMENT HAS BEEN REMOVED
     * @param AcceptanceTester $I
     * @group SEPADirectSecuredPaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I)
    {
        $I->markTestSkipped('Skipping this test - payment removed');
        $I->wantToTest('Test SEPA Direct Debit payment works');
        $this->initializeSecuredTest();
        $orderPage = $this->choosePayment($this->sepaPaymentLabel);

        $payment = Fixtures::get('sepa_payment');
        $I->fillField($this->IBANInput, $payment['IBAN']);
        $I->click("#oscunzersepaagreement");
        $I->wait(1);

        $orderPage->submitOrder();

        $this->checkSuccessfulPayment();
    }
}
