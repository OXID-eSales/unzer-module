<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use OxidEsales\Codeception\Module\Translation\Translator;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

/**
 * @group unzer_module
 * @group PaymentAvailableTest
 */
final class PaymentsAvailableCest extends BaseCest
{
    private $paymentMethods = [
        'PAYMENT_OSCUNZER_ALIPAY',
        //'PAYMENT_OSCUNZER_APPLEPAY',
        'PAYMENT_OSCUNZER_BANCONTACT',
        'PAYMENT_OSCUNZER_CARD',
        'PAYMENT_OSCUNZER_EPS',
        'PAYMENT_OSCUNZER_GIROPAY',
        'PAYMENT_OSCUNZER_IDEAL',
        'PAYMENT_OSCUNZER_INSTALLMENT',
        'PAYMENT_OSCUNZER_INVOICE',
        'PAYMENT_OSCUNZER_PAYPAL',
        'PAYMENT_OSCUNZER_PIS',
        'PAYMENT_OSCUNZER_PREPAYMENT',
        'PAYMENT_OSCUNZER_PRZELEWY24',
        'PAYMENT_OSCUNZER_SEPA',
        'PAYMENT_OSCUNZER_SEPA-SECURED',
        'PAYMENT_OSCUNZER_SOFORT',
        'PAYMENT_OSCUNZER_WECHATPAY',
    ];

    protected function _getOXID(): array
    {
        return [
            'oscunzer_alipay',
            'oscunzer_bancontact',
            'oscunzer_card',
            'oscunzer_eps',
            'oscunzer_giropay',
            'oscunzer_ideal',
            'oscunzer_installment',
            'oscunzer_invoice',
            'oscunzer_paypal',
            'oscunzer_pis',
            'oscunzer_prepayment',
            'oscunzer_przelewy24',
            'oscunzer_sepa',
            'oscunzer_sepa-secured',
            'oscunzer_sofort',
            'oscunzer_wechatpay'
        ];
    }

    /**
     * @param AcceptanceTester $I
     */
    public function checkPaymentsAvailable(AcceptanceTester $I)
    {
        $I->wantToTest('Test payment methods are available');
        $this->_initializeTest();

        foreach ($this->paymentMethods as $onePaymentMethod) {
            $I->waitForText(Translator::translate($onePaymentMethod));
        }
    }
}
