<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Core;

final class UnzerDefinitions
{
    public const ALIPAY_UNZER_PAYMENT_ID = 'oscunzer_alipay';
    public const BANCONTACT_UNZER_PAYMENT_ID = 'oscunzer_bancontact';
    public const CARD_UNZER_PAYMENT_ID = 'oscunzer_card';
    public const EPS_UNZER_PAYMENT_ID = 'oscunzer_eps';
    public const GIROPAY_UNZER_PAYMENT_ID = 'oscunzer_giropay';
    public const IDEAL_UNZER_PAYMENT_ID = 'oscunzer_ideal';
    public const INSTALLMENT_UNZER_PAYMENT_ID = 'oscunzer_installment';
    public const INVOICE_UNZER_PAYMENT_ID = 'oscunzer_invoice';
    public const INVOICE_SECURED_UNZER_PAYMENT_ID = 'oscunzer_invoice-secured';
    public const PAYPAL_UNZER_PAYMENT_ID = 'oscunzer_paypal';
    public const PIS_UNZER_PAYMENT_ID = 'oscunzer_pis';
    public const PREPAYMENT_UNZER_PAYMENT_ID = 'oscunzer_prepayment';
    public const PRZELEWY24_UNZER_PAYMENT_ID = 'oscunzer_przelewy24';
    public const SEPA_UNZER_PAYMENT_ID = 'oscunzer_sepa';
    public const SEPA_SECURED_UNZER_PAYMENT_ID = 'oscunzer_sepa-secured';
    public const SOFORT_UNZER_PAYMENT_ID = 'oscunzer_sofort';
    public const WECHATPAY_UNZER_PAYMENT_ID = 'oscunzer_wechatpay';
    public const APPLEPAY_UNZER_PAYMENT_ID = 'oscunzer_applepay';

    private const PAYMENT_CONSTRAINTS = [
        'oxfromamount' => 0,
        'oxtoamount' => 10000,
        'oxaddsumtype' => 'abs'
    ];

    private const UNZER_DEFINTIONS = [

        //Alipay is China’s leading third-party mobile and online payment solution.
        self::ALIPAY_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Alipay',
                    'longdesc' => 'Alipay ist Chinas führende Zahlungs- und Lifestyleplattform. Sie wurde
                        2004 von der Alibaba Group gegründet. Inzwischen zählt Alipay 870 Millionen Nutzer.
                        80 Prozent Marktanteil im Mobile Payment- und mehr als 50 Prozent im Online-Bereich
                        machen AliPay unverzichtbar für Händler, die nach China verkaufen. Auch die vielen
                        chinesischen Touristen bezahlen im Ausland gerne mit ihrer heimischen Zahlungsmethode.
                        Das wichtigste Produkt ist das Alipay Wallet. Mit der zugehörigen App können Käufer
                        Transaktionen direkt mit dem Smartphone vornehmen.'
                ],
                'en' => [
                    'desc' => 'Alipay',
                    'longdesc' => 'Alipay is Chinas leading payment and lifestyle platform. It was founded
                        in 2004 by the Alibaba Group. Alipay now has 870 million users. 80 percent of the
                        market share in mobile payment and more than 50 percent in the online area make AliPay
                        indispensable for merchants who sell to China. The many Chinese tourists abroad also
                        like to pay with their domestic payment method. The most important product is the
                        Alipay wallet. With the associated app, buyers can carry out transactions directly
                        with their smartphone.'
                ]
            ],
            'countries' => [],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //ApplePay
        self::APPLEPAY_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'ApplePay',
                    'longdesc' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
                        Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus
                        et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec,
                        pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo,
                        fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a,
                        venenatis vitae, justo.'
                ],
                'en' => [
                    'desc' => 'ApplePay',
                    'longdesc' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
                        Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus
                        et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec,
                        pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo,
                        fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a,
                        venenatis vitae, justo.'
                ]
            ],
            'countries' => [],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Bancontact is a Belgian company that offers user-friendly solutions for easy everyday shopping experience.
        self::BANCONTACT_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Bancontact',
                    'longdesc' => 'Bancontact ist ein belgisches Unternehmen, das benutzerfreundliche Lösungen
                        für ein einfaches tägliches Einkaufserlebnis bietet.'
                ],
                'en' => [
                    'desc' => 'Bancontact',
                    'longdesc' => 'Bancontact is a Belgian company that offers user-friendly solutions for easy
                        everyday shopping experience.'
                ]
            ],
            'countries' => ['BE'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Credit cards and debit cards are the most common payment method in e-commerce.
        self::CARD_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Kreditkarte',
                    'longdesc' => 'Von Europa, über Nordamerika bis Asien: kartenbasierte Zahlungsmethoden sind
                        international verbreitet. In vielen Teilen der Welt haben sie schon längst das Bargeld
                        abgelöst. Mit Unzer können Sie einfach alle wichtigen Anbieter akzeptieren.'
                ],
                'en' => [
                    'desc' => 'Credit Card',
                    'longdesc' => 'From Europe to North America to Asia: card-based payment methods are widely
                        used around the world. In many parts of the world they have long since replaced cash.
                        With Unzer you can easily accept all major providers.'
                ]
            ],
            'countries' => [],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Electronic Payment Standard (EPS) is an online payment system used in Austria.
        self::EPS_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'EPS',
                    'longdesc' => 'Electronic Payment Standard – oder kurz eps – ist ein österreichisches
                        Direktüberweisung-Verfahren. Es wurde von den größten Banken des Landes gemeinsam
                        mit der Regierung konzipiert. Ähnlich wie mit dem deutschen Gegenstück Giropay
                        können Käufer mit eps sicher und ohne Übermittlung von sensiblen Daten zahlen.
                        Händlern bietet Electronic Payment Standard vor allem Schutz vor Zahlungsausfällen.'
                ],
                'en' => [
                    'desc' => 'EPS',
                    'longdesc' => 'Electronic Payment Standard - or eps for short - is an Austrian direct
                        transfer procedure. It was designed by the countrys largest banks together with the
                        government. Similar to the German counterpart Giropay, buyers can use eps to pay securely
                        and without transmitting sensitive data. Electronic Payment Standard offers merchants
                        above all protection against payment defaults.'
                ]
            ],
            'countries' => ['AT'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Giropay is an online payment method used in Germany.
        self::GIROPAY_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Giropay',
                    'longdesc' => 'Giropay ist besonders in Deutschland stark verbreitet. Das Online-Verfahren
                        wurde von der deutschen Kreditwirtschaft speziell für den E-Commerce konzipiert und
                        optimiert. Käufer können damit sicher per Vorkasse bezahlen. Dabei werden keine sensiblen
                        Daten an den Händler übermittelt.'
                ],
                'en' => [
                    'desc' => 'Giropay',
                    'longdesc' => 'Giropay is particularly widespread in Germany. The online process was specially
                        designed and optimized by the German banking industry for e-commerce. Buyers can use it
                        to pay securely in advance. No sensitive data is transmitted to the dealer.'
                ]
            ],
            'countries' => ['DE'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //iDEAL is the most popular method for online payments in the Netherlands.
        self::IDEAL_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'iDEAL',
                    'longdesc' => 'iDEAL ist die beliebteste Zahlungsmethode im niederländischen E-Commerce
                        und nahtlos in das Online-Banking fast aller großen Banken des Landes integriert.
                        So können die Käufer mit ihren vertrauten Zugangsdaten bezahlen. Dieser komfortable
                        Checkout-Prozess steigert Beliebtheit und Conversions. Für fast jede zweite Online-
                        Transaktion in den Niederlanden wird iDEAL verwendet.'
                ],
                'en' => [
                    'desc' => 'iDEAL',
                    'longdesc' => 'iDEAL is the most popular payment method in Dutch e-commerce and is seamlessly
                        integrated into the online banking of almost all major banks in the country. Buyers can
                        pay with their familiar access data. This convenient checkout process increases popularity
                        and conversions. IDEAL is used for almost every second online transaction in the Netherlands.'
                ]
            ],
            'countries' => ['NL'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Unzer Installment lets your customers pay in monthly payments.
        self::INSTALLMENT_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Ratenzahlung',
                    'longdesc' => 'Ratenzahlung mit Unzer'
                ],
                'en' => [
                    'desc' => 'Installment',
                    'longdesc' => 'Unzer installment'
                ]
            ],
            'countries' => ['DE', 'AT'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Unzer Invoice lets you issue an invoice and then collect the payment.
        self::INVOICE_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Rechnung',
                    'longdesc' => 'Rechnung, ausgestellt von Unzer'
                ],
                'en' => [
                    'desc' => 'Invoice',
                    'longdesc' => 'Invoice, issued by Unzer'
                ]
            ],
            'countries' => ['BE', 'DE', 'EE', 'FI', 'FR', 'GR', 'IE', 'IT', 'LV',
                'LT', 'LU', 'MT', 'NL', 'PT', 'SK', 'SI', 'ES', 'CY', 'AT'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Unzer Invoice Secured lets you issue an invoice and then collect the payment, your payment is secured.
        self::INVOICE_SECURED_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Rechnung Secured',
                    'longdesc' => 'Rechnung, ausgestellt von Unzer (gesicherter Kanal)'
                ],
                'en' => [
                    'desc' => 'Invoice Secured',
                    'longdesc' => 'Invoice, issued by Unzer (secure channel)'
                ]
            ],
            'countries' => ['DE', 'AT'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //PayPal is one of the world’s most popular online payment systems.
        self::PAYPAL_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'PayPal',
                    'longdesc' => 'Paypal kann zum Zahlen per Banküberweisung, Kreditkarte oder Lastschrift
                        verwendet werden. 246 Millionen Käufer nutzen das e-Wallet weltweit – in über 200 Märkten
                        und 25 Währungen. Allein in Deutschland erreichen Sie etwa 25 Millionen PayPal-Kunden.
                        Anschließend bezahlen Käufer damit vor allem in Online-Shops. Aber auch im stationären
                        Handel nutzen sie die Google Pay App auf ihrem Smartphone. Da während der Transaktion
                        keine Bankdaten übertragen werden, gilt die die Zahlung als sicher.'
                ],
                'en' => [
                    'desc' => 'PayPal',
                    'longdesc' => 'Paypal can be used to pay by bank transfer, credit card, or direct debit.
                        246 million buyers use the e-wallet worldwide - in over 200 markets and 25 currencies.
                        In Germany alone you can reach around 25 million PayPal customers. Buyers then use it
                        to pay in online shops in particular. But they also use the Google Pay app on their
                        smartphone in brick-and-mortar retail. Since no bank details are transferred during
                        the transaction, the payment is considered secure.'
                ]
            ],
            'countries' => [],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Unzer Prepayment lets you collect the payment before sending the goods to your customer.
        self::PREPAYMENT_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Vorkasse',
                    'longdesc' => 'Bei der Vorauskasse oder Vorkasse erklärt schon der Name, wie die
                        Zahlungsmethode funktioniert: Online-Käufer überweisen das Geld für Ihre Bestellung
                        im Voraus. Erst wenn der Betrag eingegangen ist, verschickt der Händler die Ware.'
                ],
                'en' => [
                    'desc' => 'Prepayment',
                    'longdesc' => 'In the case of prepayment or prepayment, the name already explains how
                        the payment method works: online buyers transfer the money for their order in advance.
                        Only when the amount has been received does the dealer send the goods.'
                ]
            ],
            'countries' => ['BE', 'DE', 'EE', 'FI', 'FR', 'GR', 'IE', 'IT', 'LV',
                'LT', 'LU', 'MT', 'NL', 'PT', 'SK', 'SI', 'ES', 'CY', 'AT'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Przelewy24 is an online payment method used in Poland.
        self::PRZELEWY24_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Przelewy24',
                    'longdesc' => 'Przelewy24 ist die beliebteste Online-Zahlungsmethode Polens. Sie ermöglicht
                        Käufern nicht nur Zahlungen vom eigenen Bankkonto oder der Kreditkarte. Sondern unterstützt
                        auch alternative Zahlungsmethoden wie SMS. Voraussetzung für die Nutzung ist ein Konto
                        bei einer der über 150 polnischen Banken, die Przelewy24 unterstützt.'
                ],
                'en' => [
                    'desc' => 'Przelewy24',
                    'longdesc' => 'Przelewy24 is the most popular online payment method in Poland. It not only
                        enables buyers to make payments from their own bank account or credit card. It also supports
                        alternative payment methods such as SMS. The requirement for use is an account with one of
                        the more than 150 Polish banks that Przelewy24 supports.'
                ]
            ],
            'countries' => ['PL'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Unzer Direct Debit lets you accept payments in euro.
        self::SEPA_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'SEPA-Lastschrift',
                    'longdesc' => 'Sie erteilen ein SEPA-Lastschriftmandat'
                ],
                'en' => [
                    'desc' => 'SEPA Direct Debit',
                    'longdesc' => 'You issue a SEPA direct debit mandate'
                ]
            ],
            'countries' => ['BE', 'DE', 'EE', 'FI', 'FR', 'GR', 'IE', 'IT', 'LV',
                'LT', 'LU', 'MT', 'NL', 'PT', 'SK', 'SI', 'ES', 'CY', 'AT'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Unzer Direct Debit Secured lets you accept payments in euro and secures your money.
        self::SEPA_SECURED_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'SEPA-Lastschrift Secured',
                    'longdesc' => 'Sie erteilen ein SEPA-Lastschriftmandat'
                ],
                'en' => [
                    'desc' => 'SEPA Direct Debit Secured',
                    'longdesc' => 'You issue a SEPA direct debit mandate'
                ]
            ],
            'countries' => ['DE'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Sofort is an online payment method used in select European countries.
        self::SOFORT_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Sofort',
                    'longdesc' => 'Sofortüberweisung (gesicherter Kanal)'
                ],
                'en' => [
                    'desc' => 'Sofort',
                    'longdesc' => 'Instant bank transfer (secure channel)'
                ]
            ],
            'countries' => ['SE', 'NO', 'FI', 'DK', 'DE', 'NL', 'BE', 'CH', 'FR', 'IT',
                'PL', 'ES', 'PT', 'GB', 'HU', 'CZ', 'AU', 'SK', 'US'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Unzer Bank Transfer lets your customers pay directly from their bank account.
        self::PIS_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Bank transfer',
                    'longdesc' => 'Unzer Bank Transfer ist unser Direktüberweisungs- oder auch
                        Zahlungsauslösedienst. Mit ihm können Käufer im Checkout-Prozess komfortabel
                        eine Überweisung beauftragen. Dazu geben sie die Zugangsdaten ihres Online-Bankings
                        ein und authentifizieren sich zusätzlich mit einer TAN. Unzerbank transfer prüft in
                        Echtzeit, ob das Konto gedeckt ist. Der Betrag wird direkt abgebucht.'
                ],
                'en' => [
                    'desc' => 'Bank Transfer',
                    'longdesc' => 'Unzer Bank Transfer is our direct transfer or payment initiation service.
                        With it, buyers can conveniently order a transfer in the checkout process. To do this,
                        they enter the access data for their online banking and also authenticate themselves
                        with a TAN. Unzer bank transfer checks in real time whether the account is sufficient.
                        The amount will be debited directly.'
                ]
            ],
            'countries' => ['DE', 'AT'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //WeChat Pay is one of the biggest and fastest-growing mobile payment solutions in China.
        self::WECHATPAY_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'WeChat Pay',
                    'longdesc' => 'WeChat Pay ist ein von Tencent betriebenes Bezahlsystem aus China.
                        Bei WeChat handelte es sich ursprünglich um eine Kommunikationsapp, analog zu WhatsApp.
                        Im Laufe der Zeit wurde die App um immer mehr Tools erweitert – im Jahr 2015 auch um ein
                        Bezahlsystem vergleichbar mit Google Pay oder Apple Pay. Dank der sozialen Plattform WeChat
                        verfügt WeChat Pay über eine riesige Nutzerbasis von über einer Milliarde Chat-Nutzern.
                        Von diesen vertrauen bereits etwa 600 Millionen WeChat Pay. '
                ],
                'en' => [
                    'desc' => 'WeChat Pay',
                    'longdesc' => 'WeChat Pay is a payment system from China operated by Tencent. WeChat was
                        originally a communication app, analogous to WhatsApp. Over time, the app has been expanded
                        to include more and more tools - in 2015 also a payment system comparable to Google Pay or
                        Apple Pay. Thanks to the WeChat social platform, WeChat Pay has a huge user base of over
                        a billion chat users. Of these, around 600 million already trust WeChat Pay.'
                ]
            ],
            'countries' => [],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ]
    ];

    private const PAYPAL_STATIC_CONTENTS = [
        'oscunzersepamandatetext' =>
        [
            'oxloadid' => 'oscunzersepamandatetext',
            'oxactive' => 1,
            'oxtitle_de' => 'SEPA Lastschrift-Mandat (Bankeinzug)',
            'oxtitle_en' => 'SEPA direct debit mandate (direct debit)',
            'oxcontent_de' => '<p>Ich ermächtige [{$oxcmp_shop->oxshops__oxname->value}], Zahlungen von
                meinem Konto mittels SEPA Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an,
                die von [{$oxcmp_shop->oxshops__oxname->value}] auf mein Konto gezogenen SEPA Lastschriften
                einzulösen.</p>
                <p>Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung
                des belasteten Betrags verlangen. Es gelten dabei die mit meinem Kreditinstitut vereinbarten
                Bedingungen.</p>
                <p>Für den Fall der Nichteinlösung der Lastschriften oder des Widerspruchs gegen die Lastschriften
                weise ich meine Bank unwiderruflich an, [{$oxcmp_shop->oxshops__oxname->value}]oder Dritten auf
                Anforderung meinen Namen, Adresse und Geburtsdatum vollständig mitzuteilen.</p>',
            'oxcontent_en' => '<p>By signing this mandate form, you authorise [{$oxcmp_shop->oxshops__oxname->value}]
                to send instructions to your bank to debit your account and your bank to debit your account in
                accordance with the instructions from [{$oxcmp_shop->oxshops__oxname->value}].</p>
                <p>Note: As part of your rights, you are entitled to a refund from your bank under the terms and
                conditions of your agreement with your bank.</p>
                <p>A refund must be claimed within 8 weeks starting from the date on which your account was debited.
                Your rights regarding this SEPA mandate are explained in a statement that you can obtain from your
                bank.<br><br>In case of refusal or rejection of direct debit payment I instruct my bank irrevocably
                to inform [{$oxcmp_shop->oxshops__oxname->value}] or any third party upon request about my name,
                address and date of birth.</p>'
        ],
        'oscunzersepamandateconfirmation' =>
        [
            'oxloadid' => 'oscunzersepamandateconfirmation',
            'oxactive' => 1,
            'oxtitle_de' => 'Unzer Sepa',
            'oxtitle_en' => 'Unzer Sepa Text',
            'oxcontent_de' => '[{oxifcontent ident="oscunzersepamandatetext" object="oCont"}]
                <a rel="nofollow" href="[{ $oCont->getLink() }]"
                onclick="window.open(\'[{ $oCont->getLink()|oxaddparams:\'plain=1\'}]\', \'agb_popup\',
                \'resizable=yes,status=no,scrollbars=yes,menubar=no,width=620,height=400\');return false;"
                class="fontunderline">Sepa-Mandat</a> bestätigen.
                [{/oxifcontent}]',
            'oxcontent_en' => '[{oxifcontent ident="oscunzersepamandatetext" object="oCont"}]
                Confirm <a rel="nofollow" href="[{ $oCont->getLink() }]"
                onclick="window.open(\'[{ $oCont->getLink()|oxaddparams:"plain=1"}]\', \'sepa_popup\',
                \'resizable=yes,status=no,scrollbars=yes,menubar=no,width=620,height=400\');return false;"
                class="fontunderline">Sepa-Mandate</a>.
                [{/oxifcontent}]'
        ],
        'oscunzerinstallmentconfirmation' =>
        [
            'oxloadid' => 'oscunzerinstallmentconfirmation',
            'oxactive' => 1,
            'oxtitle_de' => 'Unzer Ratenkauf-Bestätigung',
            'oxtitle_en' => 'TR: Unzer Installment Text',
            'oxcontent_de' => '<p>Bitte lesen Sie den Inhalt des Vertrags (siehe PDF) und bestätigen
                Sie die Konditionen.</p><p></p>',
            'oxcontent_en' => 'TR: <p>Bitte lesen Sie den Inhalt des Vertrags (siehe PDF) und bestätigen
                Sie die Konditionen.</p><p></p>'
        ]
    ];

    /** @var array[] */
    private const UNZER_RDFA_DEFINITIONS = [
        'oscunzer_card_mastercard' => [
            'oxpaymentid' => self::CARD_UNZER_PAYMENT_ID,
            'oxrdfaid' => 'MasterCard'
        ],
        'oscunzer_card_visa' => [
            'oxpaymentid' => self::CARD_UNZER_PAYMENT_ID,
            'oxrdfaid' => 'VISA'
        ],
        'oscunzer_card_americanexpress' => [
            'oxpaymentid' => self::CARD_UNZER_PAYMENT_ID,
            'oxrdfaid' => 'AmericanExpress'
        ],
        'oscunzer_card_dinersclub' => [
            'oxpaymentid' => self::CARD_UNZER_PAYMENT_ID,
            'oxrdfaid' => 'DinersClub'
        ],
        'oscunzer_card_jcb' => [
            'oxpaymentid' => self::CARD_UNZER_PAYMENT_ID,
            'oxrdfaid' => 'JCB'
        ],
        self::PREPAYMENT_UNZER_PAYMENT_ID => [
            'oxpaymentid' => self::PREPAYMENT_UNZER_PAYMENT_ID,
            'oxrdfaid' => 'ByBankTransferInAdvance'
        ],
        self::PIS_UNZER_PAYMENT_ID => [
            'oxpaymentid' => self::PIS_UNZER_PAYMENT_ID,
            'oxrdfaid' => 'ByBankTransferInAdvance'
        ],
        self::INVOICE_UNZER_PAYMENT_ID => [
            'oxpaymentid' => self::INVOICE_UNZER_PAYMENT_ID,
            'oxrdfaid' => 'ByInvoice'
        ],
        self::INVOICE_SECURED_UNZER_PAYMENT_ID => [
            'oxpaymentid' => self::INVOICE_SECURED_UNZER_PAYMENT_ID,
            'oxrdfaid' => 'ByInvoice'
        ],
        self::SEPA_UNZER_PAYMENT_ID => [
            'oxpaymentid' => self::SEPA_UNZER_PAYMENT_ID,
            'oxrdfaid' => 'DirectDebit'
        ],
        self::SEPA_SECURED_UNZER_PAYMENT_ID => [
            'oxpaymentid' => self::SEPA_SECURED_UNZER_PAYMENT_ID,
            'oxrdfaid' => 'DirectDebit'
        ],
        self::PAYPAL_UNZER_PAYMENT_ID => [
            'oxpaymentid' => self::PAYPAL_UNZER_PAYMENT_ID,
            'oxrdfaid' => 'PayPal'
        ],
    ];

    public static function getUnzerDefinitions()
    {
        return self::UNZER_DEFINTIONS;
    }

    public static function getUnzerRdfaDefinitions()
    {
        return self::UNZER_RDFA_DEFINITIONS;
    }

    public static function getUnzerStaticContents()
    {
        return self::PAYPAL_STATIC_CONTENTS;
    }
}
