<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211105112337 extends AbstractMigration
{
    private static array $_aPayments = ['oscunzer_card', 'oscunzer_sepa', 'oscunzer_sepa-secured', 'oscunzer_sofort', 'oscunzer_invoice',
        'oscunzer_invoice-secured', 'oscunzer_giropay', 'oscunzer_ideal', 'oscunzer_prepayment', 'oscunzer_banktransfer', 'oscunzer_eps', 'oscunzer_post-finance',
        'oscunzer_applepay', 'oscunzer_installment', 'oscunzer_paypal', 'oscunzer_przelewy24', 'oscunzer_wechatpay', 'oscunzer_alipay'];

    private static array $_aRDFinserts = [
        'oscunzer_card_mastercard' => [
            'oxpaymentid' => 'oscunzer_card',
            'oxobjectid' => 'MasterCard'
        ],
        'oscunzer_card_visa' => [
            'oxpaymentid' => 'oscunzer_card',
            'oxobjectid' => 'VISA'
        ],
        'oscunzer_card_americanexpress' => [
            'oxpaymentid' => 'oscunzer_card',
            'oxobjectid' => 'AmericanExpress'
        ],
        'oscunzer_card_dinersclub' => [
            'oxpaymentid' => 'oscunzer_card',
            'oxobjectid' => 'DinersClub'
        ],
        'oscunzer_card_jcb' => [
            'oxpaymentid' => 'oscunzer_card',
            'oxobjectid' => 'JCB'
        ],
        'oscunzer_prepayment' => [
            'oxpaymentid' => 'oscunzer_prepayment',
            'oxobjectid' => 'ByBankTransferInAdvance'
        ],
        'oscunzer_banktransfer' => [
            'oxpaymentid' => 'oscunzer_banktransfer',
            'oxobjectid' => 'ByBankTransferInAdvance'
        ],
        'oscunzer_invoice' => [
            'oxpaymentid' => 'oscunzer_invoice',
            'oxobjectid' => 'ByInvoice'
        ],
        'oscunzer_invoice-secured' => [
            'oxpaymentid' => 'oscunzer_invoice-secured',
            'oxobjectid' => 'ByInvoice'
        ],
        'oscunzer_sepa' => [
            'oxpaymentid' => 'oscunzer_sepa',
            'oxobjectid' => 'DirectDebit',
        ],
        'ooscunzer_sepa-secured' => [
            'oxpaymentid' => 'oscunzer_sepa-secured',
            'oxobjectid' => 'DirectDebit',
        ],
        'oscunzer_paypal' => [
            'oxpaymentid' => 'oscunzer_paypal',
            'oxobjectid' => 'PayPal'
        ],
    ];

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `oxpayments`
          SET `OXFROMAMOUNT` = 10,`OXTOAMOUNT` = 1000 
         WHERE (`OXID` = 'oscunzer_sepa-secured' || `OXID` = 'oscunzer_invoice-secured')
          ;");

        foreach (self::$_aPayments as $paymentid) {
            $oxid = md5($paymentid . "oxidstandard.oxdelset");
            $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) 
                            select '" . $oxid . "', '" . $paymentid . "', 'oxidstandard', 'oxdelset' from oxdeliveryset where oxid = 'oxidstandard' ");
        }

        foreach (self::$_aRDFinserts as $oxid => $aRDF) {
            $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) 
                            VALUES(?, ?, ?, ?)", [$oxid, $aRDF['oxpaymentid'], $aRDF['oxobjectid'], 'rdfapayment']);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
