<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

$sLangName = 'Deutsch';

// -------------------------------
// RESOURCE IDENTIFIER = STRING
// -------------------------------
$aLang = [
    'charset' => 'UTF-8',

    // Invoice
    'OSCUNZER_BANK_DETAILS_AMOUNT' => 'Bitte überweisen sie den Betrag von %s %s auf folgendes Bankkonto:<br /><br />',
    'OSCUNZER_BANK_DETAILS_HOLDER' => 'Kontoinhaber: %s<br/>',
    'OSCUNZER_BANK_DETAILS_IBAN' => 'IBAN: %s<br/>',
    'OSCUNZER_BANK_DETAILS_BIC' => 'BIC: %s<br/><br/>',
    'OSCUNZER_BANK_DETAILS_DESCRIPTOR' => '<i>Bitte verwenden Sie diese Identifikationsnummer als Verwendungszweck: </i><br/>%s',

    // Invoice Secured B2B
    'OSCUNZER_INDUSTRY' => 'Wirtschaftszweig',
    'OSCUNZER_INDUSTRY_OTHER' => 'Sonstige',
    'OSCUNZER_INDUSTRY_WHOLESALE_TRADE_EXCEPT_VEHICLE_TRADE' => 'Großhandel mit Ausnahme von Fahrzeughandel',
    'OSCUNZER_INDUSTRY_RETAIL_TRADE_EXCEPT_VEHICLE_TRADE' => 'Einzelhandel mit Ausnahme von Fahrzeughandel',
    'OSCUNZER_INDUSTRY_WATER_TRANSPORT' => 'Wassertransport',
    'OSCUNZER_INDUSTRY_AIR_TRANSPORT' => 'Lufttransport',
    'OSCUNZER_INDUSTRY_WAREHOUSING_AND_SUPPORT_ACTIVITES_FOR_TRANSPORTATION' => 'Dienstleistungen im Bereich Einlagerung und Transportunterstützung',
    'OSCUNZER_INDUSTRY_POSTAL_AND_COURIER_ACTIVITIES' => 'Post- und Kurierdienstleistungen',
    'OSCUNZER_INDUSTRY_ACCOMMODATION' => 'Unterbringung',
    'OSCUNZER_INDUSTRY_FOOD_AND_BEVERAGE_SERVICE_ACTIVITIES' => 'Gastronomiedienstleistungen',
    'OSCUNZER_INDUSTRY_MOTION_PICTURE_PRODUCTION_AND_SIMILAR_ACTIVITIES' => 'Filmproduktion und ähnliche Tätigkeiten',
    'OSCUNZER_INDUSTRY_TELECOMMUNICATIONS' => 'Telekommunikation',
    'OSCUNZER_INDUSTRY_COMPUTER_PROGRAMMING_CONSULTANCY_AND_RELATED_ACTIVITIES' => 'Beratungstätigkeiten in der Informationstechnologie und ähnliche Dienstleistungen',
    'OSCUNZER_INDUSTRY_INFORMATION_SERVICE_ACTIVITIES' => 'Informationsdienstleistungen',
    'OSCUNZER_INDUSTRY_RENTAL_AND_LEASING_ACTIVITIES' => 'Vermietung und Verpachtung',
    'OSCUNZER_INDUSTRY_TRAVEL_AGENCY_AND_RELATED_ACTIVITIES' => 'Reisebüro und ähnliche Dienstleistungen',
    'OSCUNZER_INDUSTRY_SERVICES_TO_BUILDINGS_AND_LANDSCAPE_ACTIVITIES' => 'Gebäudebetreuung, Garten- und Landschaftsbau',
    'OSCUNZER_INDUSTRY_LIBRARIES_AND_SIMILAR_CULTURAL_ACTIVITIES' => 'Bibliotheken und andere kulturelle Dienstleistungen',
    'OSCUNZER_INDUSTRY_SPORTS_ACTIVITIES_AND_AMUSEMENT_AND_RECREATION_ACTIVITIES' => 'Dienstleistungen des Sports, der Unterhaltung und der Erholung',
    'OSCUNZER_INDUSTRY_OTHER_PERSONAL_SERVICE_ACTIVITIES' => 'Sonstige persönlich erbrachte Dienstleistungen',
    'OSCUNZER_INDUSTRY_NON_RESIDENTIAL_REAL_ESTATE_ACTIVITIES' => 'Immobilientätigkeiten mit Nicht-Wohngebäuden',
    'OSCUNZER_INDUSTRY_MANAGEMENT_CONSULTANCY_ACTIVITIES' => 'Unternehmensberatung',
    'OSCUNZER_INDUSTRY_ELECTRICITY_GAS_AND_STEAM_SUPPLY' => 'Energieversorgung',
    'OSCUNZER_INDUSTRY_WATER_COLLECTION_TREATMENT_AND_SUPPLY' => 'Wasserversorgung',
    'OSCUNZER_INDUSTRY_SEWERAGE' => 'Abwasserentsorgung',
    'OSCUNZER_INDUSTRY_MANUFACTURE_OF_FOOD_PRODUCTS' => 'Herstellung von Lebensmitteln',
    'OSCUNZER_INDUSTRY_MANUFACTURE_OF_BEVERAGES' => 'Herstellung von Getränken',
    'OSCUNZER_INDUSTRY_MANUFACTURE_OF_TEXTILES' => 'Herstellung von Textilien',
    'OSCUNZER_INDUSTRY_OTHERS_COMMERCIAL_SECTORS' => 'Sonstige Gewerbebereiche',
    'OSCUNZER_INDUSTRY_MANUFACTURE_OF_WEARING_APPAREL' => 'Herstellung von Bekleidung',
    'OSCUNZER_INDUSTRY_MANUFACTURE_OF_LEATHER_AND_RELATED_PRODUCTS' => 'Herstellung von Lederwaren',
    'OSCUNZER_INDUSTRY_MANUFACTURE_OF_PHARMACEUTICAL_PRODUCTS' => 'Herstellung pharmazeutischer Produkte',
    'OSCUNZER_INDUSTRY_REPAIR_AND_INSTALLATION_OF_MACHINERY_AND_EQUIPMENT' => 'Reparatur und Installation von Maschinen und Ausrüstungen',
    'OSCUNZER_INDUSTRY_TRADE_AND_REPAIR_OF_MOTOR_VEHICLES' => 'Handel und Reparatur motorisierter Fahrzeuge',
    'OSCUNZER_INDUSTRY_PUBLISHING_ACTIVITIES' => 'Verlegerische Tätigkeiten',
    'OSCUNZER_INDUSTRY_REPAIR_OF_COMPUTERS_AND_GOODS' => 'Reparatur von Datenverarbeitungsgeräten und Gebrauchsgütern',
    'OSCUNZER_INDUSTRY_PRINTING_AND_REPRODUCTION_OF_RECORDED_MEDIA' => 'Druck und Reproduktion von Aufzeichnungsträgern',
    'OSCUNZER_INDUSTRY_MANUFACTURE_OF_FURNITURE' => 'Möbelherstellung',
    'OSCUNZER_INDUSTRY_OTHER_MANUFACTURING' => 'Sonstige Fertigung',
    'OSCUNZER_INDUSTRY_ADVERTISING_AND_MARKET_RESEARCH' => 'Werbung und Marktforschung',
    'OSCUNZER_INDUSTRY_OTHER_PROFESSIONAL_SCIENTIFIC_AND_TECHNICAL_ACTIVITIES' => 'Sonstige freiberufliche, wissenschaftliche und technische Tätigkeiten',
    'OSCUNZER_INDUSTRY_ARTS_ENTERTAINMENT_AND_RECREATION' => 'Kunst, Unterhaltung und Erholung',

    // SEPA
    'OSCUNZER_DIRECT_DEBIT_MANDATE' => 'SEPA Lastschrift-Mandat (Bankeinzug) wird erteilt',
    'ERROR_UNZER_SEPA_CONFIRMATION_MISSING' => 'Bitte bestätigen Sie das SEPA-Mandat',

    // Installment
    'OSCUNZER_INSTALLMENT_PURCHASE_AMOUNT' => 'Gesamtkaufbetrag',
    'OSCUNZER_INSTALLMENT_INTEREST_AMOUNT' => 'Gesamtzinsbetrag',
    'OSCUNZER_INSTALLMENT_TOTAL' => 'Gesamtsumme',
    'OSCUNZER_INSTALLMENT_PDF' => 'Ich erkläre mich mit dem <b><a href="%s" target="_blank">Vertrag (PDF)</a></b> einverstanden!',
    'OSCUNZER_INSTALLMENT_SUBMIT' => 'Vertrag bestätigen',
    'OSCUNZER_INSTALLMENT_CONTINUE' => 'Weiter',
    'OSCUNZER_INSTALLMENT_CANCEL' => 'Vertrag ablehnen',
    'OSCUNZER_BUY_WITH' => 'Bezahlen mit',
];
