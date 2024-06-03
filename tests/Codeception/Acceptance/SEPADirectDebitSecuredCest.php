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
 * @group SEPADirectDebitSecuredCest
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
     * @group SEPADirectSecuredPaymentTest
     */
    public function checkPaymentWorks(AcceptanceTester $I): void
    {
        $I->markTestSkipped("SEPA Direct Debit payment is deprecated. Marking this test to be removed");
        $I->wantToTest('Test SEPA Direct Debit payment works');
        $this->initializeSecuredTest();
        $this->choosePayment($this->sepaPaymentLabel);

        $payment = Fixtures::get('sepa_payment');
        $I->fillField($this->IBANInput, $payment['IBAN']);
        $I->click("#oscunzersepaagreement");
        $I->wait(1);

        $this->submitOrder();

        $this->checkSuccessfulPayment();
    }
}
