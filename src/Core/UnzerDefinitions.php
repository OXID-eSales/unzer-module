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
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/1fb015ed91/alipay.svg"
                        title="Alipay" style="float: left;margin-right: 10px;" />'
                ],
                'en' => [
                    'desc' => 'Alipay',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/1fb015ed91/alipay.svg"
                        title="Alipay" style="float: left;margin-right: 10px;" />'
                ]
            ],
            'active' => true,
            'countries' => [],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //ApplePay
        self::APPLEPAY_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'ApplePay',
                    'longdesc' => ''
                ],
                'en' => [
                    'desc' => 'ApplePay',
                    'longdesc' => ''
                ]
            ],
            'active' => true,
            'countries' => [],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Bancontact is a Belgian company that offers user-friendly solutions for easy everyday shopping experience.
        self::BANCONTACT_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Bancontact',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/7f8c94a7dd/bancontact.svg"
                        title="Bancontact" style="float: left;margin-right: 10px;" />'
                ],
                'en' => [
                    'desc' => 'Bancontact',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/7f8c94a7dd/bancontact.svg"
                        title="Bancontact" style="float: left;margin-right: 10px;" />'
                ]
            ],
            'active' => true,
            'countries' => ['BE'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Credit cards and debit cards are the most common payment method in e-commerce.
        self::CARD_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Kartenzahlung',
                    'longdesc' => '',
                    'longdesc_beta' => 'Kartenzahlungen sind auf der ganzen Welt beliebt. Wenn Sie Ihren Warenkorb
                        mit einer Kredit- oder Debitkarte bezahlen möchten, wählen Sie diese Zahlungsmethode.'
                ],
                'en' => [
                    'desc' => 'Card',
                    'longdesc' => '',
                    'longdesc_beta' => 'Card-based payment methods are popular across the globe. If you want to pay
                        your basket with a credit-or debit card, select this payment method.'
                ]
            ],
            'active' => true,
            'countries' => [],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Electronic Payment Standard (EPS) is an online payment system used in Austria.
        self::EPS_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'EPS',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/02e1135598/eps_uberweisung.svg"
                        title="eps" style="float: left;margin-right: 10px;" />
                        Um mit eps zu bezahlen, benötigen Sie ein Online-Banking-Konto bei einer der teilnehmenden
                        Banken. Wählen Sie an der Kasse Ihre Bank aus und loggen Sie sich in den privaten
                        Online-Banking-Bereich ein. Dort überprüfen Sie die von Ihnen bereits eingegebenen
                        Zahlungsdaten. Wenn sie korrekt sind, autorisieren Sie die Zahlung und schließen den Kauf ab.'
                ],
                'en' => [
                    'desc' => 'EPS',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/02e1135598/eps_uberweisung.svg"
                        title="eps" style="float: left;margin-right: 10px;" />
                        To pay with eps, you need an online banking account at one of the participating banks.
                        At checkout, select your bank and log in to the private online banking area. There you check the
                        payment details you have already entered. If they are correct, authorize the payment
                        and complete the purchase.'
                ]
            ],
            'active' => true,
            'countries' => ['AT'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Giropay is an online payment method used in Germany.
        self::GIROPAY_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Giropay',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/3a1b3dafb0/giro_pay.svg"
                        title="Giropay" style="float: left;margin-right: 10px;" />
                        Kein Eintippen von IBAN, BIC oder Zahlungsgrund: giropay übernimmt die ganze Arbeit
                        für Sie. Sie müssen sich nicht einmal registrieren. Sie benötigen lediglich Ihre
                        Zugangsdaten für das Online-Banking. Den Rest erledigt giropay für Sie.'
                ],
                'en' => [
                    'desc' => 'Giropay',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/3a1b3dafb0/giro_pay.svg"
                        title="Giropay" style="float: left;margin-right: 10px;" />
                        No typing in IBAN, BIC or reason for payment: giropay does all the hard work for you. You don\'t
                        even have to register. You only need your access data for online banking.
                        The rest is done for you by giropay.'
                ]
            ],
            'active' => true,
            'countries' => ['DE'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //iDEAL is the most popular method for online payments in the Netherlands.
        self::IDEAL_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'iDEAL',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/efc1cbe641/ideal.svg"
                        title="iDEAL" style="float: left;margin-right: 10px;" />'
                ],
                'en' => [
                    'desc' => 'iDEAL',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/efc1cbe641/ideal.svg"
                        title="iDEAL" style="float: left;margin-right: 10px;" />'
                ]
            ],
            'active' => true,
            'countries' => ['NL'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Unzer Installment lets your customers pay in monthly payments.
        self::INSTALLMENT_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Ratenzahlung',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/59fb020f95/unzer_instalment.svg"
                        title="Ratenzahlung" style="float: left;margin-right: 10px;" />
                        Viele Kunden ziehen es vor, größere Anschaffungen von mehreren hundert Euro
                        in kleineren Raten zu bezahlen. Wählen Sie diese Methode für eine einfache
                        und bequeme Ratenzahlung.'
                ],
                'en' => [
                    'desc' => 'Installment',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/59fb020f95/unzer_instalment.svg "
                        title="Installment" style="float: left;margin-right: 10px;" />
                        Many customers prefer to pay for larger purchases of several hundred euros in
                        smaller installments. Choose this method for comfortable payment in installments.'
                ]
            ],
            'active' => true,
            'countries' => ['DE', 'AT'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Unzer Invoice lets you issue an invoice and then collect the payment.
        self::INVOICE_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Unzer Rechnung (Paylater)',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/e5b83d6129/unzer_invoice.svg"
                        title="Kauf auf Rechnung" style="float: left;margin-right: 10px;" />
                        Bei dieser Methode zahlen Sie per Kauf auf Rechnung'
                ],
                'en' => [
                    'desc' => 'Unzer Invoice (Paylater)',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/e5b83d6129/unzer_invoice.svg"
                        title="Invoice" style="float: left;margin-right: 10px;" />'
                ]
            ],
            'active' => true,
            'countries' => ['BE', 'DE', 'EE', 'FI', 'FR', 'GR', 'IE', 'IT', 'LV',
                'LT', 'LU', 'MT', 'NL', 'PT', 'SK', 'SI', 'ES', 'CY', 'AT'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //PayPal is one of the world’s most popular online payment systems.
        self::PAYPAL_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'PayPal',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/46364241f0/paypal.svg"
                        title="PayPal" style="float: left;margin-right: 10px;" />
                        PayPal ist der Online-Zahlungsservice, mit dem Sie in Online-Shops sicher,
                        einfach und schnell bezahlen können - für Sie völlig kostenlos.'
                ],
                'en' => [
                    'desc' => 'PayPal',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/46364241f0/paypal.svg"
                        title="PayPal" style="float: left;margin-right: 10px;" />
                        PayPal is the online payment service that allows you to pay in online stores securely,
                        easily and quickly - absolutely free of charge for you.'
                ]
            ],
            'active' => true,
            'countries' => [],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Unzer Prepayment lets you collect the payment before sending the goods to your customer.
        self::PREPAYMENT_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Vorkasse',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/ed7c39ce12/unzer_prepayment.svg"
                        title="Vorkasse" style="float: left;margin-right: 10px;" />
                        Bei Vorkasse überweisen Sie das Geld für Ihre Bestellung im Voraus. Erst wenn der Betrag
                        eingegangen ist, wird die Ware an Ihre Lieferadresse verschickt.'
                ],
                'en' => [
                    'desc' => 'Prepayment',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/ed7c39ce12/unzer_prepayment.svg"
                        title="Prepayment" style="float: left;margin-right: 10px;" />
                        With prepayment you transfer the money for your order in advance. Only when the amount
                        is received, the goods will be shipped to your delivery address.'
                ]
            ],
            'active' => true,
            'countries' => ['BE', 'DE', 'EE', 'FI', 'FR', 'GR', 'IE', 'IT', 'LV',
                'LT', 'LU', 'MT', 'NL', 'PT', 'SK', 'SI', 'ES', 'CY', 'AT'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Przelewy24 is an online payment method used in Poland.
        self::PRZELEWY24_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Przelewy24',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/def6433104/przelewy24.svg"
                        title="Przelewy24" style="float: left;margin-right: 10px;" />'
                ],
                'en' => [
                    'desc' => 'Przelewy24',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/def6433104/przelewy24.svg"
                        title="Przelewy24" style="float: left;margin-right: 10px;" />'
                ]
            ],
            'active' => true,
            'countries' => ['PL'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Unzer Direct Debit lets you accept payments in euro.
        self::SEPA_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'SEPA-Lastschrift',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/c3ed559dcc/sepa_lastschrift.svg"
                        title="SEPA-Lastschrift" style="float: left;margin-right: 10px;" />'
                ],
                'en' => [
                    'desc' => 'SEPA Direct Debit',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/c3ed559dcc/sepa_lastschrift.svg"
                        title="SEPA Direct Debit" style="float: left;margin-right: 10px;" />'
                ]
            ],
            'active' => true,
            'countries' => ['BE', 'DE', 'EE', 'FI', 'FR', 'GR', 'IE', 'IT', 'LV',
                'LT', 'LU', 'MT', 'NL', 'PT', 'SK', 'SI', 'ES', 'CY', 'AT'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Unzer Direct Debit Secured lets you accept payments in euro and secures your money.
        self::SEPA_SECURED_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'SEPA Lastschrift (abgesichert durch Unzer)',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/a76f7ea77f/unzer_directdebit.svg"
                        title="Przelewy24" style="float: left;margin-right: 10px;" />'
                ],
                'en' => [
                    'desc' => 'SEPA Direct Debit with Unzer',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/a76f7ea77f/unzer_directdebit.svg"
                        title="Przelewy24" style="float: left;margin-right: 10px;" />'
                ]
            ],
            'active' => true,
            'countries' => ['DE'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Sofort is an online payment method used in select European countries.
        self::SOFORT_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Sofort',
                    'longdesc' => ''
                ],
                'en' => [
                    'desc' => 'Sofort',
                    'longdesc' => ''
                ]
            ],
            'active' => true,
            'countries' => ['SE', 'NO', 'FI', 'DK', 'DE', 'NL', 'BE', 'CH', 'FR', 'IT',
                'PL', 'ES', 'PT', 'GB', 'HU', 'CZ', 'AU', 'SK', 'US'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //Unzer Bank Transfer lets your customers pay directly from their bank account.
        self::PIS_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'Bank transfer',
                    'longdesc' => ''
                ],
                'en' => [
                    'desc' => 'Bank Transfer',
                    'longdesc' => ''
                ]
            ],
            'active' => false,
            'countries' => ['DE', 'AT'],
            'constraints' => self::PAYMENT_CONSTRAINTS
        ],

        //WeChat Pay is one of the biggest and fastest-growing mobile payment solutions in China.
        self::WECHATPAY_UNZER_PAYMENT_ID => [
            'descriptions' => [
                'de' => [
                    'desc' => 'WeChat Pay',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/35bad89449/wechat_pay.svg"
                        title="WeChat Pay" style="float: left;margin-right: 10px;" />'
                ],
                'en' => [
                    'desc' => 'WeChat Pay',
                    'longdesc' => '',
                    'longdesc_beta' => '<img src="https://a.storyblok.com/f/91629/x/35bad89449/wechat_pay.svg"
                        title="WeChat Pay" style="float: left;margin-right: 10px;" />'
                ]
            ],
            'active' => true,
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
        /*
         * deactivated for first release because payment is deprecated on unzer-side, new API will coming soon (2022-07-26)
         *
         * self::INVOICE_SECURED_UNZER_PAYMENT_ID => [
         *   'oxpaymentid' => self::INVOICE_SECURED_UNZER_PAYMENT_ID,
         *   'oxrdfaid' => 'ByInvoice'
         * ],
         */
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

    /** @var array[] */
    private const UNZER_COMMERCIAL_SECTORS = [
        'OTHER',
        'WHOLESALE_TRADE_EXCEPT_VEHICLE_TRADE',
        'RETAIL_TRADE_EXCEPT_VEHICLE_TRADE',
        'WATER_TRANSPORT',
        'AIR_TRANSPORT',
        'WAREHOUSING_AND_SUPPORT_ACTIVITES_FOR_TRANSPORTATION',
        'POSTAL_AND_COURIER_ACTIVITIES',
        'ACCOMMODATION',
        'FOOD_AND_BEVERAGE_SERVICE_ACTIVITIES',
        'MOTION_PICTURE_PRODUCTION_AND_SIMILAR_ACTIVITIES',
        'TELECOMMUNICATIONS',
        'COMPUTER_PROGRAMMING_CONSULTANCY_AND_RELATED_ACTIVITIES',
        'INFORMATION_SERVICE_ACTIVITIES',
        'RENTAL_AND_LEASING_ACTIVITIES',
        'TRAVEL_AGENCY_AND_RELATED_ACTIVITIES',
        'SERVICES_TO_BUILDINGS_AND_LANDSCAPE_ACTIVITIES',
        'LIBRARIES_AND_SIMILAR_CULTURAL_ACTIVITIES',
        'SPORTS_ACTIVITIES_AND_AMUSEMENT_AND_RECREATION_ACTIVITIES',
        'OTHER_PERSONAL_SERVICE_ACTIVITIES',
        'NON_RESIDENTIAL_REAL_ESTATE_ACTIVITIES',
        'MANAGEMENT_CONSULTANCY_ACTIVITIES',
        'ELECTRICITY_GAS_AND_STEAM_SUPPLY',
        'WATER_COLLECTION_TREATMENT_AND_SUPPLY',
        'SEWERAGE',
        'MANUFACTURE_OF_FOOD_PRODUCTS',
        'MANUFACTURE_OF_BEVERAGES',
        'MANUFACTURE_OF_TEXTILES',
        'OTHERS_COMMERCIAL_SECTORS',
        'MANUFACTURE_OF_WEARING_APPAREL',
        'MANUFACTURE_OF_LEATHER_AND_RELATED_PRODUCTS',
        'MANUFACTURE_OF_PHARMACEUTICAL_PRODUCTS',
        'REPAIR_AND_INSTALLATION_OF_MACHINERY_AND_EQUIPMENT',
        'TRADE_AND_REPAIR_OF_MOTOR_VEHICLES',
        'PUBLISHING_ACTIVITIES',
        'REPAIR_OF_COMPUTERS_AND_GOODS',
        'PRINTING_AND_REPRODUCTION_OF_RECORDED_MEDIA',
        'MANUFACTURE_OF_FURNITURE',
        'OTHER_MANUFACTURING',
        'ADVERTISING_AND_MARKET_RESEARCH',
        'OTHER_PROFESSIONAL_SCIENTIFIC_AND_TECHNICAL_ACTIVITIES',
        'ARTS_ENTERTAINMENT_AND_RECREATION'
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

    public static function getUnzerCommercialSectors()
    {
        return self::UNZER_COMMERCIAL_SECTORS;
    }
}
