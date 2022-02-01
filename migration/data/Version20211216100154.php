<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Migrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\ConfigFile;
use OxidEsales\Facts\Facts;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211216100154 extends AbstractMigration
{
    /** @var array[]|string[] */
    private $defaultLanguageAbbr = [
        'de' => 'Deutsch',
        'en' => 'English'
    ];

    /** @var array[] */
    private $paymentDefinitions = [
        //set insert = 1 to write payment into oxpayments table, install = 0 for no db insert

        //Alipay is China’s leading third-party mobile and online payment solution.
        'oscunzer_alipay' => [
            'insert' => 1,
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
            associated app, buyers can carry out transactions directly with their smartphone.",
            'countries' => []
        ],

        //Bancontact is a Belgian company that offers user-friendly solutions for easy everyday shopping experience.
        'oscunzer_bancontact' => [
            'insert' => 1,
            'de_desc' => "Bancontact",
            'en_desc' => "Bancontact",
            'de_longdesc' => "Bancontact ist ein belgisches Unternehmen, das benutzerfreundliche Lösungen für ein einfaches tägliches Einkaufserlebnis bietet.",
            'en_longdesc' => "Bancontact is a Belgian company that offers user-friendly solutions for easy everyday shopping experience.",
            'countries' => ['BE']
        ],

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
            providers.",
            'countries' => []
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
            offers merchants above all protection against payment defaults.",
            'countries' => ['AT']
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
            sensitive data is transmitted to the dealer.",
            'countries' => ['DE']
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
            online transaction in the Netherlands.",
            'countries' => ['NL']
        ],

        //Unzer Installment lets your customers pay in monthly payments.
        'oscunzer_installment' => [
            'insert' => 1,
            'de_desc' => "Ratenzahlung",
            'en_desc' => "Installment",
            'de_longdesc' => "Ratenzahlung mit Unzer",
            'en_longdesc' => "Unzer installment",
            'countries' => ['DE', 'AT']
        ],

        //Unzer Invoice lets you issue an invoice and then collect the payment.
        'oscunzer_invoice' => [
            'insert' => 1,
            'de_desc' => "Rechnung",
            'en_desc' => "Invoice",
            'de_longdesc' => "Rechnung, ausgestellt von Unzer",
            'en_longdesc' => "Invoice, issued by Unzer",
            'countries' => ['BE', 'DE', 'EE', 'FI', 'FR', 'GR', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PT', 'SK', 'SI', 'ES', 'CY', 'AT']
        ],

        //Unzer Invoice Secured lets you issue an invoice and then collect the payment, your payment is secured.
        'oscunzer_invoice-secured' => [
            'insert' => 1,
            'de_desc' => "Rechnung Secured",
            'en_desc' => "Invoice Secured",
            'de_longdesc' => "Rechnung, ausgestellt von Unzer (gesicherter Kanal)",
            'en_longdesc' => "Invoice, issued by Unzer (secure channel)",
            'countries' => ['DE', 'AT']
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
            the payment is considered secure. ",
            'countries' => []
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
            dealer send the goods.",
            'countries' => ['BE', 'DE', 'EE', 'FI', 'FR', 'GR', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PT', 'SK', 'SI', 'ES', 'CY', 'AT']
        ],


        //Przelewy24 is an online payment method used in Poland.
        'oscunzer_przelewy24' => [
            'insert' => 1,
            'de_desc' => "Przelewy24",
            'en_desc' => "Przelewy24",
            'de_longdesc' => "Przelewy24 ist die beliebteste Online-Zahlungsmethode Polens. Sie ermöglicht Käufern nicht
            nur Zahlungen vom eigenen Bankkonto oder der Kreditkarte. Sondern unterstützt auch alternative Zahlungsmethoden
            wie SMS. Voraussetzung für die Nutzung ist ein Konto bei einer der über 150 polnischen Banken, die Przelewy24
            unterstützt.",
            'en_longdesc' => "Przelewy24 is the most popular online payment method in Poland. It not only enables buyers
            to make payments from their own bank account or credit card. It also supports alternative payment methods such
            as SMS. The requirement for use is an account with one of the more than 150 Polish banks that Przelewy24 supports.",
            'countries' => ['PL']
        ],

        //Unzer Direct Debit lets you accept payments in euro.
        'oscunzer_sepa' => [
            'insert' => 1,
            'de_desc' => "SEPA-Lastschrift",
            'en_desc' => "SEPA Direct Debit",
            'de_longdesc' => "Sie erteilen ein SEPA-Lastschriftmandat",
            'en_longdesc' => "You issue a SEPA direct debit mandate",
            'countries' => ['BE', 'DE', 'EE', 'FI', 'FR', 'GR', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PT', 'SK', 'SI', 'ES', 'CY', 'AT']
        ],

        //Unzer Direct Debit Secured lets you accept payments in euro and secures your money.
        'oscunzer_sepa-secured' => [
            'insert' => 1,
            'de_desc' => "SEPA-Lastschrift Secured",
            'en_desc' => "SEPA Direct Debit Secured",
            'de_longdesc' => "Sie erteilen ein SEPA-Lastschriftmandat",
            'en_longdesc' => "You issue a SEPA direct debit mandate through a",
            'countries' => ['DE']
        ],

        //Sofort is an online payment method used in select European countries.
        'oscunzer_sofort' => [
            'insert' => 1,
            'de_desc' => "Sofort",
            'en_desc' => "Sofort",
            'de_longdesc' => "Sofortüberweisung (gesicherter Kanal)",
            'en_longdesc' => "Instant bank transfer (secure channel)",
            'countries' => ['SE', 'NO', 'FI', 'DK', 'DE', 'NL', 'BE', 'CH', 'FR', 'IT', 'PL', 'ES', 'PT', 'GB', 'HU', 'CZ', 'AU', 'SK', 'US']
        ],

        //Unzer Bank Transfer lets your customers pay directly from their bank account.
        'oscunzer_pis' => [
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
            the account is sufficient. The amount will be debited directly.",
            'countries' => ['DE', 'AT']
        ],

        //WeChat Pay is one of the biggest and fastest-growing mobile payment solutions in China.
        'oscunzer_wechatpay' => [
            'insert' => 1,
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
            a huge user base of over a billion chat users. Of these, around 600 million already trust WeChat Pay.",
            'countries' => []
        ],
    ];

    /** @var array[] */
    private $staticContents = [
        [
            'oxloadid' => 'oscunzersepamandatetext',
            'oxactive' => 1,
            'oxtitle_de' => 'SEPA Lastschrift-Mandat (Bankeinzug)',
            'oxtitle_en' => 'SEPA direct debit mandate (direct debit)',
            'oxcontent_de' => '<p>Ich ermächtige [{$oxcmp_shop->oxshops__oxname->value}], Zahlungen von meinem Konto mittels
                SEPA Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an, die von
                [{$oxcmp_shop->oxshops__oxname->value}] auf mein Konto gezogenen SEPA Lastschriften einzulösen.</p>
                <p>Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum,die Erstattung des belasteten
                Betrags verlangen. Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.</p>
                <p>Für den Fall der Nichteinlösung der Lastschriften oder des Widerspruchs gegen die Lastschriften weise ich meine Bank
                unwiderruflich an, [{$oxcmp_shop->oxshops__oxname->value}]oder Dritten auf Anforderung meinen Namen, Adresse und Geburtsdatum
                vollständig mitzuteilen.</p>',
            'oxcontent_en' => '<p>By signing this mandate form, you authorise [{$oxcmp_shop->oxshops__oxname->value}] to send instructions to
                your bank to debit your account and your bank to debit your account in accordance with the instructions from
                [{$oxcmp_shop->oxshops__oxname->value}].</p>
                <p>Note: As part of your rights, you are entitled to a refund from your bank under the terms and conditions of your
                agreement with your bank.</p>
                <p>A refund must be claimed within 8 weeks starting from the date on which your account was debited. Your rights regarding
                this SEPA mandate are explained in a statement that you can obtain from your bank.<br><br>In case of refusal or rejection
                of direct debit payment I instruct my bank irrevocably to inform [{$oxcmp_shop->oxshops__oxname->value}] or any
                third party upon request about my name, address and date of birth.</p>'
        ],
        [
            'oxloadid' => 'oscunzersepamandateconfirmation',
            'oxactive' => 1,
            'oxtitle_de' => 'Unzer Sepa',
            'oxtitle_en' => 'Unzer Sepa Text',
            'oxcontent_de' => '[{oxifcontent ident="oscunzersepamandatetext" object="oCont"}]
                <a rel="nofollow" href="[{ $oCont->getLink() }]" onclick="window.open(\'[{ $oCont->getLink()|oxaddparams:\'plain=1\'}]\', \'agb_popup\', \'resizable=yes,status=no,scrollbars=yes,menubar=no,width=620,height=400\');return false;" class="fontunderline">Sepa-Mandat</a> bestätigen.
                [{/oxifcontent}]',
            'oxcontent_en' => '[{oxifcontent ident="oscunzersepamandatetext" object="oCont"}]
                Confirm <a rel="nofollow" href="[{ $oCont->getLink() }]" onclick="window.open(\'[{ $oCont->getLink()|oxaddparams:"plain=1"}]\', \'sepa_popup\', \'resizable=yes,status=no,scrollbars=yes,menubar=no,width=620,height=400\');return false;" class="fontunderline">Sepa-Mandate</a>.
                [{/oxifcontent}]'
        ],
        [
            'oxloadid' => 'oscunzerinstallmentconfirmation',
            'oxactive' => 1,
            'oxtitle_de' => 'Unzer Ratenkauf-Bestätigung',
            'oxtitle_en' => 'TR: Unzer Installment Text',
            'oxcontent_de' => '<p>Bitte lesen Sie den Inhalt des Vertrags (siehe PDF) und bestätigen Sie die Konditionen.</p><p></p>',
            'oxcontent_en' => 'TR: <p>Bitte lesen Sie den Inhalt des Vertrags (siehe PDF) und bestätigen Sie die Konditionen.</p><p></p>'
        ]
    ];

    /** @var array[] */
    private $rdfaDefinitions = [
        'oscunzer_card_mastercard' => [
            'oxpaymentid' => 'oscunzer_card',
            'oxobjectid' => 'MasterCard',
            'oxtype' => 'rdfapayment',
        ],
        'oscunzer_card_visa' => [
            'oxpaymentid' => 'oscunzer_card',
            'oxobjectid' => 'VISA',
            'oxtype' => 'rdfapayment',
        ],
        'oscunzer_card_americanexpress' => [
            'oxpaymentid' => 'oscunzer_card',
            'oxobjectid' => 'AmericanExpress',
            'oxtype' => 'rdfapayment',
        ],
        'oscunzer_card_dinersclub' => [
            'oxpaymentid' => 'oscunzer_card',
            'oxobjectid' => 'DinersClub',
            'oxtype' => 'rdfapayment',
        ],
        'oscunzer_card_jcb' => [
            'oxpaymentid' => 'oscunzer_card',
            'oxobjectid' => 'JCB',
            'oxtype' => 'rdfapayment',
        ],
        'oscunzer_prepayment' => [
            'oxpaymentid' => 'oscunzer_prepayment',
            'oxobjectid' => 'ByBankTransferInAdvance',
            'oxtype' => 'rdfapayment',
        ],
        'oscunzer_pis' => [
            'oxpaymentid' => 'oscunzer_pis',
            'oxobjectid' => 'ByBankTransferInAdvance',
            'oxtype' => 'rdfapayment',
        ],
        'oscunzer_invoice' => [
            'oxpaymentid' => 'oscunzer_invoice',
            'oxobjectid' => 'ByInvoice',
            'oxtype' => 'rdfapayment',
        ],
        'oscunzer_invoice-secured' => [
            'oxpaymentid' => 'oscunzer_invoice-secured',
            'oxobjectid' => 'ByInvoice',
            'oxtype' => 'rdfapayment',
        ],
        'oscunzer_sepa' => [
            'oxpaymentid' => 'oscunzer_sepa',
            'oxobjectid' => 'DirectDebit',
            'oxtype' => 'rdfapayment',
        ],
        'ooscunzer_sepa-secured' => [
            'oxpaymentid' => 'oscunzer_sepa-secured',
            'oxobjectid' => 'DirectDebit',
            'oxtype' => 'rdfapayment',
        ],
        'oscunzer_paypal' => [
            'oxpaymentid' => 'oscunzer_paypal',
            'oxobjectid' => 'PayPal',
            'oxtype' => 'rdfapayment',
        ],
    ];

    /** @var array[] */
    protected $isoCountries;

    /** @var array[] */
    protected $languageIds = [];

    /** @throws Exception */
    public function __construct($version)
    {
        parent::__construct($version);

        $this->platform->registerDoctrineTypeMapping('enum', 'string');
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->createTransactionTable($schema);
        $this->createPayments();
        $this->createStaticContent();
        $this->changeUserTable($schema);
        $this->createDeliverySets();
        $this->createRdfa();
    }

    public function down(Schema $schema): void
    {
        //dropping column 'customerid' in oxuser table
        $oxuser = $schema->getTable('oxuser');
        $oxuser->dropColumn('CUSTOMERID');

        // dropping table 'oscunzertransaction'
        $schema->dropTable('oscunzertransaction');

        //deleting all unzer related rdfapayment entries from oxobject2payment table
        $this->addSql("DELETE FROM `oxobject2payment` where `OXPAYMENTID` like 'oscunzer_%' and `OXTYPE` = 'rdfapayment'");

        //deleting all unzer related oxdelset entries from oxobject2payment table
        $this->addSql("DELETE FROM `oxobject2payment` where `OXPAYMENTID` like 'oscunzer_%' and `OXTYPE` = 'oxdelset'");

        //deleting all unzer related entries from oxpayments table
        $this->addSql("DELETE FROM `oxpayments` where `OXID` like 'oscunzer_%'");

        //deleting all unzer related entries from oxcontents table
        $this->addSql("DELETE FROM `oxcontents` where `OXLOADID` like 'oscunzer%'");

        //deleting all unzer related oxcountry entries from oxobject2payment table
        $this->addSql("DELETE FROM `oxobject2payment` where `OXPAYMENTID` like 'oscunzer_%' and `OXTYPE` = 'oxcountry'");
    }

    /**
     * create Transaction Table
     */
    protected function createTransactionTable(Schema $schema): void
    {
        if (!$schema->hasTable('oscunzertransaction')) {
            $transaction = $schema->createTable('oscunzertransaction');
        } else {
            $transaction = $schema->getTable('oscunzertransaction');
        }

        if (!$transaction->hasColumn('OXID')) {
            $transaction->addColumn('OXID', Types::STRING, ['columnDefinition' => 'char(32) collate latin1_general_ci']);
        }
        if (!$transaction->hasColumn('SHORTID')) {
            $transaction->addColumn('SHORTID', Types::STRING, ['default' => ""]);
        }
        if (!$transaction->hasColumn('OXORDERID')) {
            $transaction->addColumn('OXORDERID', Types::STRING, ['columnDefinition' => 'char(32) collate latin1_general_ci', 'comment' => 'OXID (oxorder)']);
        }
        if (!$transaction->hasColumn('OXSHOPID')) {
            $transaction->addColumn('OXSHOPID', Types::INTEGER, ['columnDefinition' => 'int(11)', 'default' => 1, 'comment' => 'Shop ID (oxshops)']);
        }
        if (!$transaction->hasColumn('OXUSERID')) {
            $transaction->addColumn('OXUSERID', Types::STRING, ['columnDefinition' => 'char(32) collate latin1_general_ci', 'comment' => 'OXID (oxuser)']);
        }
        if (!$transaction->hasColumn('AMOUNT')) {
            $transaction->addColumn('AMOUNT', Types::FLOAT, ['columnDefinition' => 'DOUBLE', 'default' => 0]);
        }
        if (!$transaction->hasColumn('CURRENCY')) {
            $transaction->addColumn('CURRENCY', Types::STRING, ['default' => ""]);
        }
        if (!$transaction->hasColumn('TYPEID')) {
            $transaction->addColumn('TYPEID', Types::STRING, ['default' => ""]);
        }
        if (!$transaction->hasColumn('METADATA')) {
            $transaction->addColumn('METADATA', Types::JSON, ['default' => ""]);
        }
        if (!$transaction->hasColumn('CUSTOMERID')) {
            $transaction->addColumn('CUSTOMERID', Types::STRING, ['default' => ""]);
        }
        if (!$transaction->hasColumn('OXACTIONDATE')) {
            $transaction->addColumn('OXACTIONDATE', Types::DATETIME_MUTABLE, ['columnDefinition' => 'timestamp default "0000-00-00 00:00:00"']);
        }
        if (!$transaction->hasColumn('OXACTION')) {
            $transaction->addColumn('OXACTION', Types::STRING, ['default' => ""]);
        }
        if (!$transaction->hasColumn('OXTIMESTAMP')) {
            $transaction->addColumn('OXTIMESTAMP', Types::DATETIME_MUTABLE, ['columnDefinition' => 'timestamp default current_timestamp on update current_timestamp']);
        }
        if (!$transaction->hasPrimaryKey()) {
            $transaction->setPrimaryKey(['OXID']);
        }
        if (!$transaction->hasIndex('TRANSACTIONUNIQUE')) {
            $transaction->addUniqueIndex(['OXSHOPID', 'OXORDERID', 'OXUSERID', 'AMOUNT', 'SHORTID', 'CUSTOMERID', 'OXACTION'], 'TRANSACTIONUNIQUE');
        }
    }

    /**
     * create payments
     */
    protected function createPayments(): void
    {
        foreach ($this->paymentDefinitions as $paymentId => $paymentDefinitions) {
            if ($paymentDefinitions['insert'] === 0) {
                continue;
            }
            $langRows = '';
            $sqlPlaceHolder = '?, ?, ?, ?, ?';
            $sqlValues = [$paymentId, 1, 0, 10000, 'abs'];
            foreach ($this->getLanguageIds() as $langId => $langAbbr) {
                $langRows .= ($langId == 0) ? ', `OXDESC`, `OXLONGDESC`' :
                    sprintf(', `OXDESC_%s`, `OXLONGDESC_%s`', $langId, $langId);
                $sqlPlaceHolder .= ', ?, ?';
                $sqlValues[] = $paymentDefinitions[$langAbbr . '_desc'];
                $sqlValues[] = $paymentDefinitions[$langAbbr . '_longdesc'];
            }

            $this->addSql(
                "INSERT IGNORE INTO `oxpayments` (`OXID`, `OXACTIVE`, `OXFROMAMOUNT`, `OXTOAMOUNT`, `OXADDSUMTYPE`
                " . $langRows . ")
                VALUES (" . $sqlPlaceHolder . ")",
                $sqlValues
            );

            // set Payment to Countries
            foreach ($paymentDefinitions['countries'] as $country) {
                if (array_key_exists($country, $this->getIsoCountries())) {
                    $this->addSql(
                        "INSERT IGNORE INTO `oxobject2payment`
                        (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                        VALUES(?, ?, ?, ?)",
                        [
                            md5($paymentId . $this->getIsoCountries()[$country] . ".oxcountry"),
                            $paymentId,
                            $this->getIsoCountries()[$country],
                            'oxcountry'
                        ]
                    );
                }
            }
        }

        $this->addSql("UPDATE `oxpayments`
          SET `OXFROMAMOUNT` = 10,`OXTOAMOUNT` = 1000
         WHERE (`OXID` = 'oscunzer_sepa-secured' || `OXID` = 'oscunzer_invoice-secured')
          ;");
    }

    /**
     * create static contents
     */
    protected function createStaticContent(): void
    {
        foreach ($this->staticContents as $content) {
            $langRows = '';
            $sqlPlaceHolder = '?, ?, ?';
            $sqlValues = [md5($content['oxloadid'] . '1'), $content['oxloadid'], 1];
            foreach ($this->getLanguageIds() as $langId => $langAbbr) {
                if (isset($content['oxcontent_' . $langAbbr])) {
                    $langRows .= ($langId == 0) ? ', `OXTITLE`, `OXCONTENT`, `OXACTIVE`' :
                        sprintf(', `OXTITLE_%s`, `OXCONTENT_%s`, `OXACTIVE_%s`', $langId, $langId, $langId);
                    $sqlPlaceHolder .= ', ?, ?, ?';
                    $sqlValues[] = $content['oxtitle_' . $langAbbr];
                    $sqlValues[] = $content['oxcontent_' . $langAbbr];
                    $sqlValues[] = '1';
                }
            }

            $this->addSql(
                "INSERT IGNORE INTO `oxcontents` (`OXID`, `OXLOADID`, `OXSHOPID`
                " . $langRows . ")
                VALUES (" . $sqlPlaceHolder . ")",
                $sqlValues
            );
        }

        $langRowsWithPrefix = str_replace(', ', ', c.', $langRows);

        $this->addSql("INSERT IGNORE INTO `oxcontents` (`OXID`, `OXLOADID`, `OXSHOPID`
                " . $langRows . ")
        SELECT md5(CONCAT(OXLOADID, s.OXID)), OXLOADID, s.OXID " .
            $langRowsWithPrefix .
            " FROM oxcontents c, oxshops s
        WHERE OXLOADID IN ('oscunzersepamandatetext', 'oscunzersepamandateconfirmation') AND c.OXSHOPID=1");
    }

    /**
     * change User Table
     */
    protected function changeUserTable(Schema $schema): void
    {
        //adding new column 'customerid' in oxuser table
        $oxuser = $schema->getTable('oxuser');
        if (!$oxuser->hasColumn('CUSTOMERID')) {
            $oxuser->addColumn('CUSTOMERID', Types::STRING, ['default' => "", 'comment' => 'unzer customerid']);
        }
    }

    /**
     * create DeliverySets
     */
    protected function createDeliverySets(): void
    {
        foreach ($this->paymentDefinitions as $paymentId => $paymentDefinitions) {
            $oxid = md5($paymentId . "oxidstandard.oxdelset");
            $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            select '" . $oxid . "', '" . $paymentId . "', 'oxidstandard', 'oxdelset'
                            from oxdeliveryset where oxid = 'oxidstandard' ");
        }
    }

    /**
     * create DeliverySets
     */
    protected function createRdfa(): void
    {
        foreach ($this->rdfaDefinitions as $oxid => $aRDF) {
            $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [$oxid, $aRDF['oxpaymentid'], $aRDF['oxobjectid'], 'rdfapayment']);
        }
    }

    /**
     * get the language-IDs
     */
    protected function getLanguageIds(): array
    {
        if (!count($this->languageIds)) {
            $facts = new Facts();
            $configFile = new ConfigFile($facts->getSourcePath() . '/config.inc.php');
            $configKey = is_null($configFile->getVar('sConfigKey')) ?
                Config::DEFAULT_CONFIG_KEY :
                $configFile->getVar('sConfigKey');

            if (
                $results = $this->connection->fetchAllAssociative(
                    'SELECT DECODE(OXVARVALUE, ?) as confValue FROM `oxconfig` WHERE `OXVARNAME` = ?',
                    [$configKey, 'aLanguages']
                )
            ) {
                $rawLanguageIds = unserialize($results[0]['confValue']);
            } else {
                // fallback OXID-Standard
                $rawLanguageIds = $this->defaultLanguageAbbr;
            }
            foreach ($rawLanguageIds as $langAbbr => $langName) {
                $this->languageIds[] = $langAbbr;
            }
        }
        return $this->languageIds;
    }

    /**
     * get the ISO-Codes of active Countries
     */
    protected function getIsoCountries(): array
    {
        if (!$this->isoCountries) {
            $sSql = "SELECT OXID, OXISOALPHA2 FROM oxcountry";
            $this->isoCountries = [];

            foreach ($this->connection->fetchAllAssociative($sSql) as $row) {
                $this->isoCountries [$row['OXISOALPHA2']] = $row['OXID'];
            }
        }

        return $this->isoCountries;
    }
}
