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
 * @copyright 2003-2021 OXID eSales AG
 * @link      http://www.oxid-esales.com
 * @author    OXID Solution Catalysts
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
        'oscunzer_card' => [
            'insert' => 1,
            'de_desc' => "Kreditkarte",
            'en_desc' => "Credit Card",
            'de_longdesc' => "Von Europa, über Nordamerika bis Asien: kartenbasierte Zahlungsmethoden sind international
            verbreitet. In vielen Teilen der Welt haben sie schon längst das Bargeld abgelöst. Mit Unzer können Sie einfach
            alle wichtigen Anbieter akzeptieren.",
            'en_longdesc' => "From Europe to North America to Asia: card-based payment methods are widely used around the
            world. In many parts of the world they have long since replaced cash. With Unzer you can easily accept all major
            providers."
        ],

        //Unzer Direct Debit lets you accept payments in euro.
        'oscunzer_sepa' => [
            'insert' => 1,
            'de_desc' => "SEPA-Lastschrift",
            'en_desc' => "SEPA Direct Debit",
            'de_longdesc' => "Sie erteilen ein SEPA-Lastschriftmandat",
            'en_longdesc' => "You issue a SEPA direct debit mandate"
        ],

        //Unzer Direct Debit Secured lets you accept payments in euro and secures your money.
        'oscunzer_sepa-secured' => [
            'insert' => 1,
            'de_desc' => "SEPA-Lastschrift Secured",
            'en_desc' => "SEPA Direct Debit Secured",
            'de_longdesc' => "Sie erteilen ein SEPA-Lastschriftmandat",
            'en_longdesc' => "You issue a SEPA direct debit mandate through a"
        ],

        //Sofort is an online payment method used in select European countries.
        'oscunzer_sofort' => [
            'insert' => 1,
            'de_desc' => "Sofort",
            'en_desc' => "Sofort",
            'de_longdesc' => "Sofortüberweisung (gesicherter Kanal)",
            'en_longdesc' => "Instant bank transfer (secure channel)"
        ],

        //Unzer Invoice lets you issue an invoice and then collect the payment.
        'oscunzer_invoice' => [
            'insert' => 1,
            'de_desc' => "Rechnung",
            'en_desc' => "Invoice",
            'de_longdesc' => "Rechnung, ausgestellt von Unzer",
            'en_longdesc' => "Invoice, issued by Unzer"
        ],

        //Unzer Invoice Secured lets you issue an invoice and then collect the payment, your payment is secured.
        'oscunzer_invoice-secured' => [
            'insert' => 1,
            'de_desc' => "Rechnung Secured",
            'en_desc' => "Invoice Secured",
            'de_longdesc' => "Rechnung, ausgestellt von Unzer (gesicherter Kanal)",
            'en_longdesc' => "Invoice, issued by Unzer (secure channel)"
        ],

        //Giropay is an online payment method used in Germany.
        'oscunzer_giropay' => [
            'insert' => 1,
            'de_desc' => "Giropay",
            'en_desc' => "Giropay",
            'de_longdesc' => "Giropay ist besonders in Deutschland stark verbreitet. Das Online-Verfahren wurde von der
            deutschen Kreditwirtschaft speziell für den E-Commerce konzipiert und optimiert. Käufer können damit sicher
            per Vorkasse bezahlen. Dabei werden keine sensiblen Daten an den Händler übermittelt.",
            'en_longdesc' => "Giropay is particularly widespread in Germany. The online process was specially designed
            and optimized by the German banking industry for e-commerce. Buyers can use it to pay securely in advance. No
            sensitive data is transmitted to the dealer."
        ],

        //iDEAL is the most popular method for online payments in the Netherlands.
        'oscunzer_ideal' => [
            'insert' => 1,
            'de_desc' => "iDEAL",
            'en_desc' => "iDEAL",
            'de_longdesc' => "iDEAL ist die beliebteste Zahlungsmethode im niederländischen E-Commerce und nahtlos in das
            Online-Banking fast aller großen Banken des Landes integriert. So können die Käufer mit ihren vertrauten
            Zugangsdaten bezahlen. Dieser komfortable Checkout-Prozess steigert Beliebtheit und Conversions. Für fast jede
            zweite Online-Transaktion in den Niederlanden wird iDEAL verwendet.",
            'en_longdesc' => "iDEAL is the most popular payment method in Dutch e-commerce and is seamlessly integrated into
            the online banking of almost all major banks in the country. Buyers can pay with their familiar access data.
            This convenient checkout process increases popularity and conversions. IDEAL is used for almost every second
            online transaction in the Netherlands."
        ],

        //Unzer Prepayment lets you collect the payment before sending the goods to your customer.
        'oscunzer_prepayment' => [
            'insert' => 1,
            'de_desc' => "Vorkasse",
            'en_desc' => "Prepayment",
            'de_longdesc' => "Bei der Vorauskasse oder Vorkasse erklärt schon der Name, wie die Zahlungsmethode funktioniert:
            Online-Käufer überweisen das Geld für Ihre Bestellung im Voraus. Erst wenn der Betrag eingegangen ist, verschickt
            der Händler die Ware.",
            'en_longdesc' => "In the case of prepayment or prepayment, the name already explains how the payment method works:
            online buyers transfer the money for their order in advance. Only when the amount has been received does the
            dealer send the goods."
        ],

        //Unzer Bank Transfer lets your customers pay directly from their bank account.
        'oscunzer_banktransfer' => [
            'insert' => 1,
            'de_desc' => "Banktransfer",
            'en_desc' => "Bank Transfer",
            'de_longdesc' => "Unzer Bank Transfer ist unser Direktüberweisungs- oder auch Zahlungsauslösedienst. Mit ihm
            können Käufer im Checkout-Prozess komfortabel eine Überweisung beauftragen. Dazu geben sie die Zugangsdaten
            ihres Online-Bankings ein und authentifizieren sich zusätzlich mit einer TAN. Unzer bank transfer prüft in Echtzeit,
            ob das Konto gedeckt ist. Der Betrag wird direkt abgebucht.",
            'en_longdesc' => "Unzer Bank Transfer is our direct transfer or payment initiation service. With it, buyers
            can conveniently order a transfer in the checkout process. To do this, they enter the access data for their
            online banking and also authenticate themselves with a TAN. Unzer bank transfer checks in real time whether
            the account is sufficient. The amount will be debited directly."
        ],

        //Electronic Payment Standard (EPS) is an online payment system used in Austria.
        'oscunzer_eps' => [
            'insert' => 1,
            'de_desc' => "EPS",
            'en_desc' => "EPS",
            'de_longdesc' => "Electronic Payment Standard – oder kurz eps – ist ein österreichisches Direktüberweisung-Verfahren.
            Es wurde von den größten Banken des Landes gemeinsam mit der Regierung konzipiert. Ähnlich wie mit dem deutschen
            Gegenstück Giropay können Käufer mit eps sicher und ohne Übermittlung von sensiblen Daten zahlen. Händlern bietet
            Electronic Payment Standard vor allem Schutz vor Zahlungsausfällen.",
            'en_longdesc' => "Electronic Payment Standard - or eps for short - is an Austrian direct transfer procedure.
            It was designed by the country's largest banks together with the government. Similar to the German counterpart
            Giropay, buyers can use eps to pay securely and without transmitting sensitive data. Electronic Payment Standard
            offers merchants above all protection against payment defaults."
        ],

        //PostFinance e-finance is an online direct payment method used in Switzerland.
        'oscunzer_post-finance' => [
            'insert' => 1,
            'de_desc' => "PostFinance E-Finance",
            'en_desc' => "PostFinance E-Finance",
            'de_longdesc' => "Die PostFinance AG ist zu 100% eine Konzerngesellschaft der Schweizerischen Post AG. Sie gehört
            zu den führenden Finanzinstituten der Schweiz. Über 3 Millionen Privat- und Geschäftskunden vertrauen ihr. Mit
            dem Onlinebanking PostFinance E-Finance können Käufer elektronisch bezahlen. Die Voraussetzungen: ein Bankkonto
            bei der PostFinance und Schweizer Franken als Währung.",
            'en_longdesc' => "PostFinance AG is a 100% subsidiary of Swiss Post AG. It is one of the leading financial institutions
            in Switzerland. Over 3 million private and business customers trust her. Buyers can pay electronically with PostFinance
            e-finance online banking. The requirements: a bank account with PostFinance and Swiss francs as the currency."
        ],
        //Apple Pay is a popular mobile payment and digital wallet service provided by Apple.
        'oscunzer_applepay' => [
            'insert' => 0,
            'de_desc' => "Apple Pay",
            'en_desc' => "Apple Pay",
            'de_longdesc' => "Apple Pay ist das Zahlungssystem des Technologieunternehmens Apple. Käufer können damit kontaktlos
            und sicher in Geschäften, in Apps oder in Online-Shops bezahlen. Am POS halten sie dazu ihr Smart-phone an das
            Kassenterminal und legen den Finger auf die Touch ID. Daraufhin wird die Transaktion wird mit Hilfe von Near
            Field Communication (NFC) und der eigenen Wallet App durchgeführt. Darin muss eine Kreditkarte hinterlegt sein,
            deren Anbieter mit Apple kooperiert.",
            'en_longdesc' => "Apple Pay is the payment system from the technology company Apple. Buyers can use it to make
            contactless and secure payments in shops, in apps or in online shops. To do this, at the POS, hold your smartphone
            to the cash register terminal and place your finger on the Touch ID. The transaction is then carried out with
            the help of Near Field Communication (NFC) and your own wallet app. A credit card whose provider cooperates with
            Apple must be stored in it."
        ],
        //Unzer Installment lets your customers pay in monthly payments.
        'oscunzer_installment' => [
            'insert' => 1,
            'de_desc' => "Ratenzahlung",
            'en_desc' => "Installment",
            'de_longdesc' => "Ratenzahlung mit Unzer",
            'en_longdesc' => "Unzer installment"
        ],
        //PayPal is one of the world’s most popular online payment systems.
        'oscunzer_paypal' => [
            'insert' => 1,
            'de_desc' => "PayPal",
            'en_desc' => "PayPal",
            'de_longdesc' => "Paypal kann zum Zahlen per Banküberweisung, Kreditkarte oder Lastschrift verwendet werden.
            246 Millionen Käufer nutzen das e-Wallet weltweit – in über 200 Märkten und 25 Währungen. Allein in Deutschland
            erreichen Sie etwa 25 Millionen PayPal-Kunden. Anschließend bezahlen Käufer damit vor allem in Online-Shops.
            Aber auch im stationären Handel nutzen sie die Google Pay App auf ihrem Smartphone. Da während der Transaktion
            keine Bankdaten übertragen werden, gilt die die Zahlung als sicher.",
            'en_longdesc' => "Paypal can be used to pay by bank transfer, credit card, or direct debit. 246 million buyers
            use the e-wallet worldwide - in over 200 markets and 25 currencies. In Germany alone you can reach around 25
            million PayPal customers. Buyers then use it to pay in online shops in particular. But they also use the Google
            Pay app on their smartphone in brick-and-mortar retail. Since no bank details are transferred during the transaction,
            the payment is considered secure. "
        ],

        //Przelewy24 is an online payment method used in Poland.
        'oscunzer_przelewy24' => [
            'insert' => 0,
            'de_desc' => "Przelewy24",
            'en_desc' => "Przelewy24",
            'de_longdesc' => "Przelewy24 ist die beliebteste Online-Zahlungsmethode Polens. Sie ermöglicht Käufern nicht
            nur Zahlungen vom eigenen Bankkonto oder der Kreditkarte. Sondern unterstützt auch alternative Zahlungsmethoden
            wie SMS. Voraussetzung für die Nutzung ist ein Konto bei einer der über 150 polnischen Banken, die Przelewy24
            unterstützt.",
            'en_longdesc' => "Przelewy24 is the most popular online payment method in Poland. It not only enables buyers
            to make payments from their own bank account or credit card. It also supports alternative payment methods such
            as SMS. The requirement for use is an account with one of the more than 150 Polish banks that Przelewy24 supports."
        ],

        //WeChat Pay is one of the biggest and fastest-growing mobile payment solutions in China.
        'oscunzer_wechatpay' => [
            'insert' => 0,
            'de_desc' => "WeChat Pay",
            'en_desc' => "WeChat Pay",
            'de_longdesc' => "WeChat Pay ist ein von Tencent betriebenes Bezahlsystem aus China. Bei WeChat handelte es
            sich ursprünglich um eine Kommunikationsapp, analog zu WhatsApp. Im Laufe der Zeit wurde die App um immer mehr
            Tools erweitert – im Jahr 2015 auch um ein Bezahlsystem vergleichbar mit Google Pay oder Apple Pay. Dank der
            sozialen Plattform WeChat verfügt WeChat Pay über eine riesige Nutzerbasis von über einer Milliarde Chat-Nutzern.
            Von diesen vertrauen bereits etwa 600 Millionen WeChat Pay.",
            'en_longdesc' => "WeChat Pay is a payment system from China operated by Tencent. WeChat was originally a communication
            app, analogous to WhatsApp. Over time, the app has been expanded to include more and more tools - in 2015 also
            a payment system comparable to Google Pay or Apple Pay. Thanks to the WeChat social platform, WeChat Pay has
            a huge user base of over a billion chat users. Of these, around 600 million already trust WeChat Pay."
        ],

        //Alipay is China’s leading third-party mobile and online payment solution.
        'oscunzer_alipay' => [
            'insert' => 0,
            'de_desc' => "Alipay",
            'en_desc' => "Alipay",
            'de_longdesc' => "Alipay ist Chinas führende Zahlungs- und Lifestyleplattform. Sie wurde 2004 von der Alibaba
            Group gegründet. Inzwischen zählt Alipay 870 Millionen Nutzer. 80 Prozent Marktanteil im Mobile Payment- und
            mehr als 50 Prozent im Online-Bereich machen AliPay unverzichtbar für Händler, die nach China verkaufen. Auch
            die vielen chinesischen Touristen bezahlen im Ausland gerne mit ihrer heimischen Zahlungsmethode. Das wichtigste
            Produkt ist das Alipay Wallet. Mit der zugehörigen App können Käufer Transaktionen direkt mit dem Smartphone vornehmen.",
            'en_longdesc' => "Alipay is China's leading payment and lifestyle platform. It was founded in 2004 by the Alibaba
            Group. Alipay now has 870 million users. 80 percent of the market share in mobile payment and more than 50 percent
            in the online area make AliPay indispensable for merchants who sell to China. The many Chinese tourists abroad
            also like to pay with their domestic payment method. The most important product is the Alipay wallet. With the
            associated app, buyers can carry out transactions directly with their smartphone."
        ],
    ];

    /**
     * Add Unzer payment methods set EN and DE (long) descriptions
     *
     * @return void
     */
    public static function addUnzerPaymentMethods()
    {
        foreach (self::$_aPayments as $paymentid => $aPayment) {
            $payment = oxNew(Payment::class);
            if (($aPayment['insert']) && (!$payment->load($paymentid))) {
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
            if (($shopId != $activeShopId) && ($moduleActivationBridge->isActive("osc-unzer", $shopId))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Disables Unzer payment methods
     *
     * @return void
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
     *
     * @return void
     */
    public static function onActivate()
    {
        // adding record to oxPayment table
        self::addUnzerPaymentMethods();
    }


    /**
     * Execute action on deactivate event
     *
     * @return void
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
     * ContainerFactory, ContainerInterface
     *
     * @return   ContainerInterface
     * @internal
     */
    protected static function getContainer(): ContainerInterface
    {
        return ContainerFactory::getInstance()->getContainer();
    }
}
