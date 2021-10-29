<?php
/**
 * This file is part of OXID eSales Unzer module.
 *
 * OXID eSales Unzer module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales Unzer module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales Unzer module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2021
 */

namespace OxidSolutionCatalysts\Unzer\Core;

use OxidEsales\Eshop\Application\Model\Payment;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\ModuleActivationBridgeInterface;
use OxidEsales\Facts\Facts;
use Psr\Container\ContainerInterface;

/**
 * Class defines what module does on Shop events.
 */
class Events
{
    private static array $_aPayments = [
        //set insert = 1 to write payment into oxpayments table, install = 0 for no db insert

        //Credit cards and debit cards are the most common payment method in e-commerce.
        'oscunzer_card' => ['insert' => 1,
            'de_desc' => "Kreditkarte",
            'en_desc' => "Credit Card",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],

        //Unzer Direct Debit lets you accept payments in euro.
        'oscunzer_sepa' => ['insert' => 1,
            'de_desc' => "SEPA Lastschrift",
            'en_desc' => "SEPA Direct Debit",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],

        //Unzer Direct Debit Secured lets you accept payments in euro and secures your money.
        'oscunzer_sepa-secured' => ['insert' => 1,
            'de_desc' => "SEPA Lastschrift Secured",
            'en_desc' => "SEPA Direct Debit Secured",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],

        //Sofort is an online payment method used in select European countries.
        'oscunzer_sofort' => ['insert' => 1,
            'de_desc' => "Sofort",
            'en_desc' => "Sofort",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],

        //Unzer Invoice lets you issue an invoice and then collect the payment.
        'oscunzer_invoice' => ['insert' => 1,
            'de_desc' => "Rechnung",
            'en_desc' => "Invoice",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],

        //Unzer Invoice Secured lets you issue an invoice and then collect the payment, your payment is secured.
        'oscunzer_invoice-secured' => ['insert' => 1,
            'de_desc' => "Rechnung Secured",
            'en_desc' => "Invoice Secured",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],

        //Giropay is an online payment method used in Germany.
        'oscunzer_giropay' => ['insert' => 1,
            'de_desc' => "Giropay",
            'en_desc' => "Giropay",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],

        //iDEAL is the most popular method for online payments in the Netherlands.
        'oscunzer_ideal' => ['insert' => 1,
            'de_desc' => "iDEAL",
            'en_desc' => "iDEAL",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],

        //Unzer Prepayment lets you collect the payment before sending the goods to your customer.
        'oscunzer_prepayment' => ['insert' => 1,
            'de_desc' => "Vorkasse",
            'en_desc' => "Prepayment",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],

        //Unzer Bank Transfer lets your customers pay directly from their bank account.
        'oscunzer_banktransfer' => ['insert' => 1,
            'de_desc' => "Banktransfer",
            'en_desc' => "Bank Transfer",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],

        //Electronic Payment Standard (EPS) is an online payment system used in Austria.
        'oscunzer_eps' => ['insert' => 1,
            'de_desc' => "EPS",
            'en_desc' => "EPS",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],

        //PostFinance e-finance is an online direct payment method used in Switzerland.
        'oscunzer_post-finance' => ['insert' => 1,
            'de_desc' => "PostFinance e-finance",
            'en_desc' => "PostFinance e-finance",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],
        //Apple Pay is a popular mobile payment and digital wallet service provided by Apple.
        'oscunzer_applepay' => ['insert' => 0,
            'de_desc' => "Apple Pay",
            'en_desc' => "Apple Pay",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],
        //Unzer Installment lets your customers pay in monthly payments.
        'oscunzer_installment' => ['insert' => 1,
            'de_desc' => "Ratenzahlung",
            'en_desc' => "Installment",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],
        //PayPal is one of the world’s most popular online payment systems.
        'oscunzer_paypal' => ['insert' => 1,
            'de_desc' => "PayPal",
            'en_desc' => "PayPal",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],

        //Przelewy24 is an online payment method used in Poland.
        'oscunzer_przelewy24' => ['insert' => 0,
            'de_desc' => "Przelewy24",
            'en_desc' => "Przelewy24",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],

        //WeChat Pay is one of the biggest and fastest-growing mobile payment solutions in China.
        'oscunzer_wechatpay' => ['insert' => 0,
            'de_desc' => "WeChat Pay",
            'en_desc' => "WeChat Pay",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],

        //Alipay is China’s leading third-party mobile and online payment solution.
        'oscunzer_alipay' => ['insert' => 0,
            'de_desc' => "Alipay",
            'en_desc' => "Alipay",
            'de_longdesc' => "de text",
            'en_longdesc' => "en text"],
    ];

    /**
     * Add PayPal payment method set EN and DE long descriptions
     */
    public static function addUnzerPaymentMethods()
    {
        foreach (self::$_aPayments as $paymentid => $aPayment) {
            $payment = oxNew(Payment::class);
            if ($aPayment['insert']) {
                if (!$payment->load($paymentid)) {
                    $payment->setId($paymentid);
                    $payment->oxpayments__oxactive = new Field(0);
                    $payment->oxpayments__oxtoamount = new Field(1000000);

                    $language = Registry::getLang();
                    $languages = $language->getLanguageIds();

                    $paymentDescriptions = [
                        'en' => ["desc" => $aPayment['en_desc'], "longdesc" => $aPayment['en_longdesc']],
                        'de' => ["desc" => $aPayment['de_desc'], "longdesc" => $aPayment['de_longdesc']]
                    ];
                    foreach ($paymentDescriptions as $languageAbbreviation => $description) {
                        $languageId = array_search($languageAbbreviation, $languages);
                        if ($languageId !== false) {
                            $payment->setLanguage($languageId);
                            $payment->oxpayments__oxdesc = new Field($description['desc']);
                            $payment->oxpayments__oxlongdesc = new Field($description['longdesc']);
                            $payment->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * Check if Unzer is used for sub-shops.
     *
     * @return bool
     */
    public static function isUnzerActiveOnSubShops(): bool
    {
        $config = Registry::getConfig();
        $shops = $config->getShopIds();
        $activeShopId = $config->getShopId();
        $moduleActivationBridge = self::getContainer()->get(ModuleActivationBridgeInterface::class);

        foreach ($shops as $shopId) {
            if ($shopId != $activeShopId) {
                if ($moduleActivationBridge->isActive("osc-unzer", $shopId)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Disables Unzer payment methods
     */
    public static function disableUnzerPaymentMethods()
    {
        $payment = oxNew(Payment::class);
        foreach (self::$_aPayments as $paymentid => $aPayment) {
            if ($payment->load($paymentid)) {
                $payment->oxpayments__oxactive = new Field(0);
                $payment->save();
            }
        }
    }

    /**
     * Execute action on activate event
     */
    public static function onActivate()
    {
        // adding record to oxPayment table
        self::addUnzerPaymentMethods();
    }


    /**
     * Execute action on deactivate event
     */
    public static function onDeactivate()
    {
        // If Unzer is activated on other sub shops do not remove payment methods
        if ('EE' == (new Facts())->getEdition() && self::isUnzerActiveOnSubShops()) {
            return;
        }
        self::disableUnzerPaymentMethods();
    }

    /**
     * @return ContainerInterface
     * @internal
     *
     */
    protected static function getContainer(): ContainerInterface
    {
        return ContainerFactory::getInstance()->getContainer();
    }
}
