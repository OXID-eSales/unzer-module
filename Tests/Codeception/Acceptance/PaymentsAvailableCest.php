<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Tests\Codeception\Acceptance;

use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\Eshop\Core\Registry;
use OxidSolutionCatalysts\Unzer\Tests\Codeception\AcceptanceTester;

/**
 * @group unzer_module
 * @group PaymentAvailableTest
 */
final class PaymentsAvailableCest extends BaseCest
{
    private array $paymentMethods = [
        'OSCUNZER_PAYMENT_METHOD_ALIPAY',
        //'OSCUNZER_PAYMENT_METHOD_APPLEPAY',
        'OSCUNZER_PAYMENT_METHOD_BANCONTACT', // BE only
        'OSCUNZER_PAYMENT_METHOD_CARD',
        'OSCUNZER_PAYMENT_METHOD_EPS', // AT only
        'OSCUNZER_PAYMENT_METHOD_GIROPAY',
        'OSCUNZER_PAYMENT_METHOD_IDEAL', // NL only
        'OSCUNZER_PAYMENT_METHOD_INVOICE',
        'OSCUNZER_PAYMENT_METHOD_PAYPAL',
        'OSCUNZER_PAYMENT_METHOD_PREPAYMENT',
        'OSCUNZER_PAYMENT_METHOD_PRZELEWY24', // PL only
        'OSCUNZER_PAYMENT_METHOD_SEPA',
        'OSCUNZER_PAYMENT_METHOD_SOFORT',
        'OSCUNZER_PAYMENT_METHOD_WECHATPAY',
    ];
    private array $paymentMethodsByCountry = [
        'DE' => [
            'OSCUNZER_PAYMENT_METHOD_ALIPAY',
            'OSCUNZER_PAYMENT_METHOD_CARD',
            'OSCUNZER_PAYMENT_METHOD_GIROPAY',
            'OSCUNZER_PAYMENT_METHOD_INVOICE',
            'OSCUNZER_PAYMENT_METHOD_PAYPAL',
            'OSCUNZER_PAYMENT_METHOD_PREPAYMENT',
            'OSCUNZER_PAYMENT_METHOD_SEPA',
            'OSCUNZER_PAYMENT_METHOD_SOFORT',
            'OSCUNZER_PAYMENT_METHOD_WECHATPAY',
        ],
        'AT' => [
            'OSCUNZER_PAYMENT_METHOD_EPS', // AT only
        ],
        'BE' => [
            'OSCUNZER_PAYMENT_METHOD_BANCONTACT', // BE only
        ],
        'PL' => [
            'OSCUNZER_PAYMENT_METHOD_PRZELEWY24', // PL only
        ],
        'NL' => [
            'OSCUNZER_PAYMENT_METHOD_IDEAL', // NL only
        ],
    ];
    private array $country2Id = [
        'DE' => 'a7c40f631fc920687.20179984',
        'AT' => 'a7c40f6320aeb2ec2.72885259',
        'BE' => 'a7c40f632e04633c9.47194042',
        'PL' => '8f241f1109624d3f8.50953605',
        'NL' => 'a7c40f632cdd63c52.64272623',
    ];
    private array $country2Currency = [
        'DE' => 'EUR',
        'AT' => 'EUR',
        'BE' => 'EUR',
        'PL' => 'PLN',
        'NL' => 'EUR',
    ];

    public function _before(AcceptanceTester $I): void
    {
        parent::_before($I);
    }

    private function getCountryId($country): string
    {
        $return = '';
        if (isset($this->country2Id[$country])) {
            $return = $this->country2Id[$country];
        }
        return  $return;
    }

    private function switchCurrency($currency)
    {
        $curr = '';

        switch ($currency) {
            case 'EUR':
                $curr = 'EUR@ 1.00@ ,@ .@ €@ 2';
                break;
            case 'PLN':
                $curr = 'PLN@ 4.66@ ,@ @ zł@ 2';
                break;
            case 'GBP':
                $curr = 'GBP@ 0.8565@ .@  @ £@ 2';
                break;
            case 'USD':
                $curr = 'USD@ 1.2994@ .@  @ $@ 2';
                break;
            case 'CHF':
                $curr = 'CHF@ 1.4326@ ,@ .@ <small>CHF</small>@ 2';
                break;
        }

        if ('' !== $curr) {
            $oConfig = Registry::getConfig();
            $oConfig->saveSystemConfigParameter(
                'arr',
                'aCurrencies',
                [0 => $curr]
            );
            $oConfig->setActShopCurrency(0);
        }
    }

    private function switchCountry(AcceptanceTester $I, string $country): void
    {
        if (($countryId = $this->getCountryId($country))) {
            $user = Fixtures::get('client');
            $I->updateInDatabase(
                'oxuser',
                ['oxcountryid' => $countryId],
                ['oxusername' => $user['username']]
            );
        }
    }

    protected function getOXID(): array
    {
        return [
            'oscunzer_alipay',
            'oscunzer_bancontact',
            'oscunzer_card',
            'oscunzer_eps',
            'oscunzer_giropay',
            'oscunzer_ideal',
            'oscunzer_invoice',
            'oscunzer_paypal',
            'oscunzer_prepayment',
            'oscunzer_przelewy24',
            'oscunzer_sepa',
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
        $this->initializeTest();

        foreach ($this->paymentMethodsByCountry as $country => $paymentMethods) {
            $this->switchCountry($I, $country);
            $this->switchCurrency($this->country2Currency[$country]);
            $I->reloadPage();
            $I->wait(2);
            foreach ($paymentMethods as $paymentMethod) {
                $I->waitForText(Translator::translate($paymentMethod));
            }
        }
    }
}
